<?php


namespace App\Common;

use App\Story;
use App\StoryArch;
use App\StoryPoint;
use App\Common\Permission;


class StoryPoints
{
    const   forward     = 'forward',
            backwards   = 'backwards';

    private $storyArch = null;

    public function __construct(StoryArch $storyArch) {
        $this->storyArch = $storyArch;
    }

    public function UpdateStoryPoint(StoryPoint $storyPoint) {

        // Check that the story point belongs to this user
        if(!Permission::CheckOwnership(auth()->user()->id, $storyPoint->story->user_id)) {
            return;
        }

        $storyPoint->save();
    }

    public function DeleteStoryPointsViaStoryPointsID($storyPointIDs) {

        $affectedStoryPoints = [
            'deleted'  => [],
            'affected'  => [],
            'deleted_arch_starting_point' => false
        ];
        if(is_countable($storyPointIDs) && count($storyPointIDs)) {

            foreach($storyPointIDs as $storyPointID) {

                $delete = $this->DeleteStoryPoint($storyPointID);

                if($delete['deleted']) {

                    // Add the affected rows
                    $affectedStoryPoints['affected'] = array_merge($delete['affected'], $affectedStoryPoints['affected']);

                    // Add this row to the deleted records
                    $affectedStoryPoints['deleted'][] = $storyPointID;

                    if($delete['deleted_arch_starting_point']) {
                        $affectedStoryPoints['deleted_arch_starting_point'] = true;
                    }
                }
            }

        }

        // remove duplicates
        $affectedStoryPoints['affected'] = array_unique($affectedStoryPoints['affected']);

        // Find the rows that are affected but not deleted
        $affectedStoryPoints['affected'] = array_diff($affectedStoryPoints['affected'], $affectedStoryPoints['deleted']);

        // Now that we have a complete list of affected but not deleted records, go through them and remove the leads to that may now lead to a deleted record
        if(is_countable($affectedStoryPoints['affected']) && count($affectedStoryPoints['affected']) > 0) {

            foreach($affectedStoryPoints['affected'] as $affectedStoryPointID) {

                $affectedStoryPoint = StoryPoint::find($affectedStoryPointID);

                // Extract leads to JSON
                $leadsTo = json_decode($affectedStoryPoint->leads_to_json);

                // Check if it leads anywhere
                if(is_countable($leadsTo) && count($leadsTo) > 0) {

                    // Loop through the leads to
                    foreach($leadsTo as $entryID => $leadsToObj) {

                        // Check if this is a story point and that the story point it leads to is one of the deleted ones
                        if(property_exists($leadsToObj, 'point') && in_array($leadsToObj->point, $affectedStoryPoints['deleted'])) {

                            // If it is, then remove that lead from the story point
                            unset($leadsTo[$entryID]);

                        }

                    }
                }

                // Save the new leads to into the story point model
                $affectedStoryPoint->leads_to_json = is_countable($leadsTo) && count($leadsTo) > 0 ? json_encode($leadsTo) : '';


                // Update the story point model
                $this->UpdateStoryPoint($affectedStoryPoint);

            }

        }

        // Ensure that we're returning objects
        $affectedStoryPoints['deleted'] = (object)$affectedStoryPoints['deleted'];
        $affectedStoryPoints['affected'] = (object)$affectedStoryPoints['affected'];

        return $affectedStoryPoints;

    }

    public function DeleteStoryPoint($storyPointID) {

        // Get the story point
        $storyPoint = StoryPoint::find($storyPointID);

        // Check that the story point belongs to this user
        if(!Permission::CheckOwnership(auth()->user()->id, $storyPoint->story->user_id)) {
            return [
                'deleted'   => false,
                'affected'  => [],
                'deleted_arch_starting_point' => false
            ];
        }

        $deletedArchStartingPoint = false;
        // Check if this is the starting point of this story point - if it is, then we will have to do some mumbo jumbo
        if($storyPointID == $this->storyArch->start_story_point_id) {

            // Okay we're absolutely deleting the starting story point ID...

            $this->storyArch->start_story_point_id = 0;
            $this->storyArch->save();

            $deletedArchStartingPoint = true;

        }

        // Now that we know that this storypoint is deletable for this user - we can safely delete it

        // But first - get a list of story points that leads to this
        $referers = $this->ExtractReferers($storyPoint, self::backwards);
        $referersList = [];
        if(is_countable($referers) && count($referers) > 0)
        foreach($referers as $storyPointID => $refererStoryPoint) {
            $referersList[] = $storyPointID;
        }

        // Delete
        $storyPoint->delete();

        return [
            'deleted'   => true,
            'affected'  => $referersList,
            'deleted_arch_starting_point' => $deletedArchStartingPoint
        ];

    }

    public function GetStoryPointsToDeleteFromStartingPoint(StoryPoint $storyPoint) {
        $storyPointMazeArray = $this->GetStoryPointMazeArray($storyPoint);

        // Go through the maze we just received and figure out which ones are deletable
        $storyPointsToDelete = [];
        if(is_countable($storyPointMazeArray) && count($storyPointMazeArray) > 0) {

            foreach($storyPointMazeArray as $storyPointID => $storyPointStats) {

                // if 0 is in this array, then it's the starting point, and that will be deleted nomatter what
                $isStartingPoint = in_array(0, $storyPointStats['visited_by']);
                $canDelete = $isStartingPoint;

                // Only check the next few things if this is not the starting point - the starting point will always be deleted
                if(!$isStartingPoint) {

                    // Otherwise, we can only delete if visited_by array is equal to the referers array. That means that evey possible route to this story point
                    if(count(array_diff($storyPointStats['referers'], $storyPointStats['visited_by'])) == 0)
                        $canDelete = true;

                }

                if($canDelete) {

                    $storyPointsToDelete[] = $storyPointID;

                }

            }

            $this->CleanUpStoryPointsToDeleteFromStartingPoint($storyPoint->id, $storyPointMazeArray, $storyPointsToDelete);
        }

        return $storyPointsToDelete;
    }

    public function CleanUpStoryPointsToDeleteFromStartingPoint($startingStoryPointID, $storyPointMazeArray, &$storyPointsToDelete) {

        /* THIS IS CONFUSING! TEXT BELOW IS PROBABLY NOT ENOUGH TO CLARIFY WHY THIS IS BEING DONE IN THE WAY IT IS! */
        // Now we have flagged all the story points for deletion that needs to be deleted, but some may need to be removed from the list.
        // Let's say that we delete from story point #6 and onwards.
        // Story point 13 leads to 3. Also story point 2 leads to 3.
        // Nothing in this maze leads to 2, so story point 2 wont be deleted.
        // That means that story point 3 isn't in the $storyPointsToDelete array. That's fine.
        // However, story point 3 is the only story point that leads to 4. Currently that means that 4 is in the list of story points to be deleted - but it shouldn't be, because story point 3 isn't being delete.
        // That means that we should loop through the story points again and remove the ones from the deletion list, that are being delete because of a story point that aren't being deleted anyways.
        // Each time we stumble upon a story point that shouldn't be delete anyway, we should loop through all of them again from the top, because another removal of another story point may influence what's being deleted.

        foreach($storyPointMazeArray as $storyPoint => $storyPointStats) {

            if(!in_array($storyPoint, $storyPointsToDelete))
                continue;

            if($storyPoint == $startingStoryPointID)
                continue;

            if(is_countable($storyPointStats['referers']) && count($storyPointStats['referers']) > 0) {

                foreach($storyPointStats['referers'] as $refererStoryPoint) {

                    if(!in_array($refererStoryPoint, $storyPointsToDelete)) {

                        $storyPointsToDelete = array_diff($storyPointsToDelete, [$storyPoint]);

                        $this->CleanUpStoryPointsToDeleteFromStartingPoint($startingStoryPointID, $storyPointMazeArray, $storyPointsToDelete);
                        break;

                    }

                }

            }

        }

    }

    public function GetStoryPointMazeArray(StoryPoint $storyPoint, $storyPointCollection = [], $referedByStoryPointID = 0) {

        // If this is not the first run, and we're looking at the story_point start, the do nothing further because something can always lead to the story point start.
        if($referedByStoryPointID != 0 && $this->storyArch->start_story_point_id == $storyPoint->id) {
            return $storyPointCollection;
        }

        $seenThisBefore = true;
        if(!key_exists($storyPoint->id, $storyPointCollection)) {

            $seenThisBefore = false;

            $storyPointCollection[$storyPoint->id] = [
                /*'story_point'   => $storyPoint,*/
                'name'          => $storyPoint->name,
                'referers'      => [],
                'leads_to'      => [],
                'visited_by'    => []
            ];
        }

        // Update visited by to make sure we know which story points has lead to this thoughout the maze
        $storyPointCollection[$storyPoint->id]['visited_by'][] = $referedByStoryPointID;

        // Don't go any further if we've seen this story point before
        if($seenThisBefore)
            return $storyPointCollection;

        // Find out which story points leads to this one
        $referers = $this->ExtractReferers($storyPoint, self::backwards);
        if(is_countable($referers) && count($referers) > 0) {

            // Loop through the referers and get the ids
            foreach($referers as $storyPointID => $referer) {
                if(!in_array($referer->id, $storyPointCollection[$storyPoint->id]['referers']))
                    $storyPointCollection[$storyPoint->id]['referers'][] = $referer->id;
            }

        }

        // Find out which story points this one leads to
        $leadsTos = $this->ExtractReferers($storyPoint, self::forward);
        if(is_countable($leadsTos) && count($leadsTos) > 0) {

            // Loop through the leadsTo and do the same with them
            foreach($leadsTos as $storyPointID => $leadsTo) {

                if(!in_array($leadsTo->id, $storyPointCollection[$storyPoint->id]['leads_to']))
                    $storyPointCollection[$storyPoint->id]['leads_to'][] = $leadsTo->id;

                $storyPointCollection = $this->GetStoryPointMazeArray($leadsTo, $storyPointCollection, $storyPoint->id);
            }

        }

        return $storyPointCollection;
    }

    /*
    public function GetStoryPointLeads(StoryPoint $storyPoint, $direction = self::forward)
    {
        // Do we find the storypoints that leads to this one, or the ones this one leads to?
        return $this->ExtractReferers($storyPoint, $direction);
    }*/

    private function GetLeadsTos(StoryPoint $storyPoint)
    {
        $leadsToJson = json_decode($storyPoint->leads_to_json);
        $leadsTo = [];

        if(is_countable($leadsToJson) && count($leadsToJson) > 0) {
            foreach($leadsToJson as $val) {
                if(property_exists($val, 'point'))
                    $leadsTo[] = $val->point;
            }
        }

        if(is_countable($leadsTo) && count($leadsTo) > 0) {
            return StoryPoint::whereIn('id', $leadsTo)->get();
        }
        return;
    }

    private function GetReferers(StoryPoint $storyPoint) {
            return StoryPoint::where(
                'story_id', $storyPoint->story_id
            )->whereRaw(
                'JSON_CONTAINS(leads_to_json, \'{"point": '.$storyPoint->id.'}\')'
            )->orderBy(
                'id', 'ASC'
            )->get();
    }

    private function ExtractReferers(StoryPoint $storyPoint, $direction = self::forward) {
        $referers = $direction == self::forward
            ? $this->GetLeadsTos($storyPoint)
            : $this->GetReferers($storyPoint);

        $refererList = [];
        if(is_countable($referers) && count($referers) > 0) {
            foreach($referers as $referer) {
                $refererList[$referer->id] = $referer;
            }
        }

        return $refererList;
    }

}