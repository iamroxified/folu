<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Term::firstOrCreate(['term_name' => 'First Term', 'term_number' => 1]);
        \App\Models\Term::firstOrCreate(['term_name' => 'Second Term', 'term_number' => 2]);
        \App\Models\Term::firstOrCreate(['term_name' => 'Third Term', 'term_number' => 3]);
    }
}
