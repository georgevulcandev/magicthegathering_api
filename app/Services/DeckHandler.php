<?php

namespace App\Services;

use App\Events\CardsAddedToDeck;
use App\Exceptions\CardBelongsToADeckException;
use App\Exceptions\CardNotFoundException;
use App\Exceptions\DeckSizeLimitException;
use App\Models\Deck;
use App\Models\Card;
use Illuminate\Support\Facades\DB;

final class DeckHandler
{
    public function addCards(AddCardsCommand $command, Deck $deck): void
    {
        DB::beginTransaction();

        collect($command->items)->each(static function ($item) use($deck) {
            if ($deck->isFull()) {
                throw new DeckSizeLimitException('Deck size limit exceeded');
            }

            /** @var Card $card */
            $card = Card::find($item['id']);

            if (! $card instanceof Card) {
                throw CardNotFoundException::withId($card->id);
            }
            if ($card->belongsToADeck()) {
                throw CardBelongsToADeckException::withId($card->id);
            }

            $card->addToDeck($deck);
            //or $card->deck()->associate($deck);
            //   $card->save()
        });

        DB::commit();

        //new event
        CardsAddedToDeck::dispatch($deck);
    }
}
