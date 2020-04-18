<?php
namespace Paranoia\Gvp\RequestBuilder;

use Paranoia\Configuration\GvpConfiguration;
use Paranoia\Core\Formatter\IsoNumericCurrencyCodeFormatter;
use Paranoia\Core\Formatter\MoneyFormatter;
use Paranoia\Core\Request\RefundRequest;
use Paranoia\Core\RequestBuilder\RefundRequestBuilder as CoreRefundRequestBuilderAlias;
use Paranoia\Core\Serializer\Serializer;

class RefundRequestBuilder extends BaseRequestBuilder implements CoreRefundRequestBuilderAlias
{
    const TRANSACTION_TYPE = 'refund';
    const ENVELOPE_NAME = 'GVPSRequest';
    const API_VERSION = 'v0.01';
    const CARD_HOLDER_PRESENT_CODE_NON_3D = 0;

    /** @var MoneyFormatter */
    protected $amountFormatter;

    /** @var IsoNumericCurrencyCodeFormatter */
    protected $currencyFormatter;

    /**
     * RefundRequestBuilder constructor.
     * @param GvpConfiguration $configuration
     * @param MoneyFormatter $amountFormatter
     * @param IsoNumericCurrencyCodeFormatter $currencyFormatter
     */
    public function __construct(
        GvpConfiguration $configuration,
        MoneyFormatter $amountFormatter,
        IsoNumericCurrencyCodeFormatter $currencyFormatter
    ) {
        parent::__construct($configuration);
        $this->amountFormatter = $amountFormatter;
        $this->currencyFormatter = $currencyFormatter;
    }

    /**
     * @param RefundRequest $request
     * @return array
     */
    public function build(RefundRequest $request): array
    {
        $hash = $this->buildHash(
            [
                $request->getOrderId(),
                $this->configuration->getTerminalId(),
                $this->amountFormatter->format($request->getAmount()),
            ],
            $this->configuration->getRefundPassword()
        );

        $data = [
            'Version' => self::API_VERSION,
            'Mode' => $this->configuration->getMode(),
            'Terminal' => $this->buildTerminal($this->configuration->getRefundUsername(), $hash),
            'Order' => $this->buildOrder($request->getOrderId()),
            'Customer' => $this->buildCustomer(),
            'Transaction' => $this->buildTransaction(
                $request->getAmount(),
                $request->getCurrency()
            ),
        ];

        $serializer = new Serializer(Serializer::XML);
        $xml =  $serializer->serialize($data, ['root_name' => self::ENVELOPE_NAME]);
        return ['data' => $xml];
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return array
     */
    private function buildTransaction(float $amount, string $currency): array
    {
        $data = [
            'Type' => self::TRANSACTION_TYPE,
            'Amount' => $this->amountFormatter->format($amount),
            'CurrencyCode' => $this->currencyFormatter->format($currency),
            'CardholderPresentCode' => self::CARD_HOLDER_PRESENT_CODE_NON_3D,
            'MotoInd' => 'N',
            'OriginalRetrefNum' => null,
        ];

        return $data;
    }
}