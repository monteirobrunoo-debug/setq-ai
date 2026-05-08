<?php

namespace App\Console\Commands;

use App\Models\BookChunk;
use Illuminate\Console\Command;
use Smalot\PdfParser\Parser;
use Symfony\Component\Finder\Finder;

/**
 * Ingest PDFs into the SETQ.AI book_chunks table.
 *
 * Usage:
 *   php artisan books:ingest /path/to/books --domain=back_office
 *   php artisan books:ingest /path/to/books --domain=front_office --refresh
 *
 * One row per PDF page (≥50 chars of extracted text). FTS5 index is
 * updated automatically via the trigger created in the migration.
 *
 * Idempotent for the same book_key — re-runs delete the previous chunks
 * for that book before re-inserting.
 */
class IngestBooksCommand extends Command
{
    protected $signature = 'books:ingest
                            {path : Folder containing the PDFs (recursive)}
                            {--domain= : back_office | front_office}
                            {--refresh : truncate book_chunks before ingesting}';

    protected $description = 'Ingest PDF books into the SETQ.AI knowledge base';

    public function handle(): int
    {
        $path   = $this->argument('path');
        $domain = (string) $this->option('domain');

        if (!is_dir($path)) {
            $this->error("Folder does not exist: {$path}");
            return self::FAILURE;
        }
        if (!in_array($domain, ['back_office', 'front_office'], true)) {
            $this->error("--domain must be back_office or front_office");
            return self::FAILURE;
        }

        if ($this->option('refresh')) {
            BookChunk::truncate();
            $this->warn('🗑  book_chunks truncated');
        }

        $finder = (new Finder())->files()->in($path)->name('*.pdf');
        $count  = $finder->count();
        $this->info("📚 {$count} PDFs to ingest into [{$domain}]");

        $totalChunks = 0;
        $totalErrors = 0;

        foreach ($finder as $file) {
            $bookKey   = preg_replace('/\.pdf$/i', '', $file->getFilename());
            $bookKey   = preg_replace('/[^A-Za-z0-9_\-]/', '-', $bookKey);
            $bookKey   = mb_substr(trim($bookKey, '-'), 0, 90);
            $bookTitle = ucwords(str_replace(['-', '_'], ' ', preg_replace('/^\d+-/', '', $bookKey)));

            // Idempotency — wipe existing chunks for this book key
            BookChunk::where('book_key', $bookKey)->delete();

            try {
                $parser = new Parser();
                $pdf    = $parser->parseFile($file->getRealPath());
                $pages  = $pdf->getPages();

                if (empty($pages)) {
                    $this->warn("  · {$file->getFilename()} — no text (image-only PDF?)");
                    continue;
                }

                $bar = $this->output->createProgressBar(count($pages));
                $bar->start();
                $kept = 0;

                foreach ($pages as $i => $page) {
                    $text = trim((string) $page->getText());
                    if (mb_strlen($text) < 50) { $bar->advance(); continue; }
                    BookChunk::create([
                        'domain'     => $domain,
                        'book_key'   => $bookKey,
                        'book_title' => mb_substr($bookTitle, 0, 250),
                        'page_no'    => $i + 1,
                        'content'    => mb_substr($text, 0, 8000),
                    ]);
                    $kept++;
                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
                $this->info("  ✓ {$file->getFilename()} — {$kept} chunks");
                $totalChunks += $kept;
            } catch (\Throwable $e) {
                $totalErrors++;
                $this->error("  ✗ {$file->getFilename()}: " . $e->getMessage());
            }
        }

        $this->info('');
        $this->info("📊 Done — {$totalChunks} chunks, {$totalErrors} errors");
        return self::SUCCESS;
    }
}
