<?php

namespace Database\Factories;

use App\Models\Informasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Informasi>
 */
class InformasiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Informasi::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasImage = fake()->boolean(70); // 70% kemungkinan ada gambar untuk variasi data

        return [
            'title' => fake()->text(255), // Sesuai max:255
            'desc' => fake()->paragraphs(3, true), // Aman, berupa long text
            'date' => fake()->date(), // Sesuai format date
            'loc' => substr(fake()->address(), 0, 255), // Aman untuk max:255
            'organizer' => substr(fake()->company(), 0, 255), // Aman untuk max:255
            
            // Properti di bawah mensimulasikan hasil dari validasi upload file image (nullable)
            'nama_image' => $hasImage ? fake()->word() . '.jpg' : null,
            'path_image' => $hasImage ? 'informasi/' . fake()->uuid() . '.jpg' : null,
        ];
    }
}