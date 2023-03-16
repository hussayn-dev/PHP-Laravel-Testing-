<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'price', 'description' , 'user_id', 'purchase_id'
    ];

    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public static function boot()
    {
        parent::boot();

        self::saving(function ($purchase) {
            // perform some logic before saving the model
            $purchase->purchase_id = self::generateUUID();
        });
    }

    protected static function generateUUID(): string
    {
        $uuid = Str::uuid();
        if (self::query()->where('purchase_id', $uuid)->first()) {
            self::generateUUID();
        }
        return $uuid;
    }
}
