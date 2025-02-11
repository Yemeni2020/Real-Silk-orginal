<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmitServicetedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;
    protected $orderDetails;
    protected $user;
    protected $name_product;

    public function __construct($order, $orderDetails,$user,$name_product)
    {
        $this->order = $order;
        $this->orderDetails = $orderDetails;
        $this->user = $user;
        $this->name_product = $name_product;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Submit Service Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email-templates.submit_serviceted', with: [
                'order' => $this->order,
                'orderDetails' => $this->orderDetails,
                'user' => $this->user,
                'name_product' => $this->name_product,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
