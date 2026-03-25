<?php

namespace App\Notifications;

use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly WorkspaceInvitation $invitation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        /** @var Workspace $workspace */
        $workspace = $this->invitation->workspace;

        $url = route('invitations.accept', ['token' => $this->invitation->token]);

        return (new MailMessage)
            ->subject('You\'ve been invited to '.$workspace->name)
            ->line('You\'ve been invited to join '.$workspace->name.' as a '.$this->invitation->role)
            ->action('Accept Invitation', $url)
            ->line('This invitation expires in 48 hours.');
    }
}
