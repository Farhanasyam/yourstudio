<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * An offline sale was synced but something no longer added up
 * (stock went negative, the price changed meanwhile, short payment).
 */
class OfflineSaleReviewNotification extends Notification
{
    use Queueable;

    public $transaction;
    public $issues;

    public function __construct($transaction, array $issues)
    {
        $this->transaction = $transaction;
        $this->issues = $issues;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Transaksi Offline Perlu Dicek',
            'message' => "{$this->transaction->transaction_code}: " . implode('; ', $this->issues),
            'type' => 'offline_sale_review',
            'action_url' => route('transaction-history.show', $this->transaction->id),
            'icon' => 'fa fa-wifi',
            'color' => 'danger',
            'transaction_id' => $this->transaction->id,
            'created_at' => now()->toISOString(),
        ];
    }
}
