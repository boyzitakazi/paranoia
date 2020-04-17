<?php
namespace Paranoia\Processor\Posnet;

use Paranoia\Core\Exception\InvalidResponseException;
use Paranoia\Processor\AbstractResponseProcessor;
use Paranoia\Response;

abstract class BaseResponseProcessor extends AbstractResponseProcessor
{
    /**
     * @param \SimpleXMLElement $xml
     * @param Response $response
     */
    private function prepareErrorDetails(\SimpleXMLElement $xml, Response $response)
    {
        $errorMessages = array();
        if (property_exists($xml, 'respCode')) {
            $response->setResponseCode((string)$xml->respCode);
        }
        if (property_exists($xml, 'respText')) {
            $errorMessages[] = sprintf('%s ', (string)$xml->respText);
        }
        $errorMessage = implode(' ', $errorMessages);
        $response->setResponseMessage($errorMessage);
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param Response $response
     */
    private function prepareTransactionDetails(\SimpleXMLElement $xml, Response $response)
    {
        if (property_exists($xml, 'orderId')) {
            $response->setOrderId((string)$xml->orderId);
        }
        $response->setTransactionId((string)$xml->hostlogkey);
        if (property_exists($xml, 'authCode')) {
            $response->setAuthCode((string)$xml->authCode);
        }
    }

    /**
     * @param $rawResponse
     * @return Response
     * @throws InvalidResponseException
     */
    protected function processCommonResponse($rawResponse)
    {
        try {
            /** @var \SimpleXMLElement $xml */
            $xml = new \SimpleXmlElement($rawResponse);
        } catch (\Exception $e) {
            $exception = new InvalidResponseException('Provider returned unexpected response: ' . $rawResponse);
            throw $exception;
        }
        $this->validateResponse($xml);
        $response = new Response();
        $response->setIsSuccess((int)$xml->approved > 0);
        if (!$response->isSuccess()) {
            $this->prepareErrorDetails($xml, $response);
        } else {
            $this->prepareTransactionDetails($xml, $response);
        }
        return $response;
    }

    protected function validateResponse($transformedResponse)
    {
        //TODO: response validation should implemented.
        return true;
    }
}
