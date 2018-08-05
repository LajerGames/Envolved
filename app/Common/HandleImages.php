<?php

namespace App\Common;

use Illuminate\Http\Request;
 
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

    public static function DeleteImage()
    {
        
    }
 
}