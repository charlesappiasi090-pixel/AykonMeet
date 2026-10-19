<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log a security-related event.
     */
    public static function log(
        string $action,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = []
    ): void {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'auditable_type' => $auditable?->getMorphClass(),
                'auditable_id' => $auditable?->getKey(),
                'old_values' => $oldValues ?: null,
                'new_values' => $newValues ?: null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'metadata' => $metadata ?: null,
            ]);
        } catch (\Throwable $e) {
            // Silently fail to avoid disrupting the main operation
            Log::warning('Failed to write audit log', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log user authentication events.
     */
    public static function login(string $email, bool $success = true): void
    {
        self::log(
            $success ? 'user.login' : 'user.login_failed',
            null,
            [],
            ['email' => $email],
            ['success' => $success]
        );
    }

    public static function logout(): void
    {
        self::log('user.logout');
    }

    public static function passwordChanged(): void
    {
        self::log('user.password_changed');
    }

    public static function twoFactorEnabled(): void
    {
        self::log('user.two_factor_enabled');
    }

    public static function twoFactorDisabled(): void
    {
        self::log('user.two_factor_disabled');
    }

    public static function loginFailed(string $email): void
    {
        self::log('user.login_failed', null, [], ['email' => '***'], ['success' => false]);
    }

    // Meeting events
    public static function meetingCreated($meeting): void
    {
        self::log('meeting.created', $meeting, [], $meeting->getAttributes());
    }

    public static function meetingUpdated($meeting, array $oldValues, array $newValues): void
    {
        self::log('meeting.updated', $meeting, $oldValues, $newValues);
    }

    public static function meetingDeleted($meeting): void
    {
        self::log('meeting.deleted', $meeting);
    }

    public static function meetingStarted($meeting): void
    {
        self::log('meeting.started', $meeting);
    }

    public static function meetingEnded($meeting): void
    {
        self::log('meeting.ended', $meeting);
    }

    public static function meetingCancelled($meeting): void
    {
        self::log('meeting.cancelled', $meeting);
    }

    public static function meetingLocked($meeting, $user): void
    {
        self::log('meeting.locked', $meeting, [], ['locked_by' => $user->id]);
    }

    public static function meetingUnlocked($meeting, $user): void
    {
        self::log('meeting.unlocked', $meeting, [], ['unlocked_by' => $user->id]);
    }

    public static function meetingRescheduled($meeting, array $oldValues, array $newValues): void
    {
        self::log('meeting.rescheduled', $meeting, $oldValues, $newValues);
    }

    // Participant events
    public static function participantJoined($participant): void
    {
        self::log('participant.joined', $participant);
    }

    public static function participantLeft($participant): void
    {
        self::log('participant.left', $participant);
    }

    public static function participantRemoved($participant, $actor): void
    {
        self::log('participant.removed', $participant, [], ['removed_by' => $actor->id]);
    }

    public static function participantMuted($participant, $actor): void
    {
        self::log('participant.muted', $participant, [], ['muted_by' => $actor->id]);
    }

    public static function participantUnmuted($participant, $actor): void
    {
        self::log('participant.unmuted', $participant, [], ['unmuted_by' => $actor->id]);
    }

    public static function participantPromoted($participant, $actor): void
    {
        self::log('participant.promoted', $participant, [], ['promoted_by' => $actor->id]);
    }

    public static function participantDemoted($participant, $actor): void
    {
        self::log('participant.demoted', $participant, [], ['demoted_by' => $actor->id]);
    }

    // Document events
    public static function documentUploaded($document): void
    {
        self::log('document.uploaded', $document);
    }

    public static function documentDownloaded($document, $participant): void
    {
        self::log('document.downloaded', $document, [], ['downloaded_by' => $participant->id]);
    }

    public static function documentPreviewed($document, $participant): void
    {
        self::log('document.previewed', $document, [], ['previewed_by' => $participant->id]);
    }

    public static function documentShared($document, array $oldValues, array $newValues): void
    {
        self::log('document.shared', $document, $oldValues, $newValues);
    }

    public static function documentRevoked($document, $actor): void
    {
        self::log('document.revoked', $document, [], ['revoked_by' => $actor->id]);
    }

    // Recording events
    public static function recordingStarted($recording): void
    {
        self::log('recording.started', $recording);
    }

    public static function recordingStopped($recording): void
    {
        self::log('recording.stopped', $recording);
    }

    public static function recordingDeleted($recording): void
    {
        self::log('recording.deleted', $recording);
    }

    // Settings
    public static function settingsUpdated(array $oldValues, array $newValues): void
    {
        self::log('settings.updated', null, $oldValues, $newValues);
    }

    public static function recordingPermissionsChanged($meeting, array $oldValues, array $newValues): void
    {
        self::log('recording_permissions.changed', $meeting, $oldValues, $newValues);
    }
}
