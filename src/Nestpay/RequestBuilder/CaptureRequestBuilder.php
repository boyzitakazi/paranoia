<?php
namespace Paranoia\Nestpay\RequestBuilder;

use Paranoia\Configuration\NestpayConfiguration;
use Paranoia\Core\Formatter\DecimalFormatter;
use Paranoia\Core\Formatter\IsoNumericCurrencyCodeFormatter;
use Paranoia\Core\Request\CaptureRequest;
use Paranoia\Core\RequestBuilder\CaptureRequestBuilder as CoreCaptureRequestBuilderAlias;
use Paranoia\Core\Serializer\Serializer;

class CaptureRequestBuilder implements CoreCaptureRequestBuilderAlias
{
    const TRANSACTION_TYPE = 'PostAuth';
    const ENVELOPE_NAME = 'CC5Request';
    const FORM_FIELD = 'DATA';

    /** @var NestpayConfiguration */
    protected $configuration;

    /** @var DecimalFormatter */
    protected $amountFormatter;

    /** @var IsoNumericCurrencyCodeFormatter */
    protected $currencyFormatter;

    /**
     * AuthorizationRequestBuilder constructor.
     * @param NestpayConfiguration $configuration
     * @param DecimalFormatter $amountFormatter
     * @param IsoNumericCurrencyCodeFormatter $currencyFormatter
     */
    public function __construct(
        NestpayConfiguration $configuration,
        DecimalFormatter $amountFormatter,
        IsoNumericCurrencyCodeFormatter $currencyFormatter
    ) {
        $this->configuration = $configuration;
        $this->amountFormatter = $amountFormatter;
        $this->currencyFormatter = $currencyFormatter;
    }

    public function build(CaptureRequest $request): array
    {
        $data = [
            'Name' => $this->configuration->getUsername(),
            'ClientId' => $this->configuration->getClientId(),
            'Type' => self::TRANSACTION_TYPE,
            'OrderId' => $request->getTransactionRef(),
            'Total' => $this->amountFormatter->format($request->getAmount()),
            'Currency' => $this->currencyFormatter->format($request->getCurrency()),
        ];

        $serializer = new Serializer(Serializer::XML);
        $xml =  $serializer->serialize($data, ['root_name' => self::ENVELOPE_NAME]);
        return [self::FORM_FIELD => $xml];
    }
}
