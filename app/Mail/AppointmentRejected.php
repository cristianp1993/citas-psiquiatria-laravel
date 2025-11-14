<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function build()
    {
        return $this->subject('Tu cita no pudo ser confirmada')
                    ->view('emails.appointment-rejected');
    }
}