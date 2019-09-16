<?php

namespace Adams\Przelewy24;

class Response 
{
    /**
     * Attributes passed to request.
     * 
     * @var array
     */
    protected $attributes;

    /**
     * Create new object instance.
     * 
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Return attributes passed to request.
     * 
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Check that error ocurred during request
     * to payment provider.
     * 
     * @return bool
     */
    public function error()
    {
        return ((int) $this->attributes['error']) > 0;
    }

    /**
     * Get error message.
     * 
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->attributes['errorMessage'] ?? '';
    }

    /**
     * Get token generated for request.
     * 
     * @return string|null
     */
    public function getToken()
    {
        return $this->attributes['token'] ?? null;
    }
}