<?php

namespace Adams\Przelewy24\Http\Controllers;

use Adams\Przelewy24\Facades\Facade as Przelewy24;
use Adams\Przelewy24\TransactionConfirmation;
use Adams\Przelewy24\Events\TransactionReceived;
use Adams\Przelewy24\Events\TransactionVerified;
use Adams\Przelewy24\Http\Middleware\AuthorizePrzelewy24Servers;
use Adams\Przelewy24\Http\Requests\TransactionRequest;
use Illuminate\Routing\Controller;

class WebhookController extends Controller 
{
    /**
     * Determine that transactions will be verified
     * automatically when notification was received.
     * 
     * @var bool
     */
    protected $verifyTransactions = true;
    
    /**
     * Create new object instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware(AuthorizePrzelewy24Servers::class);
    }

    /**
     * Handle all incoming transaction notifications
     * from provider.
     * 
     * @param TransactionRequest $request
     * @return Illuminate\Http\Response
     */
    public function handleWebhook(TransactionRequest $request)
    {
        event(new TransactionReceived($request));

        if ($this->verifyTransactions) {
            $this->verifyTransaction($request);
        }

        return response('OK');
    }

    /**
     * Verify received transaction.
     * 
     * @param TransactionRequest $request
     * @return void
     */
    protected function verifyTransaction(TransactionRequest $request)
    {
        $payload = new TransactionConfirmation();
        $payload->setAttributes(
            $request->only($payload->getAttributeKeys())
        );

        Przelewy24::verify($payload);

        event(new TransactionVerified($request));
    }
}