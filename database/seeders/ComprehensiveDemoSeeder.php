<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprehensiveDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();
            $admin = DB::table('users')->where('email', 'admin@kdpmanager.local')->value('id');
            $author = DB::table('users')->where('email', 'author@example.com')->value('id');
            $work = DB::table('works')->where('slug', 'demo-obra-01')->first();
            $language = DB::table('work_languages')->where('work_id', $work->id)->where('language_code', 'es')->first();
            $manuscript = DB::table('manuscript_versions')->where('work_id', $work->id)->first();
            $chapter = DB::table('chapters')->where('manuscript_version_id', $manuscript->id)->first();
            $publication = DB::table('publications')->where('work_id', $work->id)->first();
            $platform = DB::table('platforms')->where('name', 'Amazon KDP')->first();
            $marketplace = DB::table('marketplaces')->where('platform_id', $platform->id)->where('code', 'amazon.es')->first();

            foreach ([['es', 'Español'], ['en', 'Inglés'], ['fr', 'Francés'], ['de', 'Alemán']] as [$code, $name]) {
                $this->record('languages', ['code' => $code], ['name' => $name, 'native_name' => $name, 'active' => true]);
            }

            $series = $this->record('series', ['user_id' => $author, 'title' => 'Crónicas del Horizonte'], [
                'description' => 'Serie de demostración que agrupa varias obras relacionadas.',
            ]);
            DB::table('works')->where('id', $work->id)->update(['series_id' => $series, 'series_number' => 1]);

            $edition = $this->record('editions', ['work_id' => $work->id, 'work_language_id' => $language->id, 'edition_number' => 1], [
                'edition_name' => 'Primera edición revisada', 'edition_type' => 'commercial', 'notes' => 'Edición demo lista para publicación.',
            ]);
            DB::table('manuscript_versions')->where('id', $manuscript->id)->update(['edition_id' => $edition]);

            $source = $this->record('sources', ['work_id' => $work->id, 'title' => 'Archivo histórico de referencia'], [
                'author' => 'Biblioteca Nacional', 'year' => '2024', 'source_type' => 'website', 'language_code' => 'es',
                'url' => 'https://example.com/fuente-demo', 'consulted_at' => now()->subMonths(2)->toDateString(),
                'citation' => 'Biblioteca Nacional (2024). Archivo histórico de referencia.', 'summary' => 'Contexto documental utilizado en el capítulo inicial.',
                'rights_status' => 'public_domain', 'reliability' => 'high',
            ]);
            $this->record('source_usages', ['source_id' => $source, 'work_id' => $work->id], [
                'manuscript_version_id' => $manuscript->id, 'chapter_id' => $chapter?->id, 'fragment' => 'Referencia contextual del capítulo 1.',
                'usage_type' => 'background', 'verified' => true,
            ]);

            $aiTool = $this->record('ai_tools', ['user_id' => $author, 'name' => 'OpenAI editorial demo'], [
                'provider' => 'openai', 'tool_type' => 'text_generation', 'model' => 'gpt-4o-mini',
                'strengths' => 'Revisión, ideación y resumen.', 'weaknesses' => 'Requiere revisión humana.', 'quality_score' => 4,
            ]);
            $aiTask = $this->record('ai_tasks', ['work_id' => $work->id, 'task_type' => 'improve_description'], [
                'preferred_ai_tool_id' => $aiTool, 'notes' => 'Mejorar la sinopsis conservando el tono original.',
            ]);
            $prompt = $this->record('prompts', ['work_id' => $work->id, 'title' => 'Sinopsis comercial demo'], [
                'ai_tool_id' => $aiTool, 'task_id' => $aiTask, 'prompt_text' => 'Redacta una sinopsis comercial breve y fiel al manuscrito.',
                'language_code' => 'es', 'purpose' => 'marketing', 'result_summary' => 'Sinopsis revisada por el autor.',
                'rating' => 5, 'reused' => false, 'generated_final_content' => true, 'result_text' => 'Una historia sobre memoria, cambio y nuevos horizontes.',
            ]);

            $illustration = $this->record('illustrations', ['work_id' => $work->id, 'title' => 'Mapa del mundo narrativo'], [
                'work_language_id' => $language->id, 'description' => 'Mapa interior de demostración.', 'image_type' => 'interior',
                'file_original' => 'demo/illustrations/mapa-original.png', 'file_optimized' => 'demo/illustrations/mapa-web.png',
                'thumbnail' => 'demo/illustrations/mapa-thumb.png', 'format' => 'png', 'width' => 1600, 'height' => 1200,
                'resolution' => 300, 'ai_tool_id' => $aiTool, 'prompt_id' => $prompt, 'rights_status' => 'owned',
                'status' => 'approved', 'approved' => true,
            ]);
            $this->record('illustration_versions', ['illustration_id' => $illustration, 'version_number' => 1], [
                'file_path' => 'demo/illustrations/mapa-v1.png', 'change_summary' => 'Versión inicial aprobada.', 'created_by' => $author,
            ]);
            $this->record('illustration_anchors', ['illustration_id' => $illustration, 'manuscript_version_id' => $manuscript->id], [
                'chapter_id' => $chapter?->id, 'anchor_type' => 'after_heading', 'position_type' => 'block',
                'search_text' => $chapter?->title ?? 'Capítulo 1', 'insertion_mode' => 'after', 'confidence' => 'high', 'status' => 'confirmed',
            ]);

            $this->record('kdp_select_periods', ['publication_id' => $publication->id, 'start_date' => now()->subDays(30)->toDateString()], [
                'end_date' => now()->addDays(20)->toDateString(), 'auto_renewal' => true, 'status' => 'active',
            ]);
            $this->record('payment_thresholds', ['platform_id' => $platform->id, 'marketplace_id' => $marketplace->id, 'currency' => 'EUR'], [
                'threshold_amount' => 100,
            ]);
            $this->record('royalty_payments', ['platform_id' => $platform->id, 'period_start' => '2026-01-01'], [
                'marketplace_id' => $marketplace->id, 'period_end' => '2026-01-31', 'expected_amount' => 245.80,
                'received_amount' => 245.80, 'withheld_tax' => 0, 'currency' => 'EUR', 'expected_date' => '2026-03-31',
                'received_date' => '2026-03-29', 'status' => 'received', 'notes' => 'Pago demo conciliado.',
            ]);

            $award = $this->record('awards', ['name' => 'Premio Narrativa Horizonte 2026'], [
                'organizer' => 'Fundación Letras Abiertas', 'country' => 'España', 'city' => 'Madrid', 'genre' => 'Narrativa',
                'language_code' => 'es', 'prize_amount' => '3.000 EUR', 'url' => 'https://example.com/premio-demo',
                'opening_date' => '2026-01-15', 'deadline' => '2026-09-30', 'expected_resolution_date' => '2026-12-15',
                'requires_unpublished' => false, 'forbids_self_publishing' => false, 'forbids_simultaneous_submissions' => false,
                'requires_anonymity' => true, 'allows_pseudonym' => true, 'min_words' => 30000, 'max_words' => 120000,
            ]);
            $this->record('award_submissions', ['work_id' => $work->id, 'award_id' => $award], [
                'work_language_id' => $language->id, 'manuscript_version_id' => $manuscript->id,
                'submission_date' => now()->subDays(10)->toDateString(), 'submitted_title' => $work->title_public,
                'pseudonym_used' => 'Autor Horizonte', 'status' => 'submitted', 'block_publication' => false,
            ]);

            $event = $this->record('book_events', ['user_id' => $author, 'title' => 'Presentación y firma demo'], [
                'event_type' => 'book_signing', 'event_date' => now()->addDays(14)->toDateString(), 'start_time' => '18:00',
                'end_time' => '20:00', 'location_name' => 'Librería Central', 'city' => 'Madrid', 'country' => 'España',
                'status' => 'planned', 'expected_attendance' => 45, 'notes' => 'Evento visible en el dashboard.',
            ]);
            $this->record('event_books', ['event_id' => $event, 'work_id' => $work->id], [
                'work_language_id' => $language->id, 'edition_id' => $edition, 'copies_brought' => 30,
                'copies_sold' => 8, 'unit_sale_price' => 15.90, 'income_amount' => 127.20,
            ]);

            $printRun = $this->record('physical_print_runs', ['work_id' => $work->id, 'print_date' => '2026-07-01'], [
                'work_language_id' => $language->id, 'format' => 'paperback', 'copies_printed' => 200,
                'unit_cost' => 3.25, 'total_cost' => 650, 'supplier' => 'Imprenta Demo', 'invoice_number' => 'DEMO-2026-001',
            ]);
            $location = $this->record('stock_locations', ['user_id' => $author, 'name' => 'Almacén principal'], [
                'type' => 'warehouse', 'address' => 'Calle Demo 10', 'city' => 'Madrid', 'country' => 'España', 'active' => true,
            ]);
            $this->record('stock_movements', ['work_id' => $work->id, 'movement_date' => '2026-07-02', 'movement_type' => 'entry'], [
                'work_language_id' => $language->id, 'print_run_id' => $printRun, 'to_location_id' => $location,
                'quantity' => 200, 'unit_cost' => 3.25, 'reference' => 'Entrada tirada DEMO-2026-001', 'created_by' => $author,
            ]);
            $point = $this->record('distribution_points', ['user_id' => $author, 'name' => 'Librería Central'], [
                'type' => 'bookstore', 'contact_person' => 'Ana Librera', 'email' => 'libreria@example.com', 'city' => 'Madrid',
                'country' => 'España', 'accepts_consignment' => true, 'accepts_direct_purchase' => true, 'accepts_events' => true,
                'default_discount_percentage' => 30, 'active' => true,
            ]);
            $delivery = $this->record('book_deliveries', ['work_id' => $work->id, 'distribution_point_id' => $point, 'delivery_date' => '2026-07-10'], [
                'work_language_id' => $language->id, 'physical_print_run_id' => $printRun, 'quantity_delivered' => 25,
                'retail_price' => 15.90, 'author_price' => 11.13, 'agreement_type' => 'consignment', 'status' => 'active',
                'delivered_by' => $author,
            ]);
            $visit = $this->record('distribution_visits', ['distribution_point_id' => $point, 'visit_date' => now()->subDays(2)->toDateString()], [
                'visited_by' => $author, 'purpose' => 'inventory_review', 'notes' => 'Revisión de ventas y reposición.',
            ]);
            $this->record('delivery_reviews', ['book_delivery_id' => $delivery, 'distribution_visit_id' => $visit], [
                'copies_remaining_before' => 25, 'copies_sold' => 8, 'copies_returned' => 0, 'copies_restocked' => 5,
                'copies_remaining_after' => 22, 'amount_to_collect' => 89.04, 'amount_collected' => 89.04,
                'amount_pending' => 0, 'payment_method' => 'transfer', 'review_status' => 'completed',
            ]);

            $asset = $this->record('promotional_assets', ['user_id' => $author, 'work_id' => $work->id, 'title' => 'Banner lanzamiento demo'], [
                'work_language_id' => $language->id, 'platform_id' => $platform->id, 'marketplace_id' => $marketplace->id,
                'asset_type' => 'banner', 'description' => 'Banner horizontal para redes sociales.',
                'file_path' => 'demo/assets/banner-lanzamiento.jpg', 'thumbnail_path' => 'demo/assets/banner-thumb.jpg',
                'file_format' => 'jpg', 'width' => 1200, 'height' => 628, 'alt_text' => 'Banner de la obra de demostración',
                'ai_tool_id' => $aiTool, 'prompt_id' => $prompt, 'rights_status' => 'owned', 'status' => 'approved',
            ]);
            $this->record('asset_versions', ['promotional_asset_id' => $asset, 'version_number' => 1], [
                'file_path' => 'demo/assets/banner-lanzamiento-v1.jpg', 'change_summary' => 'Diseño inicial aprobado.', 'created_by' => $author,
            ]);
            $aplus = $this->record('aplus_projects', ['publication_id' => $publication->id, 'asin' => $publication->asin], [
                'user_id' => $author, 'work_id' => $work->id, 'work_language_id' => $language->id, 'platform_id' => $platform->id,
                'marketplace_id' => $marketplace->id, 'language_code' => 'es', 'title' => 'Contenido A+ demo',
                'commercial_goal' => 'Presentar universo y beneficios de lectura.', 'status' => 'draft',
            ]);
            $this->record('aplus_modules', ['aplus_project_id' => $aplus, 'module_order' => 1], [
                'module_type' => 'standard_image_text', 'headline' => 'Descubre un nuevo horizonte',
                'body_text' => 'Una introducción visual al universo de la obra.', 'image_asset_id' => $asset,
                'alt_text' => 'Imagen promocional de la obra', 'status' => 'ready',
            ]);

            $batch = $this->record('import_batches', ['file_hash' => hash('sha256', 'demo-import-royalties-2026')], [
                'user_id' => $author, 'import_type' => 'prior_royalties', 'report_period' => '2026-01-01', 'source_system' => 'Amazon KDP',
                'original_file_path' => 'demo/imports/royalties-2026.csv', 'original_file_name' => 'royalties-2026.csv',
                'detected_format' => 'csv', 'status' => 'completed', 'started_at' => now()->subHour(), 'finished_at' => now(),
                'total_rows' => 1, 'imported_rows' => 1, 'processed_by_ai' => true, 'ai_tool_id' => $aiTool,
            ]);
            $this->record('import_mappings', ['import_batch_id' => $batch, 'external_column_name' => 'Royalty'], [
                'internal_entity' => 'royalty_entries', 'internal_field' => 'total_royalty', 'confidence' => 98.5,
                'mapped_by_ai' => true, 'confirmed_by_user' => true,
            ]);
            $royalty = DB::table('royalty_entries')->where('publication_id', $publication->id)->first();
            $catalogItem = $this->record('kdp_catalog_items', ['user_id' => $author, 'identity_key' => hash('sha256', 'demo-kdp-catalog-item')], [
                'work_id' => $work->id, 'publication_id' => $publication->id, 'asin' => $publication->asin,
                'title' => $work->title_public, 'author' => $work->author_name, 'format' => $publication->format,
                'marketplaces' => json_encode(['Amazon.es']), 'review_status' => 'linked',
                'first_seen_at' => now()->subMonth(), 'last_seen_at' => now(),
            ]);
            $this->record('kdp_report_rows', ['user_id' => $author, 'row_fingerprint' => hash('sha256', 'demo-kdp-row-2026')], [
                'import_batch_id' => $batch, 'publication_id' => $publication->id, 'kdp_catalog_item_id' => $catalogItem,
                'report_type' => 'prior_royalties',
                'report_period' => '2026-01-01', 'title' => $work->title_public, 'author' => $work->author_name,
                'asin' => $publication->asin, 'format' => $publication->format, 'marketplace' => 'Amazon.es',
                'currency' => 'EUR', 'transaction_type' => 'Sale', 'units_sold' => 42, 'units_refunded' => 2,
                'net_units_sold' => 40, 'kenp_read' => 8500, 'total_earnings' => $royalty->total_royalty,
                'raw_data' => json_encode(['ASIN' => $publication->asin, 'Total Earnings' => $royalty->total_royalty]),
                'normalized_data' => json_encode(['asin' => $publication->asin, 'total_earnings' => $royalty->total_royalty]),
            ]);
            $this->record('import_rows', ['import_batch_id' => $batch, 'row_number' => 1], [
                'raw_data_json' => json_encode(['ASIN' => $publication->asin, 'Royalty' => $royalty->total_royalty]),
                'normalized_data_json' => json_encode(['publication_id' => $publication->id, 'total_royalty' => $royalty->total_royalty]),
                'validation_status' => 'valid', 'linked_work_id' => $work->id, 'linked_publication_id' => $publication->id,
                'linked_royalty_entry_id' => $royalty->id,
            ]);
            $this->record('import_errors', ['import_batch_id' => $batch, 'error_type' => 'decimal_separator'], [
                'severity' => 'warning', 'message' => 'Separador decimal detectado y normalizado automáticamente.',
                'row_number' => 1, 'field_name' => 'Royalty', 'suggested_solution' => 'Confirmar el formato regional.', 'resolved' => true,
            ]);
            $this->record('calibre_imports', ['import_batch_id' => $batch, 'title' => $work->title_public], [
                'calibre_book_id' => 'DEMO-001', 'author' => $work->author_name, 'series' => 'Crónicas del Horizonte',
                'series_index' => 1, 'language_code' => 'es', 'tags' => 'ficción, demo',
                'available_formats_json' => json_encode(['epub', 'pdf']), 'matched_work_id' => $work->id, 'status' => 'matched',
            ]);
            $ocr = $this->record('ocr_jobs', ['user_id' => $author, 'input_file_path' => 'demo/ocr/capitulo-escaneado.pdf'], [
                'source_id' => $source, 'import_batch_id' => $batch, 'ocr_engine' => 'tesseract', 'language_code' => 'es',
                'output_txt_path' => 'demo/ocr/capitulo.txt', 'confidence_score' => 96.4, 'status' => 'completed',
                'started_at' => now()->subMinutes(15), 'finished_at' => now()->subMinutes(10),
            ]);
            $this->record('ocr_text_versions', ['ocr_job_id' => $ocr, 'version_type' => 'reviewed'], [
                'text_content' => 'Texto OCR de demostración corregido y revisado.', 'processed_by_ai' => true,
                'ai_tool_id' => $aiTool, 'human_reviewed' => true, 'reviewed_by' => $author, 'reviewed_at' => now(),
            ]);
            $this->record('translation_jobs', ['work_id' => $work->id, 'target_language_code' => 'en'], [
                'source_work_language_id' => $language->id, 'source_manuscript_version_id' => $manuscript->id,
                'tool_type' => 'ai_assisted', 'tool_name' => 'OpenAI editorial demo', 'ai_tool_id' => $aiTool,
                'calibre_used' => false, 'input_file_path' => 'demo/manuscripts/original-es.epub',
                'output_file_path' => 'demo/translations/draft-en.epub', 'status' => 'in_progress',
                'human_review_status' => 'pending', 'started_at' => now()->subDays(3),
            ]);

            $task = $this->record('tasks', ['work_id' => $work->id, 'title' => 'Revisar traducción inglesa'], [
                'assigned_to' => $author, 'description' => 'Revisar terminología y coherencia del primer capítulo.',
                'task_type' => 'editing', 'priority' => 'high', 'status' => 'pending', 'due_date' => now()->addDays(7)->toDateString(),
                'created_by' => $admin,
            ]);
            $this->record('comments', ['user_id' => $admin, 'task_id' => $task], [
                'work_id' => $work->id, 'manuscript_version_id' => $manuscript->id, 'chapter_id' => $chapter?->id,
                'comment' => 'Comprueba especialmente nombres propios y continuidad temporal.',
            ]);
            $checklist = $this->record('checklists', ['work_id' => $work->id, 'name' => 'Preparación para publicación'], [
                'description' => 'Controles editoriales previos a publicar la edición.',
            ]);
            foreach (['ISBN y metadatos revisados', 'Cubierta aprobada', 'Prueba final del manuscrito'] as $index => $item) {
                $this->record('checklist_items', ['checklist_id' => $checklist, 'item' => $item], [
                    'is_checked' => $index < 2, 'checked_by' => $index < 2 ? $author : null,
                    'checked_at' => $index < 2 ? $now : null, 'order' => $index + 1,
                ]);
            }
            foreach (['demostración', 'ficción', 'publicado', 'KDP'] as $tagName) {
                $tag = $this->record('tags', ['name' => $tagName]);
                DB::table('taggables')->insertOrIgnore(['tag_id' => $tag, 'taggable_type' => 'App\\Models\\Work', 'taggable_id' => $work->id]);
            }

            $viewPermission = DB::table('permissions')->where('name', 'view_works')->value('id');
            DB::table('model_has_permissions')->insertOrIgnore([
                'permission_id' => $viewPermission, 'model_type' => User::class, 'model_id' => $author,
            ]);
        });
    }

    private function record(string $table, array $identity, array $values = []): int
    {
        $timestamps = [];
        $columns = collect(DB::select("PRAGMA table_info({$table})"))->pluck('name');
        $values = array_intersect_key($values, array_flip($columns->all()));
        if ($columns->contains('created_at')) {
            $timestamps['created_at'] = now();
        }
        if ($columns->contains('updated_at')) {
            $timestamps['updated_at'] = now();
        }

        DB::table($table)->updateOrInsert($identity, array_merge($values, $timestamps));

        return (int) DB::table($table)->where($identity)->value('id');
    }
}
