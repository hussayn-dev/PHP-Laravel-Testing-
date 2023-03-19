<?php

namespace Tests\Unit;

use App\Models\Transactions;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use App\Services\TransferService;
use Illuminate\Http\Request;

class WalletTest extends TestCase
{
    use RefreshDatabase ;


    protected $transferService;
    protected $sender;
    protected $recepient;
    protected $request;
    protected $initialAmount;
    protected $sentAmount;

    public function setUp(): void
    {
        parent::setUp();
        $this->recepient =  User::factory()->create();
         $this->sender = User::factory()->create();

        $this->initialAmount = 10000;
        $this->sentAmount = 5000;
        $this->transferService = new TransferService();

        $input = [
            "amount" => $this->sentAmount,
            "description" => "Snacks",
            'account_number' => $this->recepient->wallet->account_number,
        ];

        $request = new Request();
        $this->request = $request->merge($input);
    }


    public function test_user_input_validation () : void
    {
        $response =  $this->transferService->checkValidationRules($this->request);
        $this->assertNull($response);
    }

    public function test_user_and_wallet_check () :void {
         $sender = $this->transferService->checkUserWallet($this->sender->email);
         $this->assertInstanceOf(User::class, $sender);
    }
    public function test_recepient_validity():void {
        $this->actingAs($this->sender);
      $recepient = $this->transferService->checkRecepientValidity($this->request);

      $this->assertInstanceOf(User::class, $recepient);
    }

   public function test_wallet_insufficient_funds():void {
    $this->sender->wallet->depositAmount($this->initialAmount);
    $response =$this->transferService->checkInsufficientFunds($this->sender->wallet->current_value, $this->request->amount);

    $this->assertEquals($this->initialAmount, $this->sender->wallet->current_value);
    $this->assertNull($response);
}

public function test_user_perform_transaction () : void {

   $initial_wallet_value =  $this->sender->wallet->current_value;
   $result = $this->transferService->performTransaction($this->request, $this->sender, $this->recepient);

   $this->sender->refresh();
   $this->recepient->refresh();
   $this->assertInstanceOf(Transactions::class, $result);
   $this->assertLessThanOrEqual($initial_wallet_value - $this->sentAmount, $this->sender->wallet->current_value);
   $this->assertEquals($this->sentAmount, $this->recepient->wallet->current_value);

}

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
