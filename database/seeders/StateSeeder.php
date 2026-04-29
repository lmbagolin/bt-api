<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/estados.csv');
        $file = fopen($path, 'r');

        // skip header
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $code = trim($row[0]);
            $name = trim($row[1]);

            if (! $code || ! $name) {
                continue;
            }

            State::updateOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
        }

        fclose($file);
    }
}
