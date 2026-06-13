<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentRequest;
use Carbon\Carbon;

class ExpirePaymentRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expira solicitudes de pago pendientes después de 48 horas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        PaymentRequest::where('status', 'pending')->where('expires_at', '<', Carbon::now())->update(['status' => 'expired']);
    }
}
