<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{
    protected $guarded = [];
    protected $appends = ['letter'];

    public function getLetterAttribute(){
        return mb_substr($this->sender_mail, 0, 1);
    }
}
