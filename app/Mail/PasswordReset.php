<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;

    /**
     * PasswordReset constructor.
     * @param User $user
     * @param $token
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(trans('messages.password_reset_subject'))
            ->view('emails.password-reset')
            ->with(['name' => $this->user->name, 'token' => $this->user->createPasswordRecoveryToken()]);
    }
}
