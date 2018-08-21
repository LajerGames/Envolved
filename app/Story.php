<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    public function characters()
    {
        return $this->hasMany('App\Character')->orderBy('role', 'asc');
    }
    public function variables()
    {
        return $this->hasMany('App\Variable')->orderBy('type', 'asc');
    }
    public function phonenumber()
    {
        return $this->hasMany('App\PhoneNumber');
    }
    public function photos()
    {
        return $this->hasMany('App\Photo')->orderBy('taken_on', 'asc');
    }
}