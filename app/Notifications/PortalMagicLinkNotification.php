<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PortalMagicLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly string $magicLinkUrl,
        public readonly string $clientName,
        public readonly string $workspaceName,
    ) {
        //
    }

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
        return (new MailMessage)
            ->subject("Your portal access for $this->clientName")
            ->greeting('Hello!')
            ->line("You have been invited to access the $this->clientName client portal on $this->workspaceName.")
            ->action('Access Portal', $this->magicLinkUrl)
            ->line('This link expires in 24 hours and can only be used once.')
            ->line('If you did not request this, you can safely ignore this email.');
    }
}
