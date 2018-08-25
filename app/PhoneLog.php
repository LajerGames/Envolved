<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhoneLog extends Model
{
    protected $table = 'story_phone_logs';
    public function story()
    {
        return $this->belongsTo('App\Story');
    }
    public function phonenumber()
    {
        return $this->hasOne('App\PhoneNumber');
    }
}
