<?php

namespace Database\Seeders;

use App\Models\Playlist;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Factories\PlaylistFactory;
use Database\Factories\TrackFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'tests@example.com',
        ]);

//        $erik = User::factory()->create([
//            'name' => 'Erik L. Arneson',
//            'email' => 'erik@example.com',
//        ]);
//
//        $joy = User::factory()->create([
//            'name' => 'Joy Ajayi',
//            'email' => 'joy@example.com',
//        ]);
//
//        $testUser->follow($erik);
//        $erik->follow($testUser);
//        $joy->follow($testUser);

//        $this->call(TrackSeeder::class);


    }
}
