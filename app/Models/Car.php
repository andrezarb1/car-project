<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Dealer;
use App\Models\CarImage;

class Car extends Model
{
    use HasFactory;

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function images()
    {
        return $this->hasMany(CarImage::class);
    }

    protected $fillable = [
    'dealer_id', 'make', 'model', 'year', 'price', 'vin', 'slug', 'fuel_type'
    ];

}
