<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    protected $table = 'story_phone_number_texts';
    public function story()
    {
        return $this->belongsTo('App\PhoneNumber');
    }
}
