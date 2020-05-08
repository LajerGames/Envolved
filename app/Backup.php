<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $table = 'story_phone_logs';
    public function story()
    {
        return $this->belongsTo('App\Story');
    }
}
