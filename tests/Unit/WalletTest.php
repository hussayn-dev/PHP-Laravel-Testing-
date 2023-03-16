<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_makes_payment(): void
    {
        $user =  User::factory()->create();
        $user->wallet->depositAmount(1000);

        $response = $this->post('/api/user/pay/'.$user->email, [
            "price" => 50,
            "description" => "Snacks"
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            "success"=> true,
            "message" =>"Purchase successfull",
            "data"=>[

            ]
        ]);
    }
    public function test_user_makes_transfer(): void
    {
        $sender =  User::factory()->create();
        $recepient =  User::factory()->create();
        $amount = 500;

     $sender->wallet->depositAmount(1000);

        $response = $this->post('/api/user/transfer/'.$sender->email, [
            "amount" => $amount,
            "description" => "Snacks",
            'account_number' => $recepient->wallet->account_number
        ]);

      $user_sender = User::where('email', $sender->email)->first();

        $response->assertStatus(200);

        $this->assertEquals(1000 - $amount, $user_sender->wallet->current_value);
        // $this->assertEquals($amount, $recepient->wallet->current_value);
        $response->assertJson([
            "success"=> true,
            "message" =>"Transfer successfull",
            "data"=>[

            ]
        ]);
    }
}
