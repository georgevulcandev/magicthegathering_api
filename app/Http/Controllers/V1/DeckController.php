<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\CardBelongsToADeckException;
use App\Exceptions\DeckSizeLimitException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ControllerTrait;
use App\Http\Requests\AddCardsRequest;
use App\Http\Requests\StoreDeckRequest;
use App\Http\Requests\UpdateDeckRequest;
use App\Http\Resources\V1\DeckCollection;
use App\Http\Resources\V1\DeckResource;
use App\Models\Deck;
use App\Services\AddCardsCommand;
use App\Services\DeckHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class DeckController extends Controller
{
    use ControllerTrait;

    public function __construct(private readonly DeckHandler $deckHandler)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $decks = QueryBuilder::for(Deck::class)
            ->allowedIncludes(['cards'])
            ->paginate()
            ->appends($request->query());

        return new DeckCollection($decks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDeckRequest $request)
    {
        return new DeckResource(Deck::create($request->all()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Deck $deck)
    {
        $deck = QueryBuilder::for(Deck::where('id', $deck->id))
            ->allowedIncludes(['cards'])
            ->first();

        return new DeckResource($deck);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDeckRequest $request, Deck $deck)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deck $deck)
    {
        //
    }

    public function addCards(AddCardsRequest $request, Deck $deck): JsonResponse
    {
        try {
            $this->deckHandler->addCards(
                command: AddCardsCommand::fromRequest($request->toArray()),
                deck: $deck
            );
        } catch (DeckSizeLimitException|CardBelongsToADeckException $e) {
            return $this->respondError([$e->getMessage()]);
        }

        return $this->respondNoContent();
    }
}
