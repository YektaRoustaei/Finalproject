<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateCategories extends Command
{
    protected $signature = 'categories:cleanup-duplicates';
    protected $description = 'Remove duplicate categories from the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting duplicate cleanup...');

        // Using SQLite-compatible syntax to remove duplicates
        DB::statement("
            DELETE FROM categories
            WHERE id NOT IN (
                SELECT MIN(id)
                FROM categories
                GROUP BY title
            )
        ");

        $this->info('Duplicate cleanup completed successfully.');
        return 0;
    }
}
