<?php

namespace Adams\Przelewy24\Contracts;

interface IndexedContract
{
    /**
     * Return attributes with given indexes.
     * 
     * @param int $index
     * @return array
     */
    public function getIndexedAttributes($index);
}