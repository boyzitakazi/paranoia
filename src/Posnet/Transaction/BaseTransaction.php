<?php
namespace Paranoia\Posnet\Transaction;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Paranoia\Configuration\PosnetConfiguration;
use Paranoia\Core\Exception\CommunicationError;
use Psr\Http\Message\ResponseInterface;

abstract class BaseTransaction
{
    /** @var PosnetConfiguration */
    protected $configuration;

    /** @var Client */
    protected $client;

    /**
     * BaseTransaction constructor.
     * @param PosnetConfiguration $configuration
     * @param Client $client
     */
    public function __construct(PosnetConfiguration $configuration, Client $client)
    {
        $this->configuration = $configuration;
        $this->client = $client;
    }

    /**
     * @param array $data
     * @return ResponseInterface
     * @throws CommunicationError
     */
    protected function sendRequest(array $data): ResponseInterface
    {
        try {
            /** @var ResponseInterface $response */
            return $this->client->post($this->configuration->getApiUrl(), [
                'verify' => true,
                'curl' => [
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2
                ],
                'form_params' => $data,
            ]);
        } catch (GuzzleException $exception) {
            // TODO: Handle client and connection errors separately
            throw new CommunicationError($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}