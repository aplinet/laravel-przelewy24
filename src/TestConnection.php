<?php

namespace Adams\Przelewy24;

/**
 * @property int $merchant_id
 * @property int $pos_id
 */
class TestConnection extends Model 
{
    /**
     * Fillable fields for this model.
     * 
     * @var array
     */
    protected $fillable = [
        'merchant_id',
        'pos_id'
    ];

    /**
     * Fields needed to sign model.
     * 
     * @var array
     */
    protected $signable = [
        'pos_id'
    ];
}