<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ImportCategories extends Command
{
    protected $signature = 'import:categories';
    protected $description = 'Import categories from a JSON file into the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $filePath = storage_path('app/occupations.json');

        if (!file_exists($filePath)) {
            $this->error('The file does not exist.');
            return 1;
        }

        $json = file_get_contents($filePath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Error decoding JSON data.');
            Log::error('JSON decoding error: ' . json_last_error_msg());
            return 1;
        }

        if (!isset($data['occupations']) || !is_array($data['occupations'])) {
            $this->error('Invalid JSON structure: missing or incorrect "occupations" key.');
            return 1;
        }

        $occupations = $data['occupations'];
        $batchSize = 100; // Adjust as needed
        $chunks = array_chunk($occupations, $batchSize);

        // Collect all titles to check for existing records
        $existingTitles = Category::pluck('title')->toArray();
        $existingTitlesSet = array_flip($existingTitles); // Faster lookup

        $importedCount = 0;
        $skippedCount = 0;

        foreach ($chunks as $chunk) {
            $categories = [];

            foreach ($chunk as $occupation) {
                // Check if the occupation already exists in the set
                if (!isset($existingTitlesSet[$occupation])) {
                    $categories[] = ['title' => $occupation];
                    $importedCount++;
                    $existingTitlesSet[$occupation] = true; // Add to the set
                } else {
                    $skippedCount++;
                }
            }

            // Insert new categories in chunks
            if (count($categories) > 0) {
                Category::insert($categories);
            }
        }

        $this->info("Categories imported successfully. Imported: $importedCount, Skipped: $skippedCount.");
        return 0;
    }
}
