<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class NewTransactionNotification extends Notification
{
    use Queueable;

    public $transaction;

    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Transaksi Baru',
            'message' => "Transaksi baru dengan total Rp " . number_format($this->transaction->total_amount, 0, ',', '.'),
            'type' => 'new_transaction',
            'action_url' => route('transaction-history.show', $this->transaction->id),
            'icon' => 'fa fa-shopping-cart text-success',
            'transaction_id' => $this->transaction->id,
        ];
    }
}
