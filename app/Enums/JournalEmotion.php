<?php

namespace App\Enums;

enum JournalEmotion: string
{
    case Satisfied = 'satisfied';
    case Motivated = 'motivated';
    case Neutral = 'neutral';
    case Stressed = 'stressed';
    case Exhausted = 'exhausted';
    case Frustrated = 'frustrated';

    public function label(): string
    {
        return match ($this) {
            self::Satisfied => 'Zufrieden',
            self::Motivated => 'Motiviert',
            self::Neutral => 'Neutral',
            self::Stressed => 'Gestresst',
            self::Exhausted => 'Erschöpft',
            self::Frustrated => 'Frustriert',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Satisfied => 'lime',
            self::Motivated => 'blue',
            self::Neutral => 'zinc',
            self::Stressed, self::Exhausted => 'orange',
            self::Frustrated => 'red',
        };
    }
}
