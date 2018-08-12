<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Variable extends Model
{
    protected $table = 'story_variables';
    public function story()
    {
        return $this->belongsTo('App\Story');
    }
}
