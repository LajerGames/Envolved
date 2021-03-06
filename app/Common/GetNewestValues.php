<?php

namespace App\Common;
 
class GetNewestValues {

    /**
     * Generates an array with the newest values of certain models
     * 
     * You see, I never figured out how to re-order data in a given model.
     * So we'll loop through it, and get the data from the newest ID
     *
     * @param model $model
     * @param array $keys
     * @param string $highestWhat (Must ne numberic! What do we need the highest of? default is id)
     * 
     * @return array
     */
    public static function Build($model, $keys, $highestWhat = 'id')
    {
        // Let's initiate a highest ID that'll be used to figure out which data to save.
        $highestNO = 0;

        // Beneath, initiated the array that'll contain the data we'll return.
        $array = self::saveData('', $keys);

        if(!empty($model)) {
            foreach($model as $entry) {

                if($highestNO < $entry->{$highestWhat}) {
    
                    // This entry is newer, use it.
                    $highestNO = $entry->{$highestWhat};
    
                    $array = self::saveData($entry, $keys);
    
                }
    
            }
        }

        return $array;
    }

    /**
     * Returning the actual array of what is described in the only other function in here...
     *
     * @param object $modelEntry
     * @param array $keys
     * 
     * @return array
     */
    private static function saveData($modelEntry, $keys) {

        $array = [];
        // Loop through the data we've been asked  to save
        foreach($keys as $key) {

            // Save the data
            $array[$key] = !empty($modelEntry) ? $modelEntry->{$key} : '';

        }

        return $array;

    }

}