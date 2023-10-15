<?php

namespace App\Listeners;

use App\Enums\CardType;
use App\Events\CardsAddedToDeck;

class UpdateDeckAvgManaCost
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CardsAddedToDeck $event): void
    {
        $deck = $event->deck;

        $avgManaCost = collect($deck->cards()->get()->toArray())->filter(function ($card) {
            return collect(CardType::LAND->value)->doesntContain($card['type']);
        })->map( function ($card) {
            return $card['cmc'];
        })->avg();

        $deck->average_mana_cost = $avgManaCost ?? 0;
        $deck->save();
    }
}
