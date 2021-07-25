<?php

namespace App\Utils;

class ChainUtil
{
    private string $chain;

    public function __construct(string $chain)
    {
        $this->chain = $chain;
    }

    public function getChainExplorerUrl(): string
    {
        if ($this->chain === 'bsc') {
            return 'https://bscscan.com';
        } else if($this->chain === 'polygon') {
            return 'https://polygonscan.com';
        } else if($this->chain === 'fantom') {
            return 'https://ftmscan.com';
        }

        return 'https://bscscan.com';
    }
}