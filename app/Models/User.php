<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];



    public static function boot()
    {
        parent::boot();

        self::created(function ($user) {
            // perform some logic before saving the model
            self::createUserWallet($user);
        });
    }

    protected static function createUserWallet($user)
    {
        $user_wallet = Wallet::create([
        'owner_id' => $user->id,
        ]);
        $user_wallet->save();
    }

    public function wallet () : HasOne
    {
        return $this->hasOne(Wallet::class, 'owner_id');
    }
    public function sentTransactions () : HasMany
    {
        return $this->hasMany(Transactions::class, 'sender_id');
    }
    public function receivedTransactions () : HasMany
    {
        return $this->hasMany(Transactions::class, 'recepient_id');
    }
    public function purchases () :HasMany
    {
        return $this->hasMany(Purchase::class, 'user_id');
    }


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
