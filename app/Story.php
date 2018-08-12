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
        return $this->hasMany('App\Variable')->orderBy('type', 'asc');;
    }
}