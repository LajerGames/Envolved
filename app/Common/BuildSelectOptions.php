<?php

namespace App\Common;
 
class BuildSelectOptions {

    /**
     * Generates an array based on a mode.
     * 
     * The two mixed options can be either array or string, depending on whether you want to combine multiple fields in a certain value in the options
     * The seperato is only relevant if either of the two mixed options are arrays.
     *
     * @param model $model
     * @param mixed [array/string] $value
     * @param mixed [array/string] $optionValue
     * @param string $seperator
     * @param string $nameOfEmptyField
     * 
     * @return array
     */
    public static function Build($model, $value, $optionValue, $seperator = '', $nameOfEmptyField = '')
    {
        $options = [];

        // Do we add any custom values at the beginning of the array
        if(!empty($nameOfEmptyField)) {
            $options[0] = ' -- '.$nameOfEmptyField.' -- ';
        }

        if(count($model) > 0) {

            foreach($model as $entry) {

                $options[self::makeValue($entry, $value, $seperator)] = self::makeValue($entry, $optionValue, $seperator);

            }

        }

        return $options;
        
    }

    /**
     * Generating a value for a value or an option html value.
     * 
     * @param model $modelEntry
     * @param mixed [array/string] $value
     * @param string $seperator
     * 
     * @return string
     */
    private static function makeValue($modelEntry, $value, $seperator) {

        $string = '';

        if(is_array($value)) {

            // The option values are a combination of different fields in the model, create them
            foreach($value as $field) {


                $string .= (empty($modelEntry->{$field}) ? '' : $modelEntry->{$field}).$seperator;

            }

            $string = rtrim($string, $seperator);
            $string = empty($string) ? '-' : $string;

        } else {

            $string = $modelEntry->{$value};

        }

        return $string;

    }
 
}