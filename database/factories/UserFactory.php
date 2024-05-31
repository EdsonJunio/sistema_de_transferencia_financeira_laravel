<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'id' => $this->faker->randomNumber(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'document_id' => rand(11111,99999),
            'password' => Hash::make('password123'),
        ];
    }

    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
