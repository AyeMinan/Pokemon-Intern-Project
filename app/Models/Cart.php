<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';




    protected $fillable = [
    'image',
    'heading',
    'rarity',
    'price',
    'amount',
    'user_id',
    'card_id'] ;

    public function cards(){
        return $this->hasMany(Card::class);
    }
    public function user(){
     return $this->belongsTo(User::class);
    }
}
