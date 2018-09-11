<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    protected $table = 'story_phone_number_texts';
    public function phoneNumber()
    {
        return $this->belongsTo('App\PhoneNumber');
    }
}
