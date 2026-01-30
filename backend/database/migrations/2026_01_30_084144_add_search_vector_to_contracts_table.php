<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds PostgreSQL full-text search capabilities to the contracts table.
     * Creates a tsvector column and GIN index for fast searching.
     * For SQLite (testing), these features are skipped.
     */
    public function up(): void
    {
        // Only add PostgreSQL-specific features when using PostgreSQL
        if ($this->isPostgres()) {
            // Add tsvector column for full-text search
            DB::statement('ALTER TABLE contracts ADD COLUMN search_vector tsvector');

            // Create GIN index for fast full-text search
            DB::statement('CREATE INDEX contracts_search_vector_idx ON contracts USING GIN (search_vector)');

            // Create function to update search vector
            DB::statement("
                CREATE OR REPLACE FUNCTION contracts_search_vector_update() RETURNS trigger AS \$\$
                BEGIN
                    NEW.search_vector :=
                        setweight(to_tsvector('english', COALESCE(NEW.title, '')), 'A') ||
                        setweight(to_tsvector('english', COALESCE(NEW.original_filename, '')), 'B');
                    RETURN NEW;
                END;
                \$\$ LANGUAGE plpgsql;
            ");

            // Create trigger to auto-update search vector on insert/update
            DB::statement('
                CREATE TRIGGER contracts_search_vector_trigger
                BEFORE INSERT OR UPDATE OF title, original_filename
                ON contracts
                FOR EACH ROW
                EXECUTE FUNCTION contracts_search_vector_update();
            ');

            // Update existing rows to populate search_vector
            DB::statement("
                UPDATE contracts SET search_vector =
                    setweight(to_tsvector('english', COALESCE(title, '')), 'A') ||
                    setweight(to_tsvector('english', COALESCE(original_filename, '')), 'B');
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->isPostgres()) {
            // Drop trigger first
            DB::statement('DROP TRIGGER IF EXISTS contracts_search_vector_trigger ON contracts');

            // Drop function
            DB::statement('DROP FUNCTION IF EXISTS contracts_search_vector_update()');

            // Drop index
            DB::statement('DROP INDEX IF EXISTS contracts_search_vector_idx');

            // Drop column
            DB::statement('ALTER TABLE contracts DROP COLUMN IF EXISTS search_vector');
        }
    }

    /**
     * Check if the current database connection is PostgreSQL.
     */
    private function isPostgres(): bool
    {
        return Schema::getConnection()->getDriverName() === 'pgsql';
    }
};
