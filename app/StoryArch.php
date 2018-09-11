<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoryArch extends Model
{
    protected $table = 'story_story_arches';
    public function story()
    {
        return $this->belongsTo('App\Story');
    }
}
