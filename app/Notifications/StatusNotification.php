<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class StatusNotification extends Notification
{
    use Queueable;

    private $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast']; // Lưu vào DB và gửi real-time nếu dùng Laravel Echo
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->details['title'],
            'message' => $this->details['message'] ?? 'No message provided',
            'actionURL' => $this->details['actionURL'] ?? null, // ✅ tránh lỗi
            'type' => $this->details['fas'] ?? 'info',
            'notifiable_type' => \App\Models\User::class,
        ];
    }


    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->details['title'],
            'message' => $this->details['message'] ?? 'No message provided',
            'actionURL' => $this->details['actionURL'] ?? null, // ✅ tránh lỗi
            'fas' => $this->details['fas'] ?? 'info',
            'time' => now()->format('F d, Y h:i A'),
            'notifiable_type' => \App\Models\User::class,
        ]);
    }

}
