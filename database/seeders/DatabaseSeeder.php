<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
            'role' => 'Admin'
        ]);
        // \App\Models\User::factory(10)->create();



        \App\Models\CMS::factory()->create([
            'detail' => fake()->paragraph(50),
            'type' => 'tc'
        ]);
        \App\Models\CMS::factory()->create([
            'detail' => fake()->paragraph(50),
            'type' => 'pp'
        ]);
        \App\Models\CMS::factory()->create([
            'detail' => fake()->paragraph(50),
            'type' => 'au'
        ]);

        \App\Models\ContentWeb::factory()->create([
            'type' => 'tc',
            'url' => 'https://admin.mybackyardusa.com/public/termsconditions'

        ]);
        \App\Models\ContentWeb::factory()->create([
            'type' => 'pp',
            'url' => 'https://admin.mybackyardusa.com/public/privacypolicy'

        ]);
        \App\Models\ContentWeb::factory()->create([
            'type' => 'au',
            'url' => 'https://admin.mybackyardusa.com/public/aboutus'

        ]);


    }
}
