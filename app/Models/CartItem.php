<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'duration_days',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'duration_days' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function subtotal(): int
    {
        $daily = (int) ($this->product?->price_per_day ?? 0);

        return $daily * max(1, (int) $this->quantity) * max(1, (int) $this->duration_days);
    }
}
