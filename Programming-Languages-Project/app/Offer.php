<?php

namespace App;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'item_id',
        'First_offer',
        'value_of_discount_first_offer',
        'Second_offer',
        'value_of_discount_Second_offer',
        'Third_offer',
        'value_of_discount_Third_offer'
    ];
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

}
