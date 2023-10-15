<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    use HasFactory;

    public const SIZE_LIMIT = 30; // a deck can't have more than 30 cards

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'average_mana_cost' => 'float',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'average_mana_cost' => '0.00',
    ];

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function isFull(): bool
    {
        return $this->cards()->count() >= self::SIZE_LIMIT;
    }
}
