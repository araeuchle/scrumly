<?php

namespace App\Enums;

enum SprintEventStatus: string
{
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Geplant',
            self::InProgress => 'Läuft',
            self::Completed => 'Abgeschlossen',
        };
    }
}
