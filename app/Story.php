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
        return $this->hasMany('App\Character');
    }
}