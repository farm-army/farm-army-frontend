<?php declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\HttpFoundation\RequestStack;

class ChainGuesser
{
    private string $chain;
    private RequestStack $requestStack;

    public function __construct(string $chain, RequestStack $requestStack)
    {
        $this->chain = $chain;
        $this->requestStack = $requestStack;
    }

    public function getChain(): string
    {
        if (!$request = $this->requestStack->getMasterRequest()) {
            return $this->chain;
        }

        $host = $request->getHost();

        if ($host === 'farm.army') {
            return 'bsc';
        } else if ($host === 'polygon.farm.army') {
            return 'polygon';
        } else if ($host === 'fantom.farm.army') {
            return 'fantom';
        } else if ($host === 'kcc.farm.army') {
            return 'kcc';
        } else if ($host === 'harmony.farm.army') {
            return 'harmony';
        } elseif ($host === 'celo.farm.army') {
            return 'celo';
        } elseif ($host === 'moonriver.farm.army') {
            return 'moonriver';
        }

        return $this->chain;
    }
}
