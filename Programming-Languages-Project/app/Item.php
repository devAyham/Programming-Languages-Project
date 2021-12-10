<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use App\Category;
use App\ItemImage;
use App\Size;
use App\ItemSize;
use App\ItemType;
use App\Color;
use App\Type;
use App\Brand;
class Item extends Model
{
    use Translatable;
    protected $table = 'items';
    protected $fillable = ['contact_information','expiration_date','quantity','price','new_price','views'];
    public $translatedAttributes = ['title'];

    public function categories(){
        return $this->belongsToMany('App\Category');
    }
    public function images(){
        return $this->hasMany('App\ItemImage');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
