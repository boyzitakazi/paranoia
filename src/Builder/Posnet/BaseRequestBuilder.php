<?php
namespace Paranoia\Builder\Posnet;

use Paranoia\Builder\AbstractRequestBuilder;
use Paranoia\Configuration\AbstractConfiguration;
use Paranoia\Configuration\Posnet;
use Paranoia\Formatter\MoneyFormatter;
use Paranoia\Formatter\MultiDigitInstallmentFormatter;
use Paranoia\Formatter\Posnet\CustomCurrencyCodeFormatter;
use Paranoia\Formatter\Posnet\ExpireDateFormatter;
use Paranoia\Formatter\Posnet\OrderIdFormatter;
use Paranoia\Request\Request;
use Paranoia\Request\Resource\Card;
use Paranoia\Request\Resource\ResourceInterface;

abstract class BaseRequestBuilder extends AbstractRequestBuilder
{
    /** @var MoneyFormatter */
    protected $amountFormatter;

    /** @var  CustomCurrencyCodeFormatter */
    protected $currencyCodeFormatter;

    /** @var  MultiDigitInstallmentFormatter */
    protected $installmentFormatter;

    /** @var  ExpireDateFormatter */
    protected $expireDateFormatter;

    /** @var OrderIdFormatter OrderId */
    protected $orderIdFormatter;

    public function __construct(
        AbstractConfiguration $configuration,
        CustomCurrencyCodeFormatter $currencyCodeFormatter,
        MoneyFormatter $amountFormatter,
        MultiDigitInstallmentFormatter $installmentFormatter,
        ExpireDateFormatter $expireDateFormatter,
        OrderIdFormatter $orderIdFormatter
    ) {
        parent::__construct($configuration);
        $this->currencyCodeFormatter = $currencyCodeFormatter;
        $this->amountFormatter = $amountFormatter;
        $this->installmentFormatter = $installmentFormatter;
        $this->expireDateFormatter = $expireDateFormatter;
        $this->orderIdFormatter = $orderIdFormatter;
    }

    protected function buildBaseRequest(Request $request)
    {
        /** @var Posnet $configuration */
        $configuration = $this->configuration;
        return [
            'mid' => $configuration->getMerchantId(),
            'tid' => $configuration->getTerminalId(),
            'username' => $configuration->getUsername(),
            'password' => $configuration->getPassword()
        ];
    }

    protected function buildCard(ResourceInterface $card)
    {
        assert($card instanceof Card);

        /** @var Card $_card */
        $_card = $card;

        return [
            'ccno' => $_card->getNumber(),
            'cvc' => $_card->getSecurityCode(),
            'expDate' => $this->expireDateFormatter->format(
                [
                    $_card->getExpireMonth(),
                    $_card->getExpireYear()
                ]
            )
        ];
    }
}
