<?php

namespace App\Console\Commands;

use App\Models\ManuscriptVersion;
use App\Services\VersionManager;
use Illuminate\Console\Command;

class ManuscriptRefreshStats extends Command
{
    protected $signature = 'manuscript:refresh-stats {--work= : Work ID to refresh} {--dry-run : Show what would be updated}';

    protected $description = 'Recalculate statistics for all manuscript versions';

    public function handle(VersionManager $manager): int
    {
        $query = ManuscriptVersion::query();

        if ($this->option('work')) {
            $query->where('work_id', $this->option('work'));
        }

        $versions = $query->get();

        $this->info("Found {$versions->count()} manuscript versions to process.");

        if ($this->option('dry-run')) {
            $versions->each(function ($version) {
                $this->line("  - Version {$version->version_number}: {$version->word_count} words, {$version->chapter_count} chapters, {$version->image_count} images");
            });

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($versions->count());
        $updated = 0;

        $versions->each(function ($version) use ($manager, $bar, &$updated) {
            $manager->refreshStats($version);
            $updated++;
            $bar->advance();
        });

        $bar->finish();
        $this->newLine();
        $this->info("Updated stats for {$updated} manuscript versions.");

        return self::SUCCESS;
    }
}
