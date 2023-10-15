<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $deck_id
 */
class Card extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'id',
        'name',
        'cmc',
        'type',
    ];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function belongsToADeck(): bool
    {
        return $this->deck_id !== null;
    }

    public function addToDeck($deck): void
    {
        $this->deck_id = $deck->id;
        $this->save();
    }
}
