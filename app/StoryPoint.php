<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoryPoint extends Model
{
    protected $table = 'story_story_points';
    public function story()
    {
        return $this->belongsTo('App\Story');
    }

    public function storyArch()
    {
        return $this->belongsTo('App\StoryArch');
    }
}
