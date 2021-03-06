<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Item;

class ItemImage extends Model
{
    protected $table = 'item_images';
    protected $fillable = ['img','item_id'];

    public function items() {
        return $this->belongTo('App\Item');
    }

}
