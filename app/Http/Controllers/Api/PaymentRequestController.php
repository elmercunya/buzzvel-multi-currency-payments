<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ExchangeRateService;
use Carbon\Carbon;
use App\Models\PaymentRequest;

class PaymentRequestController extends Controller
{
    public function index(Request $request) {

        $query = PaymentRequest::query();

        if($request->user()->role === 'employee') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json([
            'paymentRequests' => $query->paginate(10)
        ]);

    }

    public function store(Request $request) {
        $data = $request->validate([
            'amount_local' => 'required|numeric|min:0.01',
            'reason' => 'required|string'
        ]);

        $currency_code = $request->user()->currency_code;

        $exchangeService = new ExchangeRateService();
        $exchange_rate_eur_to_local = $exchangeService->getRate($currency_code);

        $amount_eur = round($request->amount_local / $exchange_rate_eur_to_local, 2);

        $paymentRequest = PaymentRequest::create([
            'user_id' => $request->user()->id,
            'amount_local' => $request->amount_local,
            'currency_code' => $currency_code ,
            'reason' => $request->reason,
            'exchange_rate_eur_to_local' => $exchange_rate_eur_to_local,
            'amount_eur' => $amount_eur,
            'exchange_rate_source' => 'exchangerate-api.com',
            'exchange_rate_fetched_at' => Carbon::now(),
            'status' => 'pending',
            'expires_at' => Carbon::now()->addHours(48),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Solicitud enviada correctamente',
            'paymentRequest' => $paymentRequest
        ]);
    }

    public function show(Request $request, $id)
    {
        $paymentRequest = PaymentRequest::findOrFail($id);

        if($request->user()->role === 'employee' && !($request->user()->id === $paymentRequest->user_id)) {
            return response()->json([
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'paymentRequest' => $paymentRequest
        ]);
    }
}
