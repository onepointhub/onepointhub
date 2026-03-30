<?php

namespace App\Notifications;

use App\Modules\Core\Enums\NotificationType;
use App\Modules\Core\Models\NotificationPreference;
use App\Modules\Core\Models\User;
use App\Modules\Core\Models\Workspace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberJoinedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly User $newMember,
        public readonly Workspace $workspace,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        /** @var User $notifiable */
        $channels = ['database'];

        if (NotificationPreference::emailEnabled($notifiable, NotificationType::MemberJoined)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => NotificationType::MemberJoined->value,
            'message' => "{$this->newMember->name} joined {$this->workspace->name}",
            'member_id' => $this->newMember->id,
            'member_name' => $this->newMember->name,
            'workspace_name' => $this->workspace->name,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->newMember->name} joined {$this->workspace->name}")
            ->line("{$this->newMember->name} has accepted their invitation and joined {$this->workspace->name}.")
            ->action('View Members', route('workspace.members.index'));
    }
}
