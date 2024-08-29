<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Synonym;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImportSynonyms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'synonyms:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import synonyms from a JSON file into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        // Check if the file exists
        if (!File::exists($filePath)) {
            $this->error('File does not exist.');
            return 1; // Return a non-zero status code to indicate failure
        }

        // Read the file contents
        $json = File::get($filePath);
        $data = json_decode($json, true);

        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Error decoding JSON data.');
            Log::error('JSON decoding error: ' . json_last_error_msg());
            return 1;
        }

        // Check for the expected structure in the JSON data
        if (!isset($data['occupations']) || !is_array($data['occupations'])) {
            $this->error('Invalid JSON structure: missing or incorrect "occupations" key.');
            return 1;
        }

        // Process each occupation and its synonyms
        $importedCount = 0;
        $skippedCount = 0;

        foreach ($data['occupations'] as $title => $synonyms) {
            // Ensure synonyms are in an array format
            if (!is_array($synonyms)) {
                $this->error("Invalid synonyms format for title: $title");
                $skippedCount++;
                continue;
            }

            // Prepare the synonyms data for the database
            $synonymsData = [
                'synonym1' => $synonyms[0] ?? null,
                'synonym2' => $synonyms[1] ?? null,
                'synonym3' => $synonyms[2] ?? null,
                'synonym4' => $synonyms[3] ?? null,
                'synonym5' => $synonyms[4] ?? null,
            ];

            // Update or create the synonym record
            $existingRecord = Synonym::where('title', $title)->first();

            if ($existingRecord) {
                // Update existing record
                $existingRecord->update($synonymsData);
                $importedCount++;
            } else {
                // Create new record
                Synonym::create(array_merge(['title' => $title], $synonymsData));
                $importedCount++;
            }
        }

        $this->info("Synonyms imported successfully. Imported: $importedCount, Skipped: $skippedCount.");
        return 0; // Return zero to indicate success
    }
}
