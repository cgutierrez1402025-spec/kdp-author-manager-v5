<?php

namespace App\Enums;

enum PublicationStatus: string
{
    case Draft = 'draft';
    case Processing = 'processing';
    case Published = 'published';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador', self::Processing => 'En proceso', self::Published => 'Publicado', self::Failed => 'Error'
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray', self::Processing => 'warning', self::Published => 'success', self::Failed => 'danger'
        };
    }
}
