<?php

namespace App\Enums;

enum WorkStatus: string
{
    case Idea = 'idea';
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Idea => 'Idea', self::Draft => 'Borrador', self::Review => 'En revisión', self::Published => 'Publicado', self::Archived => 'Archivado'
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Idea => 'gray', self::Draft => 'warning', self::Review => 'info', self::Published => 'success', self::Archived => 'danger'
        };
    }
}
