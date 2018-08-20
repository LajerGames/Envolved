<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $table = 'story_photos';
    public function story()
    {
        return $this->belongsTo('App\Story');
    }
}
