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
        return $this->hasMany('App\Variable')->orderBy('type', 'asc');
    }
    public function phonenumber()
    {
        return $this->hasMany('App\PhoneNumber');
    }
    public function texts()
    {
        return $this->hasMany('App\Text')->orderBy('days_ago', 'DESC')->orderBy('time', 'ASC');
    }
    public function photos()
    {
        return $this->hasMany('App\Photo')->orderBy('days_ago', 'DESC')->orderBy('time', 'ASC');
    }
    public function phonelogs()
    {
        return $this->hasMany('App\PhoneLog')->orderBy('days_ago', 'DESC')->orderBy('start_time', 'ASC');
    }
    public function news()
    {
        return $this->hasMany('App\NewsItem')->orderBy('published', 'DESC')->orderBy('days_ago', 'DESC')->orderBy('time', 'ASC');
    }
    public function settings()
    {
        return $this->hasOne('App\Settings');
    }
    public function storyarchs()
    {
        return $this->hasMany('App\StoryArch');
    }
    public function storypoints()
    {
        return $this->hasMany('App\StoryPoint');
    }
}