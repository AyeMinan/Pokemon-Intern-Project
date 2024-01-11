<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $table = 'cards';

    protected $fillable = [
    'image',
    'heading',
    'rarity',
    'price',
    'amount',
    'user_id'
    ] ;
        public function cart(){
            return $this->belongsTo(Cart::class);
        }

        public function scopeFilter($query, $search)
    {
        if ($search) {
            $query->where('heading', 'LIKE', '%' . $search . '%');
        }
    }
}
