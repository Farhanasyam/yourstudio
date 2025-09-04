<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class UserRejectedNotification extends Notification
{
    use Queueable;

    public $user;
    public $rejectedBy;

    public function __construct($user, $rejectedBy)
    {
        $this->user = $user;
        $this->rejectedBy = $rejectedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Akun Ditolak',
            'message' => "Akun Anda telah ditolak oleh {$this->rejectedBy->name}",
            'type' => 'user_rejected',
            'action_url' => route('profile'),
            'icon' => 'fa fa-times-circle text-danger',
            'user_id' => $this->user->id,
            'rejected_by' => $this->rejectedBy->id,
        ];
    }
}
