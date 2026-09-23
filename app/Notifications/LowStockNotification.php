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
            'title' => $this->item->stock_quantity <= 0 ? 'Stok Habis' : 'Stok Menipis',
            'message' => $this->item->stock_quantity <= 0
                ? "Stok {$this->item->name} habis"
                : "Stok {$this->item->name} tersisa {$this->item->stock_quantity} {$this->item->unit}",
            'type' => 'low_stock',
            'action_url' => route('items.show', $this->item->id),
            'icon' => 'fa fa-exclamation-triangle',
            'color' => $this->item->stock_quantity <= 0 ? 'danger' : 'warning',
            'item_id' => $this->item->id,
            'created_at' => now()->toISOString(),
        ];
    }
}
