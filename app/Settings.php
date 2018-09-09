<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $table = 'story_settings';
    public function story()
    {
        return $this->belongsTo('App\Story');
    }
}
