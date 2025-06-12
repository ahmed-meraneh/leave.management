<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'days_requested',
        'reason',
        'status',
        'admin_comment',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
        };
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($leaveRequest) {
            if ($leaveRequest->start_date && $leaveRequest->end_date) {
                $leaveRequest->days_requested = Carbon::parse($leaveRequest->start_date)
                        ->diffInWeekdays(Carbon::parse($leaveRequest->end_date)) + 1;
            }
        });
    }
}
