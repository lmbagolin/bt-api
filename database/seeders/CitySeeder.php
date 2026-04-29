<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/cidades.csv');
        $file = fopen($path, 'r');

        // skip header
        fgetcsv($file, 0, ';');

        $now = now();
        $chunk = [];

        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $id = trim($row[0]);
            $name = trim($row[1]);
            $stateCode = trim($row[2]);
            $isCapital = (int) trim($row[3]);
            $info = trim($row[4]);
            $metadata = ($info === 'NULL' || $info === '') ? null : $info;

            if (! $id || ! $name) {
                continue;
            }

            $chunk[] = [
                'id' => $id,
                'name' => $name,
                'state_code' => $stateCode,
                'is_capital' => $isCapital,
                'metadata' => $metadata,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($chunk) === 500) {
                DB::table('cities')->upsert($chunk, ['id'], ['name', 'state_code', 'is_capital', 'metadata']);
                $chunk = [];
            }
        }

        if ($chunk) {
            DB::table('cities')->upsert($chunk, ['id'], ['name', 'state_code', 'is_capital', 'metadata']);
        }

        fclose($file);
    }
}
