<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyUpdatedEmail extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Confirm your updated email address - Workupcloud')
            ->view('emails.verify-email-update', [
                'url' => $url,
                'notifiable' => $notifiable,
                'expire' => config('auth.verification.expire', 60),
            ]);
    }
}
