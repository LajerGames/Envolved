<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    protected $table = 'story_characters';
    public function story()
    {
        return $this->belongsTo('App\Story');
    }
}
