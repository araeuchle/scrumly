<?php

namespace App\Enums;

enum ImpedimentPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Niedrig',
            self::Medium => 'Mittel',
            self::High => 'Hoch',
            self::Critical => 'Kritisch',
        };
    }
}
