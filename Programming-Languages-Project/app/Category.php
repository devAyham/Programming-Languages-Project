<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use App\Item;

class Category extends Model
{
    use Translatable;
    protected $table = 'categories';
    protected $fillable = ['image'];
    public $translatedAttributes = ['name'];

    public function items() {
        return $this->hasMany(Item::class );
    }
}
