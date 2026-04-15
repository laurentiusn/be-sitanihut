<?php

namespace Database\Factories;

use App\Models\Katalog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\KatalogPicture;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Katalog>
 */
class KatalogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Katalog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->text(100), // Sesuai max:100
            'description' => fake()->text(255), // Sesuai max:255 dan required
            'location' => fake()->address(), // Tidak ada max, default string
            'kth' => substr('KTH ' . fake()->company(), 0, 50), // Aman untuk max:50
            'cp1' => substr(fake()->phoneNumber(), 0, 50), // Aman untuk max:50
            'cp2' => fake()->optional()->numerify('08##########'), // Optional (nullable) dan aman max:50
            'price' => fake()->numberBetween(10000, 500000), // Sesuai numeric dan min:0
            'is_active' => fake()->boolean(90), // boolean
            'id_user' => User::factory()->penyuluh(), // Sesuai uuid & exists:users,id
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Katalog $katalog) {
            $jumlahGambar = rand(2, 3); // Random 2 atau 3 gambar

            for ($i = 0; $i < $jumlahGambar; $i++) {
                $fileName = fake()->word() . '_' . fake()->randomNumber(3) . '.jpg';

                KatalogPicture::create([
                    'id' => Str::uuid(), 
                    'id_catalog' => $katalog->id,
                    'nama_file' => $fileName,
                    'path' => 'uploads/catalogs/' . $fileName,
                ]);
            }
        });
    }
}