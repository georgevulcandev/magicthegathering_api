<?php

declare(strict_types=1);

namespace App\Services;

final class AddCardsCommand
{
    public array $items;
    public static function fromRequest(array $data): self
    {
        $self = new self();

        $self->items = $data['data'];

        return $self;
    }
}
