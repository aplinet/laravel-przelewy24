<?php

namespace Adams\Przelewy24;

use Illuminate\Support\Str;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response as HttpResponse;
use Adams\Przelewy24\Contracts\SignableContract;
use Adams\Przelewy24\Exceptions\ResponseException;
use Adams\Przelewy24\Exceptions\Przelewy24ApiException;

class Przelewy24Api
{
    /**
     * Available endpoints.
     */
    const ENDPOINT_LIVE = 'https://secure.przelewy24.pl/api/v1';
    const ENDPOINT_SANDBOX = 'https://sandbox.przelewy24.pl/api/v1';


    // Transaction status. 0 - no payment, 1 - advance payment, 2 - payment made, 3 - payment returned
    /**
     * Transaction statuses
     */
    const TRANSACTION_STATUS_NO_PAYMENT = 0;
    const TRANSACTION_STATUS_ADVANCE_PAYMENT = 1;
    const TRANSACTION_STATUS_DONE = 2;
    const TRANSACTION_STATUS_RETURNED = 3;

    /**
     * Current API version.
     */
    const API_VERSION = '3.2';

    /**
     * Currencies supported by provider.
     */
    const CURRENCY_PLN = 'PLN';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_GBP = 'GBP';
    const CURRENCY_CZK = 'CZK';

    /**
     * Languages supported by provider.
     */
    const LANGUAGE_PL = 'PL';
    const LANGUAGE_EN = 'EN';
    const LANGUAGE_DE = 'DE';
    const LANGUAGE_ES = 'ES';
    const LANGUAGE_IT = 'IT';

    /**
     * Constant for unlimited transaction time
     * limit.
     */
    const TIME_LIMIT_UNLIMITED = 0;

    /**
     * Available transaction channels.
     */
    const CHANNEL_CARDS = 1;
    const CHANNEL_TRANSFER = 2;
    const CHANNEL_TRADITIONAL_TRANSFER = 4;
    const CHANNEL_PREPAID = 32;
    const CHANNEL_PAY_BY_LINK = 64;

    /**
     * Encodings supported by provider.
     */
    const ENCODING_ISO_8859_2 = 'ISO-8859-2';
    const ENCODING_UTF_8 = 'UTF-8';
    const ENCODING_WINDOWS_1250 = 'Windows-1250';

    public $defaultExceptions = [
        '400' => 'Invalid input data',
        '401' => 'Incorrect authentication',
        '403' => 'Not authorized',
        '404' => 'Not found',
        '409' => 'Conflict',
        '500' => 'Undefined error',
    ];
    /**
     * List of IP addresses allowed to send notifications
     * about processed transactions.
     * 
     * @var array
     */
    public $allowedAddresses = [
        '91.216.191.181',
        '91.216.191.182',
        '91.216.191.183',
        '91.216.191.184',
        '91.216.191.185',
        '92.43.119.144',
        '92.43.119.145',
        '92.43.119.146',
        '92.43.119.147',
        '92.43.119.148',
        '92.43.119.149',
        '92.43.119.150',
        '92.43.119.151',
        '92.43.119.152',
        '92.43.119.153',
        '92.43.119.154',
        '92.43.119.155',
        '92.43.119.156',
        '92.43.119.157',
        '92.43.119.158',
        '92.43.119.159',
    ];

    /**
     * HTTP client used to work with payment
     * provider API.
     * 
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Create new object instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'auth' => [
                config('przelewy24.merchant_id'),
                config('przelewy24.api_key')
            ],
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);
    }

    /**
     * Return list of allowed IP addresses.
     * 
     * @return array
     */
    public function getAllowedAddresses()
    {
        return $this->allowedAddresses;
    }

    /**
     * Send test connection request.
     * 
     * @param null|TestConnection $payload
     * @return Response
     */
    public function testConnection(TestConnection $payload = null)
    {
        return $this->request(
            'get',
            'testAccess'
        );
    }

    /**
     * Get transaction by session id
     * 
     * @param string $session_id
     * @return mixed Associative array of response
     */
    public function getTransaction(string $session_id)
    {
        try {
            return $this->request(
                'get',
                '/transaction/by/sessionId/' . $session_id,
                null,
                [
                    "400" => "Invalid input data",
                    "401" => "Incorrect authentication",
                    "404" => "Transaction does not exist"
                ]
            );
        } catch (\Throwable $th) {
            if ($th instanceof Przelewy24ApiException && $th->getCode() === 404) {
                return null;
            } else {
                throw $th;
            }
        }
    }


    /**
     * Check if api mode is set to live.
     * 
     * @return bool
     */
    public function isLive()
    {
        return config('przelewy24.mode', 'sandbox') === 'live';
    }

    /**
     * Check if api mode is set to sandbox.
     * 
     * @return bool
     */
    public function isSandbox()
    {
        return !$this->isLive();
    }

    /**
     * Get endpoint url used to send request.
     * 
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->isSandbox() ? self::ENDPOINT_SANDBOX
            : self::ENDPOINT_LIVE;
    }

    /**
     * Generate url for mode set in config file.
     * 
     * @param string $uri
     * @return string
     */
    public function getUrl($uri = '')
    {
        return $this->getEndpointUrl() . Str::start($uri, '/');
    }


    /**
     * Handle guzzle client exception
     * 
     * Transform generic exception to Api relevant one. 
     * 
     * @param Response $exception
     * @param mixed $exceptionMessages Array of exception messages ie. [ 404 => 'Not found',..]
     * @throws ResponseException
     */
    protected function handleException(\Throwable $exception, $exceptionMessages = [])
    {
        $exceptions = array_replace($this->defaultExceptions, $exceptionMessages);
        if ($exception instanceof ClientException) {
            if (key_exists($exception->getCode(), $exceptions)) {
                throw new Przelewy24ApiException($exceptions[$exception->getCode()], $exception->getCode());
            } else {
                throw new Przelewy24ApiException('Unknown exception code [' . $exception->getCode() . ']', $exception->getCode());
            }
        } else {
            throw $exception;
        }
    }

    /**
     * Send request to endpoint and process response.
     * 
     * @param string $method Request method
     * @param string $uri
     * @param mixed $data Request data
     * @return Response
     * @throws ResponseException
     */
    public function request(string $method, $uri, $data = null, $exceptions = [])
    {
        try {
            $response = $this->httpClient->get($this->getUrl($uri));
        } catch (\Throwable $th) {
            $this->handleException($th, $exceptions);
        }
        $result =  json_decode(((string) $response->getBody()), true);
        if ($result === null) {
            throw new Przelewy24ApiException("Could not parse response", 1);
        }

        return $result;
    }
}
