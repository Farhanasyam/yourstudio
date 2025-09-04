<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class UserApprovedNotification extends Notification
{
    use Queueable;

    public $user;
    public $approvedBy;

    public function __construct($user, $approvedBy)
    {
        $this->user = $user;
        $this->approvedBy = $approvedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Akun Disetujui',
            'message' => "Akun Anda telah disetujui oleh {$this->approvedBy->name}",
            'type' => 'user_approved',
            'action_url' => route('profile'),
            'icon' => 'fa fa-check-circle text-success',
            'user_id' => $this->user->id,
            'approved_by' => $this->approvedBy->id,
        ];
    }
}
