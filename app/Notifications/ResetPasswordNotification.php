<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordBase;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPasswordBase
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return $this->buildMailMessage($this->resetUrl($notifiable));
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        $spaUrl = env('SPA_URL', env('FRONTEND_URL', 'http://localhost:3000'));
        $url = $spaUrl . "/reset-password?token={$this->token}&email=" . urlencode($notifiable->getEmailForPasswordReset());

        \Illuminate\Support\Facades\Log::info("Password reset link generated for {$notifiable->email}: {$url}");

        return $url;
    }
}
