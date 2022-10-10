<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Faker\Factory as Faker;
use App\Models\User;

class CustomerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

    /** @test */
    public function customer_signup()
    {
        $faker = Faker::create();

        $parameters = [
            'name' => $faker->name(),
            'email' => $faker->email(),
            'password' => 111111,
        ];

        $this->post('api/register', $parameters, []);
        $this->seeStatusCode(200);
    }

    /** @test */
    public function customer_signin()
    {
        $user = User::latest()->first();
        $parameters = [
            'email' => $user->email,
            'password' => '111111',
        ];
        $this->post('api/login', $parameters, []);
        $this->seeStatusCode(200);
    }

    /** @test */
    public function customer_browse_book_list()
    {
        $user = User::orderBy('id', 'desc')->where('role', 'customer')->first();
        $this->actingAs($user)->get('api/books');
        $this->seeStatusCode(200);
    }

    /** @test */
    public function customer_create_pickup_schedule()
    {
        $user = User::orderBy('id', 'desc')->where('role', 'customer')->first();
        $parameters = [
            'cover_id' => [3956527, 225568],
            'pick_up_date' => '2022-10-15 08:30',
        ];
        $this->actingAs($user)->post('api/books', $parameters, []);
        $this->seeStatusCode(200);
    }
}
