<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    use HasFactory;

 protected $fillable = [
'account_number', 'current_value' , 'owner_id', 'wallet_id'
 ];
 
 public function depositAmount(int $value)
 {
    $this->current_value += $value;
    $this->save();
 }

 public function user() :BelongsTo
 {
    return $this->belongsTo(User::class, 'owner_id');
 }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($wallet) {
            // perform some logic before saving the model
            $wallet->account_number = self::generateAccountNumber();
        });
    }

    protected static function generateAccountNumber ()
    {
        $prefix = '714';
        $unique_digits = str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
        $account_number = $prefix . $unique_digits;
        if(self::query()->where('account_number', $account_number)->exists()) {
            self::generateAccountNumber();
        }
        return $account_number;
    }

}
