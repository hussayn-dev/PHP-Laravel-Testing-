<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transactions extends Model
{
    use HasFactory;

    protected $fillable = [
 'amount', 'recepient_id', 'sender_id', 'description', 'transaction_id'
    ];
    protected $hidden = ['recepient_id', 'sender_id', 'sender', 'recepient'];
    public static function boot()
    {
        parent::boot();

        self::saving(function ($transaction) {
            // perform some logic before saving the model
            $transaction->transaction_id = self::generateUUID();
        });
    }

    public function sender() : BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

     public function recepient () : BelongsTo
     {
        return $this->belongsTo(User::class, 'recepient_id');
     }
    protected static function generateUUID(): string
    {
        $uuid = Str::uuid();
        if (self::query()->where('transaction_id', $uuid)->first()) {
            self::generateUUID();
        }
        return $uuid;
    }
}
