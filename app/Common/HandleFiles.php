<?php

namespace App\Common;

use App\Rules\ValidFile;
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
     * @param ValidFile $saveFilePath You may send an instance of valid file class, and MIME types will be barred from upload (most files do this check elsewhere though)
     * @return array $fileInfo
     */
    public static function DeleteThenUpload(Request $request, $model, $fileTableField, $requestFileName, $filePath, $validFile = false)
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
            return self::UploadFile($request, $requestFileName, $filePath, $validFile);
        }
    }

    /**
     * Store a file
     *
     * @param \Illuminate\Http\Request  $request
     * @param string $requestFileName  
     * @param string $saveFilePath
     * @param ValidFile $saveFilePath You may send an instance of valid file class, and MIME types will be barred from upload (most files do this check elsewhere though)
     * @return array $fileInfo
     */
    public static function UploadFile(Request $request, $requestFileName, $saveFilePath, $validFile = false)
    {
        $saveFilePath = rtrim($saveFilePath, '/');

        if($request->hasFile($requestFileName)) {

            // Do we check filetype according to ValidFile class?
            if($validFile instanceof ValidFile) {
                // Now check if this file passes the requirements
                if(!$validFile->passes('', $request->file($requestFileName))) {
                    // If not, return nothing - we're not telling the user... cba atm!
                    return '';
                }
            }

            // Get filename with extension
            $filenameWithExt = $request->file($requestFileName)->getClientOriginalName();

            // Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

            // Get just extension
            $extension = $request->file($requestFileName)->getClientOriginalExtension();

            // Filename to store
            $fileName = mt_rand(0, 10000).'_'.microtime().'.'.$extension;

            // Make sure we have the directory
            Storage::makeDirectory($saveFilePath);

            // Upload file
            $request->file($requestFileName)->storeAs($saveFilePath, $fileName);

            $mimeType = $request->file($requestFileName)->getMimeType();
        }
        else
        {
            $fileName = '';
            $mimeType = '';
        }

        return [
            'filename' => $fileName,
            'mimetype' => $mimeType
        ];
    }

    /**
     * Delete a file
     *
     * @param string $deleteFilePath
     * @param object $resourceObj
     * @param string $fileField
     * @return void
     */
    public static function DeleteFile($deleteFilePath, $resourceObj = null, $fileField = null)
    {
        if(!is_null($resourceObj) && !empty($resourceObj)) {
            if($resourceObj->{$fileField} != '')
            {
                $resourceObj->{$fileField} = '';
                $resourceObj->save();
            }   
        }

        Storage::delete($deleteFilePath);

        return;
    }
 
}