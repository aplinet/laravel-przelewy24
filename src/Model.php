<?php

namespace Adams\Przelewy24;

use Illuminate\Support\Str;
use Adams\Przelewy24\Contracts\SignableContract;
use Adams\Przelewy24\Exceptions\Przelewy24Exception;

class Model implements SignableContract
{
    /**
     * Fillable fields.
     * 
     * @var array
     */
    protected $fillable = [];
    
    /**
     * Fields needed to sign payload.
     * 
     * @var array
     */
    protected $signable = [];

    /**
     * Default attributes filled automatically
     * from configuration file.
     */
    protected $defaultAttributes = [
        'merchant_id',
        'pos_id'
    ];

    /**
     * Attributes.
     * 
     * @var array
     */
    protected $attributes = [];

    /**
     * Create new class object.
     * 
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->fillDefaultAttributes();

        $this->setAttributes($attributes);
    }

    /**
     * Get field names that can be filled.
     * 
     * @return array
     */
    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * Get field names that can be filled
     * with prefix supported by API.
     * 
     * @return array
     */
    public function getAttributeKeys()
    {
        return array_map([$this, 'getAttributeKey'],
            $this->getFillable());
    }

    /**
     * Fill default attributes from environment.
     * 
     * @return void
     */
    protected function fillDefaultAttributes()
    {
        foreach ($this->defaultAttributes as $name)
        {
            if (! in_array($name, $this->getFillable())) {
                continue;
            }

            $this->setAttribute(
                $name, config("przelewy24.$name")
            );
        }
    }

    /**
     * Set given attribute array with field compatibility 
     * check.
     * 
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value)
        {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * Get all attributes with values.
     * 
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set available attribute value.
     * 
     * @param string $name
     * @param string|int|null $name
     * @return void
     */
    public function setAttribute($name, $value)
    {
        $this->checkAttributeName($name);

        $key = $this->getAttributeKey($name);

        if (is_null($value) && array_key_exists($key, $this->attributes)) {
            unset($this->attributes[$key]);
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Get name for attribute key with valid prefix.
     * 
     * @param string $name
     * @return string
     */
    public function getAttributeKey($name)
    {
        if (Str::startsWith($name, 'p24_')) {
            return $name;
        }

        return 'p24_' . $name;
    }

    /**
     * Get available attribute value.
     * 
     * @param string $name
     * @return string|int|null
     */
    public function getAttribute($name)
    {
        $this->checkAttributeName($name);

        $key = $this->getAttributeKey($name);

        return $this->attributes[$key] ?? null;
    }

    /**
     * Check if attribute name can be filled.
     * 
     * @param string $name
     * @return void
     */
    protected function checkAttributeName($name)
    {
        $name = Str::replaceFirst('p24_', '', $name);

        if (! in_array($name, $this->getFillable())) {
            $this->throwUnsupportedAttributeException($name);
        }
    }

    /**
     * Throw exception for unsupported attribute name which is not
     * fillable for this instance.
     * 
     * @param string $name
     * @throws Przelewy24Exception
     */
    protected function throwUnsupportedAttributeException($name)
    {
        throw new Przelewy24Exception("Attribute $name is not supported for this instance.");
    }

    /**
     * Calculate signature of attributes.
     * 
     * @return string
     */
    public function sign($crc)
    {
        $payload = '';

        foreach ($this->signable as $name)
        {
            $payload .= $this->getAttribute($name) . '|';
        }

        return md5($payload . $crc);
    }

    /**
     * Get all attributes with calculated signature.
     * 
     * @return array
     */
    public function getSignedAttributes($crc)
    {
        $attributes = $this->getAttributes();
        $attributes['p24_sign'] = $this->sign($crc);

        return $attributes;
    }

    /**
     * Magic function to get instance fillable attributes.
     * 
     * @param string $name
     * @return string|int
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * Magic function to set instance fillable attributes.
     * 
     * @param string $name
     * @param string|int $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * Magic callback handler to provide getters and setters
     * for fillable attributes.
     * 
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws Przelewy24Exception
     */
    public function __call($method, array $arguments = [])
    {
        if (! preg_match('/^(?P<type>get|set)(?P<name>\w+)$/', $method, $matches)) {
            throw new Przelewy24Exception("Unable to call $method is not valid getter or setter.");
        }

        $name = Str::snake($matches['name']);

        if ('get' == $matches['type']) {
            return $this->getAttribute($name);
        }

        if ('set' == $matches['type']) {
            if (count($arguments) != 1) {
                throw new Przelewy24Exception('Invalid argument count passed to setter - excepted 1.');
            }

            $this->setAttribute($name, $arguments[0]);
        }
    }
}