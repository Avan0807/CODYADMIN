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
        return ['database', 'broadcast']; // 🔹 Lưu vào DB & gửi real-time (nếu dùng Laravel Echo)
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->details['title'],
            'message' => $this->details['message'],
            'appointment_id' => $this->details['appointment_id'],
            'type' => $this->details['type']
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->details['title'],
            'message' => $this->details['message'],
            'appointment_id' => $this->details['appointment_id'],
            'type' => $this->details['type'],
            'time' => now()->format('F d, Y h:i A')
        ]);
    }
}
