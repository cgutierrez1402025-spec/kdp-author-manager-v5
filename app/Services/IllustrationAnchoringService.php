<?php

namespace App\Services;

use App\Models\Illustration;
use App\Models\IllustrationAnchor;
use App\Models\ManuscriptVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class IllustrationAnchoringService
{
    public function applyToManuscript(IllustrationAnchor $anchor): array
    {
        $manuscript = ManuscriptVersion::find($anchor->manuscript_version_id);

        if (! $manuscript) {
            return [
                'success' => false,
                'error' => 'Manuscript version not found',
            ];
        }

        $illustration = Illustration::find($anchor->illustration_id);

        if (! $illustration) {
            return [
                'success' => false,
                'error' => 'Illustration not found',
            ];
        }

        try {
            $html = $manuscript->html_content;
            $modifiedHtml = $this->insertImageAtAnchor($html, $anchor, $illustration);

            if ($modifiedHtml === $html) {
                return [
                    'success' => false,
                    'error' => 'No insertion point found',
                ];
            }

            $newVersion = DB::transaction(function () use ($manuscript, $modifiedHtml, $illustration, $anchor) {
                $newVersion = $manuscript->createChildVersion([
                    'html_content' => $modifiedHtml,
                    'change_summary' => "Added illustration: {$illustration->title}",
                ]);

                $anchor->update([
                    'applied' => true,
                    'applied_html_content' => $modifiedHtml,
                    'applied_version_id' => $newVersion->id,
                    'applied_at' => now(),
                ]);

                $illustration->increment('usage_count');

                return $newVersion;
            });

            return [
                'success' => true,
                'new_version' => $newVersion,
                'html_preview' => $this->getPreviewHtml($modifiedHtml, $anchor),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function insertImageAtAnchor(string $html, IllustrationAnchor $anchor, Illustration $illustration): string
    {
        if ($anchor->html_marker) {
            return $this->insertByHtmlMarker($html, $anchor, $illustration);
        }

        if ($anchor->css_selector) {
            return $this->insertByCssSelector($html, $anchor, $illustration);
        }

        if ($anchor->search_text) {
            return $this->insertBySearchText($html, $anchor, $illustration);
        }

        return $html;
    }

    protected function insertByHtmlMarker(string $html, IllustrationAnchor $anchor, Illustration $illustration): string
    {
        $imageTag = $this->buildImageTag($illustration);

        return str_replace(
            $anchor->html_marker,
            $anchor->html_marker.$imageTag,
            $html
        );
    }

    protected function insertByCssSelector(string $html, IllustrationAnchor $anchor, Illustration $illustration): string
    {
        $crawler = new Crawler($html);
        $imageTag = $this->buildImageTag($illustration);

        $crawler->filter($anchor->css_selector)->each(function (Crawler $node, int $i) use ($imageTag): void {
            if ($i === 0) {
                $node->getNode(0)->appendChild(
                    $node->getDocument()->createElement('div', $imageTag)
                );
            }
        });

        return $crawler->html();
    }

    protected function insertBySearchText(string $html, IllustrationAnchor $anchor, Illustration $illustration): string
    {
        $imageTag = $this->buildImageTag($illustration);

        return str_replace(
            $anchor->search_text,
            $anchor->search_text.$imageTag,
            $html
        );
    }

    protected function buildImageTag(Illustration $illustration): string
    {
        $path = $illustration->file_optimized ?? $illustration->file_original;
        $url = $path ? Storage::url($path) : $illustration->external_url ?? '';

        $alt = e($illustration->title);
        $alignment = 'inline';

        return sprintf(
            '<img src="%s" alt="%s" class="illustration %s" data-illustration-id="%d" />',
            e($url),
            $alt,
            $alignment,
            $illustration->id
        );
    }

    protected function getPreviewHtml(string $html, IllustrationAnchor $anchor): string
    {
        $anchorRegex = '/(<[^>]*>)?(.*?)'.preg_quote($anchor->search_text ?? $anchor->html_marker ?? '', '/').'(.*?)<\/p>/is';

        if (preg_match($anchorRegex, $html, $matches)) {
            return $matches[0] ?? $html;
        }

        return substr($html, 0, 500);
    }

    public function previewInsertion(IllustrationAnchor $anchor): array
    {
        $manuscript = ManuscriptVersion::find($anchor->manuscript_version_id);

        if (! $manuscript) {
            return [
                'success' => false,
                'error' => 'Manuscript version not found',
            ];
        }

        $illustration = Illustration::find($anchor->illustration_id);

        if (! $illustration) {
            return [
                'success' => false,
                'error' => 'Illustration not found',
            ];
        }

        $html = $manuscript->html_content;
        $previewHtml = $this->insertImageAtAnchor($html, $anchor, $illustration);

        return [
            'success' => true,
            'html' => $previewHtml,
            'image_tag' => $this->buildImageTag($illustration),
        ];
    }
}
