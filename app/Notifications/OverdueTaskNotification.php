<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueTaskNotification extends Notification
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tarea Vencida - '.$this->task->title)
            ->line('La tarea "'.$this->task->title.'" ha vencido el '.$this->task->due_date->format('d/m/Y'))
            ->action('Ver Tarea', url('/admin/tasks/'.$this->task->id.'/edit'))
            ->line('Por favor, actualiza el estado de la tarea.');
    }
}
