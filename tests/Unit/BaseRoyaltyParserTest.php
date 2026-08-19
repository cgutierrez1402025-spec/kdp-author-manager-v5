<?php

namespace Tests\Unit;

use App\Services\RoyaltyParsers\BaseRoyaltyParser;
use PHPUnit\Framework\TestCase;

class BaseRoyaltyParserTest extends TestCase
{
    public function test_it_parses_common_decimal_formats(): void
    {
        $parser = new class extends BaseRoyaltyParser
        {
            public function getPlatformCode(): string
            {
                return 'test';
            }

            public function mapRow(array $row): array
            {
                return ['value' => $this->parseDecimal($row['value'])];
            }
        };

        $this->assertSame(1234.56, $parser->parse(['value' => '1.234,56 €'])['value']);
        $this->assertSame(1234.56, $parser->parse(['value' => '$1,234.56'])['value']);
        $this->assertSame(-12.5, $parser->parse(['value' => '-12,50'])['value']);
    }
}
