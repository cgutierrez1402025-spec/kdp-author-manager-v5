<?php

namespace App\Enums;

enum PublicationFormat: string
{
    case Ebook = 'ebook';
    case Paperback = 'paperback';
    case Hardcover = 'hardcover';
    case Audiobook = 'audiobook';

    public function label(): string
    {
        return match ($this) {
            self::Ebook => 'eBook', self::Paperback => 'Tapa Blanda', self::Hardcover => 'Tapa Dura', self::Audiobook => 'Audiolibro'
        };
    }
}
