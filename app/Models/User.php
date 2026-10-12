<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status_akun',
        'phone',
        'profile_photo_path',
        'last_login_at',
    ];

    public const ROLE_ADMIN = 'admin';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_ARTISAN = 'artisan';
    public const ROLE_CURATOR = 'curator';
    public const ROLE_CUSTOMER_SERVICE = 'customer_service';

    public static array $roleLabel = [
        'admin'            => 'Admin',
        'customer'         => 'Customer',
        'artisan'          => 'Perajin',
        'curator'          => 'Kurator',
        'customer_service' => 'Customer Service',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isArtisan(): bool
    {
        return $this->role === self::ROLE_ARTISAN;
    }

    public function isCurator(): bool
    {
        return $this->role === self::ROLE_CURATOR;
    }

    public function isCustomerService(): bool
    {
        return $this->role === self::ROLE_CUSTOMER_SERVICE;
    }

    public function isAktif(): bool
    {
        return $this->status_akun === 'aktif';
    }

    public function artisanProfile(): HasOne
    {
        return $this->hasOne(ArtisanProfile::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'artisan_id');
    }

    public function customerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'customer_id');
    }

    public function assignedSupportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to');
    }

    public function customerChats(): HasMany
    {
        return $this->hasMany(ArtisanCustomerChat::class, 'customer_id');
    }

    public function artisanChats(): HasMany
    {
        return $this->hasMany(ArtisanCustomerChat::class, 'artisan_id');
    }

    public function artisanCustomerChatMessages(): HasMany
    {
        return $this->hasMany(ArtisanCustomerChatMessage::class, 'sender_id');
    }
}
