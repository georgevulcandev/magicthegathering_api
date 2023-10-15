<?php

declare(strict_types=1);

namespace Tests;

use App\Events\CardsAddedToDeck;
use App\Models\Card;
use App\Models\Deck;
use Illuminate\Database\Eloquent\Collection;

trait ModelFactories
{
    /**
     * Store the models that have been created by factories.
     */
    protected array $models = [];

    protected function getOrCreateCard(
        array $attributes = [],
        ?int $count = null,
        bool $addToDeck = false
    ): Collection|Card {
        $this->models[Card::class] ??= Card::factory($count)->create([...$attributes,]);

        if ($addToDeck) {
            $cards = $count === null ? collect([$this->models[Card::class]]) : $this->models[Card::class];

            $cards->each(function ($card) {
                $card->deck()->associate($this->getOrCreateDeck()->first());
                $card->save();
            });

            CardsAddedToDeck::dispatch($this->getOrCreateDeck()->first());
        }

        return $this->models[Card::class];
    }

    protected function getOrCreateDeck(array $attributes = [], ?int $count = null): Collection|Deck
    {
        $this->models[Deck::class] ??= Deck::factory($count)->create([...$attributes,]);

        return $this->models[Deck::class];
    }

    protected function unset(...$args): static
    {
        collect($args)->each(
            function($item) {
                $class = !\is_string($item) ? \get_class($item) : $item;
                unset($this->models[$class]);
            }
        );

        return $this;
    }

    protected function unsetAll(): static
    {
        $this->models = [];

        return $this;
    }
}
