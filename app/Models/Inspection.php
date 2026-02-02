<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Inspection extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $casts = [
        'inspected_at' => 'date',
        'valid_until' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function status(): ?string
    {
        if (! $this->valid_until) {
            return null;
        }

        $now = now();
        $validUntil = $this->valid_until instanceof Carbon
            ? $this->valid_until
            : Carbon::parse($this->valid_until);

        if ($validUntil->lt($now)) {
            return 'expired';
        }

        if ($validUntil->lte($now->copy()->addDays(30))) {
            return 'warning';
        }

        return 'ok';
    }
}
