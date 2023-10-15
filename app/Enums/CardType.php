<?php

declare(strict_types=1);

namespace App\Enums;

enum CardType: string
{
    case LAND = 'Land';
    case INSTANT = 'Instant';
    case SORCERY = 'Sorcery';
    case ARTIFACT = 'Artifact';
    case CREATURE = 'Creature';
    case ENCHANTMENT = 'Enchantment';
    case PLANESWALKER = 'Planeswalker';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
