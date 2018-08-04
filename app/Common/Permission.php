<?php

namespace App\Common;
 
class Permission {

    /**
     * Checks if ownership and owner matches
     * I.e. user_id and story->user_id or story_id and character_story_id
     * 
     * @param  int  $user_id
     * @param  int  $owner_id
     * @return boolean
     */
    public static function CheckOwnership($id1, $id2)
    {
        return $id1 == $id2;
    }
 
}
