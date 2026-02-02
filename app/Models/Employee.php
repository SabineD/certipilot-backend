<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Employee extends Model
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

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function latestCertificate(): HasOne
    {
        return $this->hasOne(Certificate::class)
            ->latestOfMany('valid_until')
            ->select('certificates.*');
    }

    public function certificateStatus(): ?string
    {
        $jobTitle = $this->job_title ? trim($this->job_title) : '';
        if (strtolower($jobTitle) === 'preventieadviseur') {
            return null;
        }

        $certificate = $this->latestCertificate;
        if (!$certificate || !$certificate->valid_until) {
            return null;
        }

        $now = now();
        $validUntil = $certificate->valid_until instanceof Carbon
            ? $certificate->valid_until
            : Carbon::parse($certificate->valid_until);

        if ($validUntil->lt($now)) {
            return 'expired';
        }

        if ($validUntil->lte($now->copy()->addDays(30))) {
            return 'warning';
        }

        return 'ok';
    }
}
