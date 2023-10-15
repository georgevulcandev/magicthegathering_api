<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class CardBelongsToADeckException extends Exception
{
    private const MESSAGE_FORMAT = 'Card with id `%s` is already inside a deck.';

    public static function withId(string $id): self
    {
        return new self(sprintf(self::MESSAGE_FORMAT, $id));
    }
}
