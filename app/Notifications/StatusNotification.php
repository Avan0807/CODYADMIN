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
            'title' => $this->details['title'],  // Tiêu đề thông báo
            'message' => $this->details['message'] ?? 'No message provided',  // Đảm bảo message có sẵn
            'actionURL' => $this->details['actionURL'],  // URL để người dùng có thể click
            'type' => $this->details['fas'] ?? 'info',  // Loại thông báo (dùng icon font awesome nếu có)
            'notifiable_type' => \App\Models\User::class,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->details['title'],
            'message' => $this->details['message'] ?? 'No message provided',
            'actionURL' => $this->details['actionURL'],
            'fas' => $this->details['fas'] ?? 'info',
            'time' => now()->format('F d, Y h:i A'),
            'notifiable_type' => \App\Models\User::class,
        ]);
    }
}
