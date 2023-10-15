<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CardCollection;
use App\Http\Resources\V1\CardResource;
use App\Models\Card;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): CardCollection
    {
        $cards = QueryBuilder::for(Card::class)
            ->allowedFilters(['name', 'type'])
            ->allowedSorts(['cmc'])
            ->paginate()
            ->appends($request->query());

        return new CardCollection($cards);
    }

    /**
     * Display the specified resource.
     */
    public function show(Card $card)
    {
        return new CardResource($card);
    }
}
