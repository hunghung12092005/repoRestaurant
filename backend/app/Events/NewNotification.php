<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

/**
 * Event chung để phát sóng một thông báo mới.
 * Event này có thể được sử dụng cho bất kỳ loại thông báo nào (liên hệ, bình luận, v.v.).
 */
class NewNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The notification instance.
     *
     * @var \App\Models\Notification
     */
    public $notification;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Notification $notification
     * @return void
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Gửi đến kênh riêng của người nhận thông báo
        return [new PrivateChannel('App.Models.User.' . $this->notification->notifiable_id)];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        // Giữ tên sự kiện này nhất quán để frontend dễ dàng lắng nghe
        return 'new-notification';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        // Gửi đi dữ liệu mà component Vue có thể hiểu được
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'data' => $this->notification->data,
            'read_at' => null,
            'created_at' => $this->notification->created_at->toDateTimeString(),
        ];
    }
}