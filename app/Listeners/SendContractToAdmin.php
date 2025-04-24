<?php

namespace App\Listeners;

use App\Events\ContractSignedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContractPdfMail;

class SendContractToAdmin implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ContractSignedEvent $event)
    {
        try {
            Mail::to('info@realsilk.sa')
                ->send(new ContractPdfMail(
                    $event->vendor,
                    $event->contractPath,
                    $event->contractContent
                ));
        } catch (\Exception $e) {
            \Log::error("فشل إرسال عقد البائع: " . $e->getMessage());
        }
    }
}
