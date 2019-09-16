<?php

namespace Adams\Przelewy24;

use Adams\Przelewy24\Contracts\IndexedContract;

/**
 * @property string $name
 * @property string $description
 * @property int $quantity
 * @property int $price 
 * @property int $number 
 */
class Item extends Model implements IndexedContract
{
    /**
     * Fillable fields for this payload.
     * 
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'quantity',
        'price',
        'number'
    ];

    /**
     * Return attributes with given indexes.
     * 
     * @param int $index
     * @return array
     */
    public function getIndexedAttributes($index)
    {
        $result = [];

        foreach ($this->getAttributes() as $key => $value)
        {
            $result[$key . "_$index"] = $value;
        }

        return $result;
    }
}