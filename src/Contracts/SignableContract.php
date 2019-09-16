<?php

namespace Adams\Przelewy24\Contracts;

interface SignableContract
{
    /**
     * Get all attributes with calculated signature.
     * 
     * @return array
     */
    public function getSignedAttributes($crc);
}