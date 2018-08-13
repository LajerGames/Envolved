<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhoneNumber extends Model
{
    protected $table = 'story_phone_numbers';
    public function story()
    {
        return $this->belongsTo('App\Story');
    }
    public function character()
    {
        return $this->hasOne('App\Character');
    }
}
