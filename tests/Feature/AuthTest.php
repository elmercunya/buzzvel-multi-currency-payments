<?php

use App\Models\User;
use App\Models\PaymentRequest;
use Carbon\Carbon;

it('a user can register', function () {

    $response = $this->post('/api/register', [
        'name' => 'Juan',
        'email' => 'juan@test.com',
        'password' => '123456',
        'country_code' => 'PE',
        'currency_code' => 'PEN'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', ['email' => 'juan@test.com']);
});


it('a user can login', function() {
    $user = User::create([
        'name' => 'Juan',
        'email' => 'juan@test.com',
        'password' => bcrypt('123456'),
        'country_code' => 'PE',
        'currency_code' => 'PEN'
    ]);

    $response = $this->post('/api/login', [
        'email' => 'juan@test.com',
        'password' => '123456'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $user->id]);
});

it('an authenticated employee can create a payment request', function () {

    $user = User::create([
        'name' => 'Juan',
        'email' => 'juan@test.com',
        'password' => bcrypt('123456'),
        'country_code' => 'PE',
        'currency_code' => 'PEN'
    ]);

    Http::fake([
        '*' => Http::response([
            'conversion_rates' => [
                'PEN' => 3.9178
            ]
        ])
    ]);

    $response = $this->actingAs($user)->post('/api/payment-requests', [
        'amount_local' => 10000.00,
        'reason' => 'Equipo de computo'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('payment_requests', ['user_id' => $user->id]);

});

it('a finance user can approve a pending payment request', function () {
    $user = User::create([
        'name' => 'Felipe',
        'email' => 'felipe@test.com',
        'password' => bcrypt('123456'),
        'role' => 'finance',
        'country_code' => 'PE',
        'currency_code' => 'PEN'
    ]);

    $paymentRequest = PaymentRequest::create([
        'user_id' => $user->id,
        'amount_local' => 391.78,
        'currency_code' => $user->currency_code,
        'exchange_rate_eur_to_local' => 3.917800,
        'amount_eur' => 100.00,
        'exchange_rate_source' => 'exchangerate-api.com',
        'exchange_rate_fetched_at' => '2026-06-13 14:04:00',
        'status' => 'pending',
        'reason' => 'Compra de equipos',
        'expires_at' => '2026-06-15 14:04:00'
    ]);

    $response = $this->actingAs($user)->patch('api/payment-requests/'.$paymentRequest->id.'/approve', []);

    $response->assertStatus(200);
    $this->assertDatabaseHas('payment_requests', ['status' => 'approved']);
});

it('a non-finance user cannot approve a payment request', function () {

    $user = User::create([
        'name' => 'Juan',
        'email' => 'juan@test.com',
        'password' => bcrypt('123456'),
        'role' => 'employee',
        'country_code' => 'PE',
        'currency_code' => 'PEN'
    ]);

    $paymentRequest = PaymentRequest::create([
        'user_id' => $user->id,
        'amount_local' => 391.78,
        'currency_code' => $user->currency_code,
        'exchange_rate_eur_to_local' => 3.917800,
        'amount_eur' => 100.00,
        'exchange_rate_source' => 'exchangerate-api.com',
        'exchange_rate_fetched_at' => '2026-06-13 14:04:00',
        'status' => 'pending',
        'reason' => 'Compra de equipos',
        'expires_at' => '2026-06-15 14:04:00'
    ]);

    $response = $this->actingAs($user)->patch('api/payment-requests/'.$paymentRequest->id.'/approve', []);

    $response->assertStatus(403);
    $this->assertDatabaseHas('payment_requests', ['status' => 'pending']);
});

it('The expiration command changes the status to expired', function () {
    $user = User::create([
        'name' => 'Juan',
        'email' => 'juan@test.com',
        'password' => bcrypt('123456'),
        'role' => 'employee',
        'country_code' => 'PE',
        'currency_code' => 'PEN'
    ]);

    $paymentRequest = PaymentRequest::create([
        'user_id' => $user->id,
        'amount_local' => 391.78,
        'currency_code' => $user->currency_code,
        'exchange_rate_eur_to_local' => 3.917800,
        'amount_eur' => 100.00,
        'exchange_rate_source' => 'exchangerate-api.com',
        'exchange_rate_fetched_at' => '2026-06-08 14:04:00',
        'status' => 'pending',
        'reason' => 'Compra de equipos',
        'expires_at' => '2026-06-10 14:04:00'
    ]);

    $command = (new \App\Console\Commands\ExpirePaymentRequests())->handle();;

    $paymentRequest->refresh();

    $this->assertDatabaseHas('payment_requests', ['status' => 'expired']);

});