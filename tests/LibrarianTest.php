<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Order;

class LibrarianTest extends TestCase
{
    /** @test */
    public function librarian_signin()
    {
        $user = User::where('role', 'librarian')->first();
        $parameters = [
            'email' => $user->email,
            'password' => '111111',
        ];
        $this->post('api/login', $parameters, []);
        $this->seeStatusCode(200);
    }

    /** @test */
    public function librarian_check_order()
    {
        $user = User::where('role', 'librarian')->first();
        $parameters = [
            'status' => 'borrow' // pending, borrow, finish
        ];
        $this->actingAs($user)->get('api/order', $parameters, []);
        $this->seeStatusCode(200);
    }

    /** @test */
    public function librarian_update_order_status()
    {
        $user = User::where('role', 'librarian')->first();
        $order = Order::inRandomOrder()->first();
        $parameters = [
            'order_id' => $order->id,
            'status' => 'borrow' // borrow, finish
        ];
        $this->actingAs($user)->post('api/order_status', $parameters, []);
        $this->seeStatusCode(200);
    }
    
}