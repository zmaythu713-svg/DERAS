<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    public function isSuper(): bool
    {
        return $this->role === 'super';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasPermission(string $slug): bool
    {
        $role = $this->relationLoaded('roleModel')
            ? $this->roleModel
            : $this->roleModel()->with('permissions')->first();

        if (!$role) {
            // Fallback to legacy enum behavior if roles table empty
            return match ($slug) {
                'records.view', 'records.manage' => true,
                'records.delete', 'users.manage', 'academic_years.rollover' => $this->isSuper(),
                default => false,
            };
        }

        if (!$role->relationLoaded('permissions')) {
            $role->load('permissions');
        }

        return $role->hasPermission($slug);
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermission('users.manage');
    }

    public function canDeleteRecords(): bool
    {
        return $this->hasPermission('records.delete');
    }

    public function canRolloverYear(): bool
    {
        return $this->hasPermission('academic_years.rollover');
    }
}
