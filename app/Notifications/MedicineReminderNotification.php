<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class MedicineReminderNotification extends Notification
{
    use Queueable;

    private $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Xác định kênh gửi thông báo (database và broadcast)
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];  // Lưu vào DB và gửi real-time (broadcast)
    }

    /**
     * Lưu thông báo vào cơ sở dữ liệu
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Nhắc lịch uống thuốc',
            'message' => $this->details['message'],  // Thông báo từ bảng medicine_logs
            'product_name' => $this->details['product_name'],  // Tên sản phẩm
            'type' => 'medicine_reminder',  // Loại thông báo
            'notifiable_type' => \App\Models\User::class,
        ];
    }

    /**
     * Gửi thông báo qua Broadcast
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Nhắc lịch uống thuốc',
            'message' => $this->details['message'],
            'product_name' => $this->details['product_name'],  // Tên sản phẩm
            'time' => now()->format('F d, Y h:i A'),
            'notifiable_type' => \App\Models\User::class,
        ]);
    }
}
