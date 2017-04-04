<?php

namespace Centaur\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CentaurWelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $code, $subject = 'Your account has been created!')
    {
        $this->email = $email;
        $this->code = $code;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject($this->subject)
            ->view('centaur.email.welcome');
    }
}
