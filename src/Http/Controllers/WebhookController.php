<?php

namespace Adams\Przelewy24\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Adams\Przelewy24\TransactionConfirmation;
use Adams\Przelewy24\Events\TransactionReceived;
use Adams\Przelewy24\Events\TransactionVerified;
use Adams\Przelewy24\Facades\Facade as Przelewy24;
use Adams\Przelewy24\Http\Middleware\AuthorizePrzelewy24Servers;

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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'p24_merchant_id' => 'required|numeric',
            'p24_pos_id' => 'required|numeric',
            'p24_session_id' => 'required',
            'p24_amount' => 'required|numeric',
            'p24_currency' => 'required',
            'p24_order_id' => 'required|numeric',
            'p24_method' => 'required|numeric',
            'p24_statement' => 'required',
            'p24_sign' => 'required'
        ];
    }

    /**
     * Handle all incoming transaction notifications
     * from provider.
     * 
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function handle(Request $request)
    {
        Validator::validate($request->all(), $this->rules());
        event(new TransactionReceived($request));

        if ($this->verifyTransactions) {
            $this->verifyTransaction($request);
        }

        return response('OK');
    }

    /**
     * Verify received transaction.
     * 
     * @param Request $request
     * @return void
     */
    protected function verifyTransaction(Request $request)
    {
        $payload = new TransactionConfirmation();
        $payload->setAttributes(
            $request->only($payload->getAttributeKeys())
        );

        Przelewy24::verify($payload);

        event(new TransactionVerified($request));
    }
}
