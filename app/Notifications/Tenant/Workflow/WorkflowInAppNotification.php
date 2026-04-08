<?php

namespace App\Notifications\Tenant\Workflow;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WorkflowInAppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public ?string $actionUrl = null,
        public string $icon = 'IconBell'
    ) {}

    public function via(object $notifiable): array
    {
        // For now, we use database notifications (standard in CRM for in-app)
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'icon' => $this->icon,
            'source' => 'workflow_automation',
        ];
    }
}
