<?php

namespace Tests\Feature;

use App\Enums\CardType;
use Illuminate\Testing\Fluent\AssertableJson;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CardTest extends TestCase
{
    public function test_get_all_cards_ok(): void
    {
        //GIVEN
        $this->getOrCreateCard(count: 10);

        //WHEN
        $response = $this->getJson('api/v1/cards');

        //THEN
        $response
            ->assertOk()
            ->assertJson(fn(AssertableJson $json) =>
                $json->has('links')
                     ->has('meta')
                     ->has('data', 10, fn (AssertableJson $json) =>
                        $json->hasAll([
                            'id',
                            'name',
                            'type',
                            'cmc',
                        ])
                    )
            );
    }

    public function test_get_a_card_ok(): void
    {
        //GIVEN
        $card = $this->getOrCreateCard();

        //WHEN
        $response = $this->getJson(sprintf('api/v1/cards/%s', $card->id));

        //THEN
        $response
            ->assertOk()
            ->assertJson(fn(AssertableJson $json) =>
                $json->has('data', fn (AssertableJson $json) =>
                    $json->hasAll([
                        'id',
                        'name',
                        'type',
                        'cmc',
                    ])
                )
            );
    }

    public function test_card_not_found(): void
    {
        //GIVEN

        //WHEN
        $response = $this->getJson('api/v1/cards/12345');

        //THEN
        $response->assertNotFound();
    }

    public function test_filter_cards_by_name(): void
    {
        //GIVEN
        $card1 = $this->getOrCreateCard([
            'id'   => Uuid::uuid4()->toString(),
            'name' => 'Lazav, Dimir Mastermind',
            'type' => CardType::CREATURE->value,
            'cmc'  => 4,
        ]);

        $this->unset($card1);

        $card2 = $this->getOrCreateCard([
            'id'   => Uuid::uuid4()->toString(),
            'name' => 'Test name',
            'type' => CardType::LAND->value,
            'cmc'  => 0,
        ]);

        //WHEN
        $response = $this->getJson('api/v1/cards?filter[name]=Dimir');

        //THEN
        $response
            ->assertOk()
            ->assertJson(fn(AssertableJson $json) =>
                $json->has('links')
                     ->has('meta')
                     ->has('data', 1, fn (AssertableJson $json) =>
                        $json->where('id', $card1->id)
                             ->where('name', $card1->name)
                             ->where('type', $card1->type)
                             ->where('cmc', $card1->cmc)
                    )
            );
    }
}
