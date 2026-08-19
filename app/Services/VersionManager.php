<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\ManuscriptVersion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VersionManager
{
    protected const IMG_PATTERN = '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i';

    protected const CHAPTER_PATTERNS = [
        '/<h1[^>]*>(.*?)<\/h1>/is',
        '/<h2[^>]*>(.*?)<\/h2>/is',
    ];

    public function processVersion(ManuscriptVersion $version): void
    {
        $content = $version->html_content;

        if (! $content) {
            return;
        }

        $version->file_hash = hash('sha256', $content);
        $version->word_count = $this->countWords($content);
        $version->chapter_count = 0;
        $version->image_count = $this->extractImages($content, $version);

        $this->extractChapters($content, $version);

        if ($version->parent_version_id) {
            $version->change_summary = $this->generateDiff($version);
        }

        $version->saveQuietly();
    }

    public function refreshStats(ManuscriptVersion $version): void
    {
        $this->processVersion($version);
        $this->recalculateChapters($version);
    }

    protected function countWords(string $content): int
    {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);

        return (int) str_word_count(trim($text));
    }

    protected function extractImages(string $content, ManuscriptVersion $version): int
    {
        preg_match_all(self::IMG_PATTERN, $content, $matches);
        $count = count($matches[1] ?? []);

        foreach ($matches[1] ?? [] as $imgPath) {
            $this->extractImageToTemp($imgPath, $version);
        }

        return $count;
    }

    protected function extractImageToTemp(string $imgPath, ManuscriptVersion $version): void
    {
        $tempDir = storage_path("app/temp/images/{$version->id}");

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        if (filter_var($imgPath, FILTER_VALIDATE_URL)) {
            try {
                $contents = file_get_contents($imgPath);
                if ($contents) {
                    $filename = basename(parse_url($imgPath, PHP_URL_PATH));
                    file_put_contents("{$tempDir}/{$filename}", $contents);
                }
            } catch (\Exception $e) {
                Log::warning("Could not download image: {$imgPath}");
            }
        } elseif (Storage::exists($imgPath)) {
            $contents = Storage::get($imgPath);
            $filename = basename($imgPath);
            file_put_contents("{$tempDir}/{$filename}", $contents);
        }
    }

    protected function extractChapters(string $content, ManuscriptVersion $version): int
    {
        $order = 0;
        $chapters = [];

        foreach (self::CHAPTER_PATTERNS as $pattern) {
            preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[0] as $match) {
                $order++;
                $title = trim(strip_tags($match[0]));
                $position = $match[1];

                $chapters[] = [
                    'work_id' => $version->work_id,
                    'manuscript_version_id' => $version->id,
                    'chapter_order' => $order,
                    'level' => $this->getHeadingLevel($match[0]),
                    'title' => $title,
                    'html_id' => $this->extractHtmlId($match[0]),
                    'start_position' => $position,
                    'status' => 'extracted',
                ];
            }
        }

        Chapter::where('manuscript_version_id', $version->id)->delete();

        foreach ($chapters as $chapter) {
            Chapter::create($chapter);
        }

        $version->chapter_count = count($chapters);
        $version->saveQuietly();

        return count($chapters);
    }

    protected function recalculateChapters(ManuscriptVersion $version): void
    {
        $content = $version->html_content;

        if (! $content) {
            return;
        }

        $this->extractChapters($content, $version);
    }

    protected function getHeadingLevel(string $match): int
    {
        if (str_contains($match, '<h1')) {
            return 1;
        }

        if (str_contains($match, '<h2')) {
            return 2;
        }

        return 1;
    }

    protected function extractHtmlId(string $match): ?string
    {
        if (preg_match('/id=["\']([^"\']+)["\']/', $match, $idMatches)) {
            return $idMatches[1];
        }

        return null;
    }

    protected function generateDiff(ManuscriptVersion $version): string
    {
        $parent = $version->parentVersion;

        if (! $parent || ! $parent->html_content) {
            return 'Initial version';
        }

        $changes = [];

        $oldWords = $this->countWords($parent->html_content);
        $newWords = $version->word_count;
        $changes[] = "Words: {$oldWords} → {$newWords} (".($newWords - $oldWords).')';

        $oldImages = $parent->image_count ?? 0;
        $newImages = $version->image_count ?? 0;
        $changes[] = "Images: {$oldImages} → {$newImages}";

        $oldChapters = $parent->chapter_count ?? 0;
        $newChapters = $version->chapter_count ?? 0;
        $changes[] = "Chapters: {$oldChapters} → {$newChapters}";

        return implode('; ', $changes);
    }
}
