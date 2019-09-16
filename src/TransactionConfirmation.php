<?php

namespace Adams\Przelewy24;

/**
 * @property int $merchant_id
 * @property int $pos_id
 * @property string $session_id
 * @property int $amount
 * @property string $currency
 * @property string $order_id
 * @property int $method
 * @property string $statement
 */
class TransactionConfirmation extends Model
{
    /**
     * Fillable fields for this model.
     * 
     * @var array
     */
    protected $fillable = [
        'merchant_id',
        'pos_id',
        'session_id',
        'amount',
        'currency',
        'order_id',
        'method',
        'statement'
    ];
    
    /**
     * Fields needed to sign model.
     * 
     * @var array
     */
    protected $signable = [
        'session_id',
        'order_id',
        'amount',
        'currency'
    ];
}