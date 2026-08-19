<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(public string $fileName) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url("/storage/exports/{$this->fileName}");

        return (new MailMessage)
            ->subject('Reporte de Regalías Listo')
            ->line('Tu reporte de regalías está listo para descargar.')
            ->action('Descargar Reporte', $url);
    }
}
