<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewUserRegistrationNotification extends Notification
{
    use Queueable;

    public $newUser;

    public function __construct($newUser)
    {
        $this->newUser = $newUser;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Pendaftaran User Baru',
            'message' => "User baru {$this->newUser->name} ({$this->newUser->role}) telah mendaftar",
            'type' => 'new_user_registration',
            'action_url' => route('user-management.show', $this->newUser->id),
            'icon' => 'fa fa-user-plus',
            'color' => 'info',
            'user_id' => $this->newUser->id,
            'created_at' => now()->toISOString(),
        ];
    }
}
