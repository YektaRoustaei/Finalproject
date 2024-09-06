<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $seeker;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($seeker)
    {
        $this->seeker = $seeker;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.welcome')
            ->with([
                'name' => $this->seeker->first_name,
            ]);
    }
}
