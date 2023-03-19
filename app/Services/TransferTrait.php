<?php
namespace App\Services;

trait TransferTrait
{

public function rules() :array {
    return [
        'amount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
        'description' => 'bail|string|max:100',
        'account_number' => 'required|regex:/^714[0-9]{7}$/',
    ];
}
public function messages () :array  {
    return [
        'amount.required' => 'Please provide an amount.',
        'amount.numeric' => 'Amount must be a number.',
    ];
}


}
