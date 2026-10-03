<?php

namespace App\Enums;

enum ImpedimentStatus: string
{
    case Open = 'open';
    case Escalated = 'escalated';
    case Resolved = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Offen',
            self::Escalated => 'Eskaliert',
            self::Resolved => 'Gelöst',
        };
    }
}
