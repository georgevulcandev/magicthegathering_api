<?php

namespace Tests\Feature;

use App\Enums\CardType;
use App\Models\Card;
use App\Models\Deck;
use Illuminate\Testing\Fluent\AssertableJson;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class DeckTest extends TestCase
{
    public function test_get_all_decks_ok(): void
    {
        //GIVEN
        $this->getOrCreateDeck(count: 10);

        //WHEN
        $response = $this->getJson('api/v1/decks');

        //THEN
        $response
            ->assertOk()
            ->assertJson(fn(AssertableJson $json) =>
                $json->has('links')
                     ->has('meta')
                     ->has('data', 10, fn (AssertableJson $json) =>
                        $json->hasAll([
                            'id',
                            'avgManaCost',
                    ])
                )
            );
    }

    public function test_get_a_deck_ok(): void
    {
        //GIVEN
        $deck = $this->getOrCreateDeck();

        //WHEN
        $response = $this->getJson('api/v1/decks/' . $deck->id);

        //THEN
        $response
            ->assertOk()
            ->assertJson(fn(AssertableJson $json) =>
                $json->has('data', fn (AssertableJson $json) =>
                    $json->hasAll([
                        'id',
                        'avgManaCost',
                    ])
                )
            );
    }

    public function test_deck_not_found(): void
    {
        //GIVEN

        //WHEN
        $response = $this->getJson('api/v1/decks/12345');

        //THEN
        $response->assertNotFound();
    }

    public function test_get_all_decks_including_cards_ok(): void
    {
        //GIVEN
        $this->getOrCreateDeck(count: 2);
        $this->getOrCreateCard(count: 3, addToDeck: true);

        //WHEN
        $response = $this->getJson('api/v1/decks?include=cards');

        //THEN
        $response
            ->assertOk()
            ->assertJson(fn(AssertableJson $json) =>
                $json->has('links')
                     ->has('meta')
                     ->has('data', 2, fn (AssertableJson $json) =>
                        $json->has('cards', 3, fn (AssertableJson $json) =>
                            $json->hasAll([
                                'id',
                                'name',
                                'type',
                                'cmc',
                            ])
                        )->etc()
                     )
            );
    }

    public function test_get_a_deck_including_cards_ok(): void
    {
        //GIVEN
        $deck = $this->getOrCreateDeck();
        $this->getOrCreateCard(count: 3, addToDeck: true);

        //WHEN
        $response = $this->getJson('api/v1/decks/' . $deck->id . '?include=cards');

        //THEN
        $response
            ->assertOk()
            ->assertJson(fn(AssertableJson $json) =>
                $json->has('data', fn (AssertableJson $json) =>
                    $json->has('id')
                        ->has('avgManaCost')
                        ->has('cards', 3, fn (AssertableJson $json) =>
                            $json->hasAll([
                                'id',
                                'name',
                                'type',
                                'cmc',
                            ])
                        )
                )
            );
    }

    public function test_create_deck_ok(): void
    {
        //GIVEN

        //WHEN
        $response = $this->postJson('api/v1/decks');

        //THEN
        $response
            ->assertCreated()
            ->assertJson(fn(AssertableJson $json) =>
                $json->has('data.id')
                     ->has('data.avgManaCost')
            );
    }

    public function test_add_cards_to_a_deck_ok(): void
    {
        //GIVEN
        $deck = $this->getOrCreateDeck();

        $card = $this->getOrCreateCard();

        $payload = [
            'data' => [
                ['id' => $card->id],
            ]
        ];

        //WHEN
        $response = $this->postJson('api/v1/decks/' . $deck->id . '/cards', $payload);

        //THEN
        $response->assertNoContent();
    }

    public function test_add_cards_to_a_deck_limit_exceeded_error(): void
    {
        //GIVEN
        $deck = $this->getOrCreateDeck();
        $this->getOrCreateCard(count: Deck::SIZE_LIMIT, addToDeck: true);

        $this->unset(Card::class);
        $newCard= $this->getOrCreateCard();

        $payload = [
            'data' => [
                ['id' => $newCard->id],
            ]
        ];

        //WHEN
        $response = $this->postJson('api/v1/decks/' . $deck->id . '/cards', $payload);

        //THEN
        $response
            ->assertBadRequest()
            ->assertExactJson(['errors' => ['Deck size limit exceeded',]]);
    }

    public function test_add_card_to_a_deck_card_already_in_a_deck_error(): void
    {
        //GIVEN
        $deck = $this->getOrCreateDeck();
        $card = $this->getOrCreateCard(
            attributes: [
                'id'   => Uuid::uuid4()->toString(),
                'type' => CardType::LAND->value,
                'cmc'  => 0,
            ],
            addToDeck: true
        );

        $payload = [
            'data' => [
                ['id' => $card->id],
            ]
        ];

        //WHEN
        $response = $this->postJson('api/v1/decks/' . $deck->id . '/cards', $payload);

        //THEN
        $response
            ->assertBadRequest()
            ->assertExactJson([
                'errors' => [
                    sprintf('Card with id `%s` is already inside a deck.', $card->id),
                ]
            ]);
    }

    public function test_update_deck_avg_mana_cost_when_adding_cards_to_deck()
    {
        //GIVEN
        $deck = $this->getOrCreateDeck();
        $card1 = $this->getOrCreateCard(
            attributes: [
                'id'   => Uuid::uuid4()->toString(),
                'type' => CardType::LAND->value,
                'cmc'  => 0,
            ]
        );

        $this->unset(Card::class);
        $card2 = $this->getOrCreateCard(
            attributes: [
                'id'   => Uuid::uuid4()->toString(),
                'type' => CardType::CREATURE->value,
                'cmc'  => 8,
            ]
        );

        $this->unset(Card::class);
        $card3 = $this->getOrCreateCard(
            attributes: [
                'id'   => Uuid::uuid4()->toString(),
                'type' => CardType::PLANESWALKER->value,
                'cmc'  => 7,
            ]
        );

        $payload = [
            'data' => [
                ['id' => $card1->id],
                ['id' => $card2->id],
                ['id' => $card3->id],
            ]
        ];

        //WHEN
        $response = $this->postJson('api/v1/decks/' . $deck->id . '/cards', $payload);

        //THEN
        $response->assertNoContent();
        $this->assertDatabaseHas('decks', [
            'average_mana_cost' => collect([7, 8])->avg(),
        ]);
    }
}
