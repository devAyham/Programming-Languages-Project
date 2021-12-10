<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Interact extends Model
{
    protected $fillable = ['user_id','interact','item_id'];
}
