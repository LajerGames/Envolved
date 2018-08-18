<?php

namespace App\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
class HandleFiles {

    /**
     * Checks if there is a file saved in the field already, if there is, then delete it. Afterwards, save the new file
     *
     * @param \Illuminate\Http\Request  $request
     * @param string $model
     * @param string $fileTableField
     * @param string $requestFileName
     * @param string $filePath
     * @return string $fileName
     */
    public static function DeleteThenUpload(Request $request, $model, $fileTableField, $requestFileName, $filePath)
    {
        // Only do anything if we're uploading a new picture
        if($request->hasFile($requestFileName))
        {
            // Do we have a file already?
            if(!empty($model->{$fileTableField}))
            {
                self::DeleteFile($filePath.$model->{$fileTableField}, $model, $fileTableField);
            }

            // Upload file
            return self::UploadFile($request, $requestFileName, $filePath);
        }
    }

    /**
     * Store a file
     *
     * @param \Illuminate\Http\Request  $request
     * @param string $requestFileName  
     * @param string $saveFilePath
     * @return string $fileName
     */
    public static function UploadFile(Request $request, $requestFileName, $saveFilePath)
    {
        if($request->hasFile($requestFileName))
        {
            // Get filename with extension
            $filenameWithExt = $request->file($requestFileName)->getClientOriginalName();
            // Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just extension
            $extension = $request->file($requestFileName)->getClientOriginalExtension();
            // Filename to store
            $fileName = $filename.'_'.time().'.'.$extension;
            // Upload file
            $path = $request->file($requestFileName)->storeAs($saveFilePath, $fileName);
        }
        else
        {
            $fileName = '';
        }

        return $fileName;
    }

    /**
     * Delete a file
     *
     * @param string $deleteFilePath
     * @param object $resourceObj
     * @param string $fileField
     * @return void
     */
    public static function DeleteFile($deleteFilePath, $resourceObj, $fileField)
    {
        if($resourceObj->{$fileField} != '')
        {
            $resourceObj->{$fileField} = '';
            $resourceObj->save();

            Storage::delete($deleteFilePath);
        }

        return;
    }
 
}