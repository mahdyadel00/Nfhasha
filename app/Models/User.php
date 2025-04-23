<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Support\Str;
use TomatoPHP\FilamentLanguageSwitcher\Traits\InteractsWithLanguages;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithLanguages;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function scopeNearby(Builder $query, $latitude, $longitude, $distance = 50)
    {
        $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(latitude))
        * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))";
        return $query
            ->select('*')
            ->selectRaw("{$haversine} AS distance", [$latitude, $longitude, $latitude])
            ->having('distance', '<', $distance) // 50 KM
            ->orderBy('distance');
    }

    //Generate Invitation Code Start
    public static function generateInvitationCode()
    {
        return strtoupper(Str::random(1)) . rand(0, 9) . strtoupper(Str::random(1)) . rand(0, 9) . strtoupper(Str::random(1)) . rand(0, 9);
    }

    public function getProfilePictureAttribute($value)
    {
        if ($value && file_exists(storage_path('app/public/' . $value))) {
            return asset('storage/' . $value);
        } else {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->invitation_code = self::generateInvitationCode();
            $user->otp = rand(100000, 999999);
        });
    }
    //Generate Invitation Code End

    //Authorization for filament panel
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    //Mutator For password
    //    public function setPasswordAttribute($password)
    //    {
    //        $this->attributes['password'] = Hash::make($password);
    //    }

    //Invitations relationships
    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'user_id');
    }

    // InvitedBy relationship
    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    //Invitation relationship
    public function InvitedUser()
    {
        return $this->hasOne(Invitation::class, 'invited_user_id');
    }

    //vehicles relationship
    public function vehicles()
    {
        return $this->hasMany(UserVehicle::class);
    }

    //providers relationship
    public function provider()
    {
        return $this->hasOne(Provider::class);
    }

    public function scopeIsProvider($query)
    {
        return $query->whereHas('provider');
    }

    //wallet relationship
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function walletDeposits()
    {
        return $this->hasMany(WalletDeposit::class);
    }

    //Withdrawal relationship
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    //Orders
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id'); // تأكد أن العمود الصحيح هو `user_id`
    }

    //Puncture Services
    public function punctureServices()
    {
        return $this->hasMany(PunctureService::class);
    }

    public function notifications()
    {
        return $this->hasMany(ProviderNotification::class);
    }

    public function carReservations()
    {
        return $this->hasMany(CarReservations::class);
    }

    public function maintenance()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function ComprehensiveInspections()
    {
        return $this->hasMany(ComprehensiveInspections::class);
    }

    public function periodicInspections()
    {
        return $this->hasMany(PeriodicInspections::class);
    }

    // public function rates()
    // {
    //     return $this->hasMany(OrderRate::class);
    // }

    public function ratings()
    {
        return $this->hasManyThrough(OrderRate::class, User::class, 'id', 'provider_id', 'user_id', 'id');
    }

    public function orderOffers()
    {
        return $this->hasMany(OrderOffer::class, 'provider_id');
    }

    public function deposits()
    {
        return $this->hasMany(WalletDeposit::class);
    }
}
