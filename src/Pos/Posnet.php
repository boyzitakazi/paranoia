<?php
namespace Paranoia\Pos;

use Paranoia\Builder\PosnetBuilderFactory;
use Paranoia\Configuration\AbstractConfiguration;
use Paranoia\Processor\PosnetProcessorFactory;
use Paranoia\Request\Request;

class Posnet extends AbstractPos
{
    /** @var PosnetBuilderFactory */
    private $builderFactory;

    /** @var PosnetProcessorFactory */
    private $processorFactory;

    public function __construct(AbstractConfiguration $configuration)
    {
        parent::__construct($configuration);
        $this->builderFactory = new PosnetBuilderFactory($this->configuration);
        $this->processorFactory = new PosnetProcessorFactory($this->configuration);
    }

    /**
     * {@inheritdoc}
     * @see \Paranoia\Pos\AbstractPos::buildRequest()
     * @throws \Paranoia\Core\Exception\NotImplementedError
     */
    protected function buildRequest(Request $request, $transactionType)
    {
        $rawRequest = $this->builderFactory->createBuilder($transactionType)->build($request);
        return array( 'xmldata' => $rawRequest);
    }

    /**
     * {@inheritdoc}
     * @see \Paranoia\Pos\AbstractPos::parseResponse()
     */
    protected function parseResponse($rawResponse, $transactionType)
    {
        return $this->processorFactory->createProcessor($transactionType)->process($rawResponse);
    }
}
