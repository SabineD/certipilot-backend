<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Carbon;

class Machine extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function latestInspection(): HasOne
    {
        return $this->hasOne(Inspection::class)
            ->latestOfMany('inspected_at')
            ->select('inspections.*');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function inspectionStatus(): string
    {
        $inspection = $this->latestInspection;
        if (!$inspection || !$inspection->valid_until) {
            return 'ok';
        }

        $now = now();
        $validUntil = $inspection->valid_until instanceof Carbon
            ? $inspection->valid_until
            : Carbon::parse($inspection->valid_until);

        if ($validUntil->lt($now)) {
            return 'expired';
        }

        if ($validUntil->lte($now->copy()->addDays(30))) {
            return 'warning';
        }

        return 'ok';
    }

    public function lastInspectionDate(): ?string
    {
        return $this->latestInspection?->inspected_at?->toDateString();
    }

    public function validUntilDate(): ?string
    {
        return $this->latestInspection?->valid_until?->toDateString();
    }
}
