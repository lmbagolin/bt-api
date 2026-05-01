<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/paises.csv');
        $file = fopen($path, 'r');

        // skip header
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 3) continue;

            $iso3 = trim($row[0]);
            $iso2 = trim($row[1]) ?: null;
            $name = trim($row[2]);

            if (! $iso3 || ! $name || $iso3 === 'ISO-3') {
                continue;
            }

            Country::updateOrCreate(
                ['iso3' => $iso3],
                ['iso2' => $iso2, 'name' => $name]
            );
        }

        fclose($file);
    }
}
