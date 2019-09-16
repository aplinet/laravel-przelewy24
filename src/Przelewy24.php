<?php

namespace Adams\Przelewy24;

use Illuminate\Support\Str;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response as HttpResponse;
use Adams\Przelewy24\Exceptions\ResponseException;
use Adams\Przelewy24\Contracts\SignableContract;

class Przelewy24
{
    /**
     * Available endpoints.
     */
    const ENDPOINT_LIVE = 'https://secure.przelewy24.pl';
    const ENDPOINT_SANDBOX = 'https://sandbox.przelewy24.pl';

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
        $this->httpClient = new HttpClient();
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
        $payload = $payload ?? new TestConnection();

        return $this->request(
            'testConnection', $payload
        );
    }

    /**
     * Send register new transaction request.
     * 
     * @param Transaction $transaction
     * @return Response $transaction
     */
    public function register(Transaction $payload)
    {
        $this->testConnection();

        return $this->request(
            'trnRegister', $payload
        );
    }

    /**
     * Send verify notified transaction request.
     * 
     * @param TransactionConfirmation $payload
     * @return Response
     */
    public function verify(TransactionConfirmation $payload)
    {
        $this->testConnection();

        return $this->request(
            'trnVerify', $payload
        );
    }

    /**
     * Generate redirect response to registered transaction
     * with given token.
     * 
     * @param string|Response|Transaction $token
     * @return Illuminate\Http\Response
     */
    public function redirect($token)
    {
        if ($token instanceof Transaction) {
            $token = $this->register($token);
        }

        if ($token instanceof Response) {
            $token = $token->getToken();
        }

        return redirect()->away(
            $this->getUrl('trnRequest/' . $token)
        );
    }

    /**
     * Get crc for signing requests.
     * 
     * @return string
     */
    public function getCrc()
    {
        return config('przelewy24.crc', '');
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
        return ! $this->isLive();
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
     * Parse API response for processing. 
     * 
     * @param HttpResponse $response
     * @return Response
     */
    protected function parseHttpResponse(HttpResponse $response)
    {
        $contents = $response->getBody()->getContents();

        parse_str($contents, $attributes);

        return new Response($attributes);
    }

    /**
     * Throw response exception.
     * 
     * @param Response $response
     * @return void
     * @throws ResponseException
     */
    protected function throwResponseException(Response $response)
    {
        throw new ResponseException(
            $response->getErrorMessage()
        );
    }

    /**
     * Send request to endpoint and process response.
     * 
     * @param string $uri
     * @param SignableContract $signable
     * @return Response
     * @throws ResponseException
     */
    public function request($uri, SignableContract $signable)
    {
        $httpResponse = $this->httpClient->post($this->getUrl($uri), [
            'form_params' => $signable->getSignedAttributes($this->getCrc())
        ]);

        $response = $this->parseHttpResponse($httpResponse);

        if ($response->error()) {
            $this->throwResponseException($response);
        }

        return $response;
    }
}