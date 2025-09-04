<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Stok Menipis',
            'message' => "Stok {$this->item->name} tersisa {$this->item->stock_quantity} {$this->item->unit}",
            'type' => 'low_stock',
            'action_url' => route('items.show', $this->item->id),
            'icon' => 'fa fa-exclamation-triangle',
            'color' => 'warning',
            'item_id' => $this->item->id,
            'created_at' => now()->toISOString(),
        ];
    }
}
