<?php

namespace App\Mail;

use App\Models\Request as PrintRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $paymentUrl;

    public function __construct(PrintRequest $order, $paymentUrl)
    {
        $this->order = $order;
        $this->paymentUrl = $paymentUrl;
    }

    public function build()
    {
        return $this->subject('Betaalverzoek voor uw 3D Print: ' . $this->order->title)
            ->view('emails.payment_request');
    }
}
