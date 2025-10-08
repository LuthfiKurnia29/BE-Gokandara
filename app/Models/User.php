<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function roles() {
        return $this->hasMany(UserRole::class);
    }

    public function chatDikirim() {
        return $this->hasMany(Chatting::class, 'user_pengirim_id');
    }

    public function chatDiterima() {
        return $this->hasMany(Chatting::class, 'user_penerima_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function hasRole($role) {
        if (is_array($role)) {
            return $this->roles()->whereHas('role', function ($q) use ($role) {
                $q->whereIn('name', $role);
            })->exists();
        }
        return $this->roles()->whereHas('role', function ($q) use ($role) {
            $q->where('name', $role);
        })->exists();
    }

    /**
     * Get subordinate users (for Supervisor role)
     */
    public function getSubordinateIds() {
        if ($this->hasRole('Supervisor')) {
            return User::where('parent_id', $this->id)->pluck('id')->toArray();
        }
        return [];
    }

    /**
     * Get konsumen IDs assigned by this telemarketing user
     */
    public function getAssignedKonsumenIds() {
        if ($this->hasRole('Telemarketing')) {
            return Konsumen::where('added_by', $this->id)->pluck('id')->toArray();
        }
        return [];
    }

}
