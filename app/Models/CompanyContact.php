<?php

namespace App\Models;

use App\Support\MyanmarPhone;
use Illuminate\Database\Eloquent\Model;

class CompanyContact extends Model
{
    protected $fillable = [
        'company_name',
        'lot',
        'responsible_name',
        'phone',
        'is_active',
    ];

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = MyanmarPhone::normalize($value);
    }

    public function getFormattedPhoneAttribute(): string
    {
        return MyanmarPhone::format($this->attributes['phone'] ?? null);
    }
}
