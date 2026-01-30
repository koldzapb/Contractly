<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int         $id
 * @property int         $user_id
 * @property bool        $analysis_complete
 * @property bool        $deadline_reminder
 * @property int         $deadline_days_before
 * @property bool        $weekly_digest
 * @property bool        $contract_expiring
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property-read User   $user
 */
class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'analysis_complete',
        'deadline_reminder',
        'deadline_days_before',
        'weekly_digest',
        'contract_expiring',
    ];

    protected function casts(): array
    {
        return [
            'analysis_complete' => 'boolean',
            'deadline_reminder' => 'boolean',
            'deadline_days_before' => 'integer',
            'weekly_digest' => 'boolean',
            'contract_expiring' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this notification preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if analysis complete notifications are enabled.
     */
    public function wantsAnalysisComplete(): bool
    {
        return $this->analysis_complete;
    }

    /**
     * Check if deadline reminder notifications are enabled.
     */
    public function wantsDeadlineReminder(): bool
    {
        return $this->deadline_reminder;
    }

    /**
     * Check if weekly digest notifications are enabled.
     */
    public function wantsWeeklyDigest(): bool
    {
        return $this->weekly_digest;
    }

    /**
     * Check if contract expiring notifications are enabled.
     */
    public function wantsContractExpiring(): bool
    {
        return $this->contract_expiring;
    }
}
