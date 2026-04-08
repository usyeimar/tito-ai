<?php

namespace App\Notifications\Tenant\System;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SystemConfigurationRetestFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, string>  $failedChecks
     */
    public function __construct(
        public string $configurationKey,
        public string $message,
        public array $failedChecks,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'system_configuration_retest_failed',
            'title' => 'System Configuration Re-test Failed',
            'message' => $this->message,
            'configuration_key' => $this->configurationKey,
            'failed_checks' => $this->failedChecks,
            'action_url' => null,
            'icon' => 'IconAlertTriangle',
            'source' => 'system_configuration_monitoring',
            'automated' => true,
        ];
    }
}
