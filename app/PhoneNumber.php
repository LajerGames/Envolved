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
    public function texts()
    {
        return $this->hasMany('App\Text')->orderBy('days_ago', 'DESC')->orderBy('time', 'ASC');
    }
    public function phonelogs()
    {
        return $this->hasMany('App\PhoneLogs');
    }
}
