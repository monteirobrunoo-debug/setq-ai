<?php

namespace App\Services;

use App\Models\BookChunk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Keyword search over the agent knowledge base.
 *
 * Uses SQLite FTS5 when available (built-in, very fast). Falls back to
 * ILIKE on other drivers. Each result is a chunk that the agent can
 * inject into its system prompt as additional context.
 */
class BookSearchService
{
    /**
     * @param  string  $query    User question — words are extracted, stop-words dropped
     * @param  string  $domain   back_office | front_office
     * @param  int     $limit    How many chunks to return
     * @return array<int, array{book_key:string, book_title:string, page_no:int, content:string}>
     */
    public function search(string $query, string $domain, int $limit = 3): array
    {
        $query = trim($query);
        if (mb_strlen($query) < 4) return [];

        $words = $this->extractWords($query);
        if (empty($words)) return [];

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return $this->searchFts5($words, $domain, $limit);
        }
        return $this->searchLike($words, $domain, $limit);
    }

    /**
     * Pre-formatted markdown block ready to splice into a system prompt.
     * Empty string when no matches — caller can concat unconditionally.
     */
    public function asPromptBlock(string $query, string $domain, int $limit = 3): string
    {
        $hits = $this->search($query, $domain, $limit);
        if (empty($hits)) return '';

        $lines = [
            '═══ REFERENCE LIBRARY (cite when relevant; if not relevant, ignore) ═══',
            '',
        ];
        foreach ($hits as $i => $h) {
            $n = $i + 1;
            $lines[] = "[{$n}] {$h['book_title']} (p. {$h['page_no']})";
            // Trim chunks aggressively — system prompt budget matters
            $content = mb_substr(preg_replace('/\s+/', ' ', $h['content']), 0, 1200);
            $lines[] = $content;
            $lines[] = '';
        }
        $lines[] = '═══ END REFERENCE LIBRARY ═══';
        return implode("\n", $lines);
    }

    /** SQLite FTS5 search (fast, ranked) */
    protected function searchFts5(array $words, string $domain, int $limit): array
    {
        // Build FTS5 query — "word1 OR word2 OR word3" works well for recall
        $ftsQuery = implode(' OR ', array_map(fn($w) => '"' . str_replace('"', '', $w) . '"', $words));

        try {
            $rows = DB::select('
                SELECT b.book_key, b.book_title, b.page_no, b.content,
                       book_chunks_fts.rank as rank
                FROM book_chunks_fts
                JOIN book_chunks b ON b.id = book_chunks_fts.rowid
                WHERE book_chunks_fts MATCH ? AND b.domain = ?
                ORDER BY book_chunks_fts.rank
                LIMIT ?
            ', [$ftsQuery, $domain, $limit]);

            return array_map(fn($r) => [
                'book_key'   => $r->book_key,
                'book_title' => $r->book_title,
                'page_no'    => (int) $r->page_no,
                'content'    => $r->content,
            ], $rows);
        } catch (\Throwable $e) {
            Log::warning('BookSearch FTS5 failed: ' . $e->getMessage());
            return $this->searchLike($words, $domain, $limit);
        }
    }

    /** Generic ILIKE search fallback */
    protected function searchLike(array $words, string $domain, int $limit): array
    {
        $q = BookChunk::query()->where('domain', $domain);
        $q->where(function ($w) use ($words) {
            foreach ($words as $word) {
                $w->orWhere('content', 'LIKE', '%' . $word . '%');
            }
        });

        $candidates = $q->limit(20)->get(['book_key', 'book_title', 'page_no', 'content'])->all();

        // PHP-side rank by # words matched
        usort($candidates, function ($a, $b) use ($words) {
            $score = function ($c) use ($words) {
                $low = mb_strtolower((string) $c->content);
                $n = 0;
                foreach ($words as $w) if (str_contains($low, $w)) $n++;
                return $n;
            };
            return $score($b) <=> $score($a);
        });

        return array_map(fn($c) => [
            'book_key'   => $c->book_key,
            'book_title' => $c->book_title,
            'page_no'    => (int) $c->page_no,
            'content'    => $c->content,
        ], array_slice($candidates, 0, $limit));
    }

    protected function extractWords(string $query): array
    {
        $stop = ['the', 'and', 'or', 'a', 'an', 'is', 'are', 'was', 'were', 'be', 'been', 'being',
                 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should',
                 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'this', 'that', 'these', 'those',
                 'of', 'in', 'on', 'at', 'to', 'for', 'with', 'by', 'from', 'as', 'about',
                 'me', 'my', 'your', 'how', 'what', 'why', 'when', 'where',
                 // PT
                 'de','da','do','dos','das','para','que','com','em','na','no','os','as','um','uma',
                 'e','ou','é','se','ao','aos','quê','como','quando','onde','porque'];

        $words = preg_split('/[\s,;:.!?()"\'\/\-]+/u', mb_strtolower($query)) ?: [];
        $words = array_filter($words, fn($w) => mb_strlen($w) >= 3 && !in_array($w, $stop, true));
        return array_values(array_unique($words));
    }
}
