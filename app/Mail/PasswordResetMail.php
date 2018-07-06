<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($passwordReset)
    {
        $this->token = $passwordReset->token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $locale = config('app.locale');
        return $this->subject(trans('messages.passwordResetMailSubject'))->view('emails.' . $locale . '.passwordReset');
    }
}
