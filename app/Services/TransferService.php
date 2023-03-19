<?php

namespace App\Services;

use App\Models\Transactions;
use App\Models\User;
use App\Models\Wallet;
use App\Services\TransferTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

Class TransferService
{
 use TransferTrait;

 public function checkValidationRules ($request) {

    $validation = Validator::make($request->all(), $this->rules(), $this->messages());
    if ($validation->fails()) {
        return response()->json($validation->errors(), 400);
    }
 }

 public function checkUserWallet ($email) {
    $user = User::where('email', $email)->first();

    if(!$user) {
        throw new Exception('A user with this account does not exist');
    }
    $wallet = $user->wallet;
    if(!$wallet) {
        throw new Exception('Something went wrong, contact support team');
    }
    return $user;
 }
 public function checkRecepientValidity($request) {
    $recepient = Wallet::where('account_number', $request->account_number)->first();

    if(!$recepient) {
        throw new Exception('A user with this account number does not exist');
    }
    return $recepient->user;
 }

 public function checkInsufficientFunds($current_value, $amount) {
    if($current_value < $amount) {
        throw new Exception('Insufficient funds in wallet');
    }
 }
public function performTransaction($request, $user, $recepient) {
    $result = DB::transaction(function () use($request, $user, $recepient) {
        $transaction = Transactions::create([
            'recepient_id' => $recepient->id,
            'amount' => $request->amount,
            'sender_id' => $user->id,
            'description' => $request->description
          ]);


        $transaction->save();

        // Update the sender's wallet balance
         $user->wallet->current_value -= $request->amount;
         $user->wallet->update();

         // Update the recepient's wallet balance
        $recepient->wallet->current_value += $request->amount;
        $recepient->wallet->update();
        return $transaction;
    });
   return $result;
}
 }
