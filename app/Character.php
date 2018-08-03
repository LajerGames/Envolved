<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    public function story()
    {
        return $this->belongsTo('App\Story');
    }
}
