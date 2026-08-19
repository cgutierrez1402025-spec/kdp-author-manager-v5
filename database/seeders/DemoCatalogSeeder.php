<?php

namespace Database\Seeders;

use App\Models\BookPromotion;
use App\Models\KdpMetadata;
use App\Models\ManuscriptVersion;
use App\Models\Marketplace;
use App\Models\Platform;
use App\Models\PromotionCost;
use App\Models\PromotionDailyResult;
use App\Models\Publication;
use App\Models\RoyaltyEntry;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkLanguage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $author = User::where('email', 'author@example.com')->firstOrFail();
            $platform = Platform::firstOrCreate(
                ['name' => 'Amazon KDP'],
                ['description' => 'Kindle Direct Publishing'],
            );
            $marketplace = Marketplace::firstOrCreate(
                ['platform_id' => $platform->id, 'code' => 'amazon.es'],
                ['name' => 'Amazon España', 'currency' => 'EUR'],
            );

            $genres = ['Fantasía', 'Misterio', 'Romance', 'Ciencia ficción', 'Historia'];
            $formats = ['ebook', 'paperback', 'hardcover'];

            foreach (range(1, 20) as $number) {
                $title = sprintf('Obra de demostración %02d', $number);
                $genre = $genres[($number - 1) % count($genres)];

                $work = Work::updateOrCreate(
                    ['slug' => 'demo-obra-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT)],
                    [
                        'user_id' => $author->id,
                        'title' => $title,
                        'title_internal' => $title,
                        'title_public' => $title,
                        'author_name' => 'Autor de demostración',
                        'genre' => $genre,
                        'work_type' => 'book',
                        'original_language' => 'es',
                        'status' => $number <= 15 ? 'publicada' : 'revision',
                        'target_audience' => $number % 2 === 0 ? 'Adulto' : 'Juvenil',
                        'description_marketing' => "Una obra de {$genre} preparada para probar el flujo editorial completo.",
                        'start_date' => now()->subMonths(24 - $number)->toDateString(),
                        'planned_publish_date' => now()->subMonths(20 - $number)->toDateString(),
                    ],
                );

                $language = WorkLanguage::updateOrCreate(
                    ['work_id' => $work->id, 'language_code' => 'es'],
                    ['translation_status' => 'original', 'ai_translation_used' => false],
                );

                $manuscript = ManuscriptVersion::updateOrCreate(
                    [
                        'work_id' => $work->id,
                        'work_language_id' => $language->id,
                        'version_number' => '1',
                    ],
                    [
                        'name' => 'Versión final de demostración',
                        'status' => 'final',
                        'html_content' => "<h1>{$title}</h1><p>Contenido editorial de demostración para comprobar versiones y capítulos.</p>",
                        'is_candidate' => false,
                        'is_final' => true,
                        'is_published' => $number <= 15,
                        'created_by' => $author->id,
                    ],
                );

                $publication = Publication::updateOrCreate(
                    ['asin' => 'DEMO'.str_pad((string) $number, 6, '0', STR_PAD_LEFT), 'marketplace_id' => $marketplace->id],
                    [
                        'work_id' => $work->id,
                        'work_language_id' => $language->id,
                        'manuscript_version_id' => $manuscript->id,
                        'platform_id' => $platform->id,
                        'format' => $formats[($number - 1) % count($formats)],
                        'status' => $number <= 15 ? 'published' : 'draft',
                        'price' => 2.99 + (($number % 5) * 1.00),
                        'currency' => 'EUR',
                        'territories' => 'worldwide',
                        'published_at' => $number <= 15 ? now()->subDays($number * 7) : null,
                    ],
                );

                KdpMetadata::updateOrCreate(
                    ['publication_id' => $publication->id],
                    [
                        'title' => $title,
                        'author' => 'Autor de demostración',
                        'description' => "Ficha KDP de prueba para {$title}.",
                        'keywords' => implode(', ', [$genre, 'demo', 'KDP']),
                        'categories' => [$genre, 'Demostración'],
                        'rights' => 'Todos los derechos disponibles',
                        'ai_declaration' => 'Contenido ficticio generado exclusivamente para pruebas.',
                    ],
                );

                $royalty = round(12.50 + ($number * 3.15), 2);
                RoyaltyEntry::updateOrCreate(
                    ['publication_id' => $publication->id, 'year' => 2026, 'month' => (($number - 1) % 8) + 1],
                    [
                        'paid_units' => 5 + ($number * 2),
                        'free_units' => $number % 4,
                        'kenp_pages' => $number * 125,
                        'royalty_ebook' => $royalty,
                        'royalty_paperback' => 0,
                        'royalty_hardcover' => 0,
                        'royalty_kenp' => round($number * 0.75, 2),
                        'total_royalty' => round($royalty + ($number * 0.75), 2),
                        'currency' => 'EUR',
                        'source_file' => 'demo-royalties-2026.csv',
                    ],
                );

                if ($number <= 6) {
                    $promotion = BookPromotion::updateOrCreate(
                        ['publication_id' => $publication->id, 'promotion_name' => "Campaña demo {$number}"],
                        [
                            'marketplace_id' => $marketplace->id,
                            'promotion_type' => $number % 2 === 0 ? 'discount' : 'free',
                            'start_date' => $number <= 2
                                ? now()->subDays($number)->toDateString()
                                : now()->subDays(14 - $number)->toDateString(),
                            'end_date' => $number <= 2
                                ? now()->addDays(8 - $number)->toDateString()
                                : now()->subDays(7 - $number)->toDateString(),
                            'normal_price' => $publication->price,
                            'promotional_price' => $number % 2 === 0 ? 0.99 : 0,
                            'status' => $number <= 2 ? 'active' : 'completed',
                            'objective' => 'Datos para validar métricas y ROI.',
                        ],
                    );

                    PromotionCost::updateOrCreate(
                        ['book_promotion_id' => $promotion->id, 'cost_type' => 'advertising'],
                        ['description' => 'Publicidad de demostración', 'amount' => 10 + $number, 'currency' => 'EUR', 'date' => $promotion->start_date],
                    );

                    PromotionDailyResult::updateOrCreate(
                        ['book_promotion_id' => $promotion->id, 'date' => $promotion->start_date],
                        [
                            'paid_units' => 3 * $number,
                            'free_units_promo' => 5 * $number,
                            'free_units_price_match' => 0,
                            'kenp_pages_read' => 200 * $number,
                            'gross_royalties' => 15 + ($number * 4),
                            'net_royalties' => 12 + ($number * 3),
                            'currency' => 'EUR',
                            'ranking_position' => 1000 - ($number * 75),
                        ],
                    );
                }
            }
        });
    }
}
