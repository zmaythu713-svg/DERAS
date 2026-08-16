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
        'role_id',
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

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            // Keep role slug ↔ role_id synchronized (normalized FK + legacy column).
            if ($user->isDirty('role_id') && $user->role_id) {
                $slug = Role::where('id', $user->role_id)->value('slug');
                if ($slug) {
                    $user->role = $slug;
                }
            } elseif ($user->isDirty('role') && $user->role) {
                $roleId = Role::where('slug', $user->role)->value('id');
                if ($roleId) {
                    $user->role_id = $roleId;
                }
            }
        });
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
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

        if (!$role && $this->role) {
            $role = Role::where('slug', $this->role)->with('permissions')->first();
        }

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
