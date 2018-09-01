<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model
{
    protected $table = 'story_module_news';
    public function story()
    {
        return $this->belongsTo('App\Story');
    }
}
