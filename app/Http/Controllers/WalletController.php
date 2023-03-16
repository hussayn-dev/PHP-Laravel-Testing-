<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Transactions;
use App\Models\User;
use App\Models\Wallet;
use App\Services\JsonResponseService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{

 public $jsonResponseService;
 public function __construct(JsonResponseService $jsonResponseService)
    {
        $this->jsonResponseService = $jsonResponseService;
    }

 public function pay(Request $request, string $email) :JsonResponse
  {
    try {
        $validation = Validator::make($request->all(),[
            'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'description' => 'bail|required|string|max:100',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 400);
        }

        $user = User::where('email', $email)->first();

        if(!$user) {
            throw new Exception('A user with this account does not exist');
        }

        $purchase = null;
        DB::transaction(function () use ($user, $request, &$purchase) {
            // Create a new purchase
            $wallet = $user->wallet;

            if(!$wallet) {
                throw new Exception('Something went wrong, contact support team');
            }

            if($wallet->current_value < $request->price) {
                throw new Exception('Insufficient funds in wallet');
            }

            $purchase = Purchase::create([
              'user_id' => $user->id,
              'price' => $request->price,
              'description' => $request->description
            ]);

            $purchase->save();

            // Update the user's wallet balance
             $wallet->current_value -= $request->price;
             $wallet->update();
        });

        if(!$purchase) {
            throw new Exception('Something went wrong, try later');
        }

        return $this->jsonResponseService->success("Purchase successfull",[$purchase], 200);

    } catch (\Exception $e) {

        return $this->jsonResponseService->error($e->getMessage(),[], 400);
    }
  }

 public function transfer(Request $request, $email)
  {
try {

        $validation = Validator::make($request->all(),[
            'amount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'description' => 'bail|string|max:100',
            'account_number' => 'required|regex:/^714[0-9]{7}$/',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 400);
        }

        $user = User::where('email', $email)->first();

        if(!$user) {
            throw new Exception('Your account does not exist');
        }

        $recepient = Wallet::where('account_number', $request->account_number)->first();

        if(!$recepient) {
            throw new Exception('A user with this account number does not exist');
        }

        $transaction = null;
        DB::transaction(function () use ($user, $recepient, $request, &$transaction)
        {
            $wallet = $user->wallet;

            if(!$wallet) {
                throw new Exception('Something went wrong, contact support team');
            }

            if($wallet->current_value < $request->amount) {
                throw new Exception('Insufficient funds in wallet');
            }

            $transaction = Transactions::create([
                'recepient_id' => $recepient->user->id,
                'amount' => $request->amount,
                'sender_id' => $user->id,
                'description' => $request->description
              ]);


            $transaction->save();

            // Update the sender's wallet balance
             $wallet->current_value -= $request->amount;
             $wallet->update();

             // Update the recepient's wallet balance
            $recepient->current_value += $request->amount;
            $recepient->update();

        });

        if(!$transaction) {
            throw new Exception('Something went wrong, try later');
        }

        $sender =    [
            'name' => $transaction->sender->name,
            "account_number" => $transaction->sender->wallet->account_number
        ];

        $recepient =   [
            'name' => $transaction->recepient->name,
            'account_number' => $transaction->recepient->wallet->account_number
        ];

        return $this->jsonResponseService->success("Transfer successfull",
      [
          'transaction' =>  $transaction,
            'sender' => $sender,
           'recepient' => $recepient

        ], 200);
     } catch(\Exception $e) {
        return $this->jsonResponseService->error($e->getMessage(),[], 400);
     }
 }
}
