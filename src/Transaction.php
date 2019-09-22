<?php

namespace Adams\Przelewy24;

use Adams\Przelewy24\Exceptions\Przelewy24Exception;

/**
 * @property int $merchant_id
 * @property int $pos_id
 * @property string $session_id
 * @property int $amount
 * @property string $currency
 * @property string $description
 * @property string $email
 * @property string $client
 * @property string $address
 * @property string $zip
 * @property string $city
 * @property string $country
 * @property string $phone
 * @property string $language
 * @property int $method
 * @property string $url_return
 * @property string $url_status
 * @property int $time_limit
 * @property int $wait_for_result
 * @property int $channel
 * @property int $shipping
 * @property string $transfer_label
 * @property string $api_version
 * @property string $encoding
 */
class Transaction extends Model
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
        'description',
        'email',
        'client',
        'address',
        'zip',
        'city',
        'country',
        'phone',
        'language',
        'method',
        'url_return',
        'url_status',
        'time_limit',
        'wait_for_result',
        'channel',
        'shipping',
        'transfer_label',
        'api_version',
        'encoding'
    ];
    
    /**
     * Fields needed to sign model.
     * 
     * @var array
     */
    protected $signable = [
        'session_id',
        'merchant_id',
        'amount',
        'currency'
    ];

    /**
     * Items related to transaction.
     * 
     * @var array
     */
    protected $items = [];

    /**
     * Fill default attributes from environment.
     * 
     * @return void
     */
    protected function fillDefaultAttributes()
    {
        parent::fillDefaultAttributes();

        if (config('przelewy24.package_routes', false)) {
            $this->setUrlStatus(route('webhook.przelewy24'));
        }
        
        $this->setApiVersion(Przelewy24::API_VERSION);
        $this->setEncoding(Przelewy24::ENCODING_UTF_8);
        $this->setLanguage(Przelewy24::LANGUAGE_PL);
        $this->setCurrency(Przelewy24::CURRENCY_PLN);

        if (! is_null($name = config('przelewy24.return_route'))) {
            $this->setUrlReturn(route($name));
        }
    }

    /**
     * Override default method to get attributes with 
     * items related to transaction in valid format.
     * 
     * @return array
     */
    public function getAttributes()
    {
        $result = parent::getAttributes();

        foreach ($this->getItems() as $index => $item)
        {
            $result = array_merge(
                $result, $item->getIndexedAttributes($index + 1)
            );
        }

        return $result;
    }

    /**
     * Add given item to transaction.
     * 
     * @param Item|array $item
     * @return void
     */
    public function addItem($item)
    {
        if (is_array($item)) {
            $item = new Item($item);
        }

        if ($item instanceof Item) {
            $this->items[] = $item;
        }

        throw new Przelewy24Exception('Tried to add invalid item to transaction.');
    }

    /**
     * Get all items related with transaction.
     * 
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }
}