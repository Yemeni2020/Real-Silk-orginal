<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public $vendor;
    public $pdfPath;
    public $contract;

    public function __construct($vendor, $pdfPath,$contract)
    {
        $this->vendor = $vendor;
        $this->pdfPath = $pdfPath;
        $this->contract = $contract;
    }

    public function build()
    {
        return $this->subject("عقد جديد - {$this->vendor->shop->name}")
                    ->view('contract.contract') ->attach($this->pdfPath, [
                            'as' => "contract_{$this->vendor->id}.pdf",
                            'mime' => 'application/pdf',
                        ]);
                    // ->attach($this->pdfPath, [
                    //     'as' => "contract_{$this->vendor->id}.pdf",
                    //     'mime' => 'application/pdf',
                    // ]);
    }
}
