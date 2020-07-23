<?php

namespace Adams\Przelewy24\Events;

use Illuminate\Queue\SerializesModels;
use Adams\Przelewy24\Http\Requests\TransactionRequest;

class Event
{
    use SerializesModels;

    /**
     * Unique identifier from the seller's system.
     * 
     * @var string
     */
    public $session_id;

    /**
     * Transaction amount.
     * 
     * @var int
     */
    public $amount;

    /**
     * Transaction currency.
     * 
     * @var string
     */
    public $currency;

    /**
     * Transaction number given by Przelewy24.
     * 
     * @var int
     */
    public $order_id;

    /**
     * Payment method used by the customer.
     * 
     * @var int
     */
    public $method;

    /**
     * Transfer title.
     * 
     * @var string
     */
    public $statement;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TransactionRequest $request)
    {
        $this->session_id = $request->get('p24_session_id');
        $this->amount = $request->get('p24_amount');
        $this->currency = $request->get('p24_currency');
        $this->order_id = $request->get('p24_order_id');
        $this->method = $request->get('p24_method');
        $this->statement = $request->get('p24_statement');
    }
}