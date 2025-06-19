<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $email;
    public $firstName;
    public $lastName;
    public $defaultPassword;
    // public $languageId;
      /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $firstName, $lastName, $defaultPassword)
    {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->defaultPassword = $defaultPassword;
        // $this->languageId = $languageId;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.welcome-email')
                    ->subject('User Enrolment - National Cancer Health Fund')
                    ->with([
                        'email' => $this->email,
                        'firstName' => $this->firstName,
                        'lastName' => $this->lastName,
                        
                        'defaultPassword' => $this->defaultPassword,
                        // 'languageId' => $this->languageId,
                        'action_url' => "https://nchf.resilience.ng/login",
                        'login_url' => "https://nchf.resilience.ng/login",
                        
                        'support_email' => "info@resilience.ng",
                    ]);
    }
}
