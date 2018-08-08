<?php

namespace App\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
class HandleImages {

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