<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * book_chunks — knowledge base for the 4 SETQ.AI agents.
 *
 * One row per logical chunk (≈ 1 PDF page). Books are categorised by
 * "domain" so each agent only searches its own corpus:
 *   • back_office  → Operations + Insights (Finance, Procurement, Supply Chain)
 *   • front_office → Growth + Assistant (CRM, Sales)
 *
 * SQLite FTS5 virtual table mirrors the content column so keyword search
 * is fast even at 10K+ chunks. We rebuild the FTS index inside the same
 * migration to avoid an extra command in deploy.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('book_chunks', function (Blueprint $t) {
            $t->id();
            $t->string('domain', 32)->index();      // back_office | front_office
            $t->string('book_key', 100)->index();
            $t->string('book_title', 255);
            $t->unsignedInteger('page_no')->default(0);
            $t->text('content');
            $t->timestamp('created_at')->useCurrent();
        });

        // SQLite-only: build an FTS5 mirror so we can do MATCH queries.
        // Other drivers fall back to ILIKE in BookSearchService.
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('CREATE VIRTUAL TABLE book_chunks_fts USING fts5(
                content,
                domain UNINDEXED,
                book_key UNINDEXED,
                book_title UNINDEXED,
                page_no UNINDEXED,
                content="book_chunks",
                content_rowid="id"
            )');

            // Triggers that keep the FTS index in sync as rows arrive
            DB::unprepared('
                CREATE TRIGGER book_chunks_ai AFTER INSERT ON book_chunks BEGIN
                    INSERT INTO book_chunks_fts(rowid, content, domain, book_key, book_title, page_no)
                    VALUES (new.id, new.content, new.domain, new.book_key, new.book_title, new.page_no);
                END;
                CREATE TRIGGER book_chunks_ad AFTER DELETE ON book_chunks BEGIN
                    INSERT INTO book_chunks_fts(book_chunks_fts, rowid, content) VALUES (\'delete\', old.id, old.content);
                END;
                CREATE TRIGGER book_chunks_au AFTER UPDATE ON book_chunks BEGIN
                    INSERT INTO book_chunks_fts(book_chunks_fts, rowid, content) VALUES (\'delete\', old.id, old.content);
                    INSERT INTO book_chunks_fts(rowid, content, domain, book_key, book_title, page_no)
                    VALUES (new.id, new.content, new.domain, new.book_key, new.book_title, new.page_no);
                END;
            ');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('DROP TABLE IF EXISTS book_chunks_fts');
            DB::unprepared('
                DROP TRIGGER IF EXISTS book_chunks_ai;
                DROP TRIGGER IF EXISTS book_chunks_ad;
                DROP TRIGGER IF EXISTS book_chunks_au;
            ');
        }
        Schema::dropIfExists('book_chunks');
    }
};
