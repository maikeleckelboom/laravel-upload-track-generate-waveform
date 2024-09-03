<?php

namespace Database\Seeders;

use App\Models\Track;
use App\Models\User;
use App\Services\AudioProcessor;
use Database\Factories\TrackFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class TrackSeeder extends Seeder
{
    /**
     * @return void
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function run(): void
    {


        $user = $this->getTestUser();


        $files = $this->getTestCasesData(
            storage_path('test_cases/input_data'),
            $user
        );


    }

    private function getTestUser(): User
    {
        return User::find(1);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function createTrack(User $user, string $filename): Track
    {
        $track = TrackFactory::new(['user_id' => $user->id])->create([
            'title' => pathinfo($filename, PATHINFO_FILENAME),
        ]);

        $track->addMedia($filename)
            ->preservingOriginal()
            ->withResponsiveImages()
            ->toMediaCollection('default', 'local-test-cases');

        $user->tracks()->save($track);

        return $track;
    }


    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function getTestCasesData(string $inputDataFolder, User $user): \Illuminate\Support\Collection
    {
        $tracks = collect();
        foreach (scandir($inputDataFolder) as $file) {
            if (is_file($inputDataFolder . '/' . $file)) {
                $tracks->push($this->createTrack($user, "{$inputDataFolder}/{$file}"));
            }
        }
        return $tracks;
    }
}
