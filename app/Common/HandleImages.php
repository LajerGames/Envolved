<?php

namespace App\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
class HandleImages {

    /**
     * Checks if there is an image saved in the field already, if there is, then delete it. Afterwards, save the new image
     *
     * @param \Illuminate\Http\Request  $request
     * @param string $model
     * @param string $imageTableField
     * @param string $requestImageName
     * @param string $imagePath
     * @return string $imageName
     */
    public static function DeleteThenUpload(Request $request, $model, $imageTableField, $requestImageName, $imagePath)
    {
        // Do we have an image already?
        if(!empty($model->{$imageTableField}))
        {
            self::DeleteImage($imagePath.$model->{$imageTableField}, $model, $imageTableField);
        }

        // Upload image
        return self::UploadImage($request, $requestImageName, $imagePath);
    }

    /**
     * Store an image
     *
     * @param \Illuminate\Http\Request  $request
     * @param string $requestImageName  
     * @param string $saveImagePath
     * @return string $imageName
     */
    public static function UploadImage(Request $request, $requestImageName, $saveImagePath)
    {
        if($request->hasFile($requestImageName))
        {
            // Get filename with extension
            $filenameWithExt = $request->file($requestImageName)->getClientOriginalName();
            // Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just extension
            $extension = $request->file($requestImageName)->getClientOriginalExtension();
            // Filename to store
            $imageName = $filename.'_'.time().'.'.$extension;
            // Upload image
            $path = $request->file($requestImageName)->storeAs($saveImagePath, $imageName);
        }
        else
        {
            $imageName = '';
        }

        return $imageName;
    }

    /**
     * Delete an image
     *
     * @param string $deleteImagePath
     * @param object $resourceObj
     * @param string $imgField
     * @return void
     */
    public static function DeleteImage($deleteImagePath, $resourceObj, $imgField)
    {
        if($resourceObj->{$imgField} != '')
        {
            $resourceObj->{$imgField} = '';
            $resourceObj->save();

            Storage::delete($deleteImagePath);
        }

        return;
    }
 
}