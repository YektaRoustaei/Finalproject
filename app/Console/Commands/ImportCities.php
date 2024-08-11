<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\City;
use Illuminate\Support\Facades\Storage;

class ImportCities extends Command
{
    protected $signature = 'import:cities';
    protected $description = 'Import cities from a JSON file into the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Path to your JSON file
        $filePath = storage_path('app/gb.json');

        // Check if the file exists
        if (!file_exists($filePath)) {
            $this->error('The file does not exist.');
            return 1;
        }

        // Read and decode the JSON file
        $json = file_get_contents($filePath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Error decoding JSON data.');
            return 1;
        }

        foreach ($data as $item) {
            City::create([
                'city_name' => $item['city'],
                'Latitude' => $item['lat'],
                'Longitude' => $item['lng'],
            ]);
        }

        $this->info('Cities imported successfully.');
        return 0;
    }
}
