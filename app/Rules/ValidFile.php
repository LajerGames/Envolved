<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidFile implements Rule
{
    public  $allowedImageMimes = [],
            $allowedVideoMimes = [],
            $allowedAudioMimes = [],
            $allowedMimeTypes = [];
    /**
     * Create a new rule instance.
     *
     * @param  bool  $allowImage
     * @param  bool  $allowVideo
     * @param  bool  $allowAudio
     * @return void
     */
    public function __construct($allowImage = true, $allowVideo = true, $allowAudio = true)
    {
        if($allowImage) {
            // Allow gif, jpg and png
            $this->allowedImageMimes[] = 'image/gif';
            $this->allowedImageMimes[] = 'image/jpeg';
            $this->allowedImageMimes[] = 'image/png';
        }
        if($allowVideo) {
            $this->allowedVideoMimes[] = 'video/x-msvideo';
            $this->allowedVideoMimes[] = 'video/mp4';
            $this->allowedVideoMimes[] = 'video/mpeg';
        }
        if($allowAudio) {
            $this->allowedAudioMimes[] = 'audio/mpeg';
            $this->allowedAudioMimes[] = 'audio/mp4';
            $this->allowedAudioMimes[] = 'audio/vnd.wav';
            $this->allowedAudioMimes[] = 'audio/mid';
        }

        // Combine the mime types
        $this->allowedMimeTypes = array_merge($this->allowedImageMimes, $this->allowedVideoMimes, $this->allowedAudioMimes);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return in_array($value->getMimeType(), $this->allowedMimeTypes);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // Construct message, depending on what types of files we're accepting
        $strMessage = 'Uploaded file needs to be of type:';
        if(@count($this->allowedImageMimes) > 0)
            $strMessage .= "<br /> - Image: ".implode(',', $this->allowedImageMimes);
        if(@count($this->allowedVideoMimes) > 0)
            $strMessage .= "<br /> - Video: ".implode(',', $this->allowedVideoMimes);
        if(@count($this->allowedAudioMimes) > 0)
            $strMessage .= "<br /> - Audio: ".implode(', ', $this->allowedAudioMimes);
        
        return $strMessage;
    }
}
