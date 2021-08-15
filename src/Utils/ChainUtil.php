<?php

namespace App\Utils;

class ChainUtil
{
    public function getChainExplorerUrl(string $chain): string
    {
        if ($chain === 'bsc') {
            return 'https://bscscan.com';
        } else if($chain === 'polygon') {
            return 'https://polygonscan.com';
        } else if($chain === 'fantom') {
            return 'https://ftmscan.com';
        } else if($chain === 'kcc') {
            return 'https://explorer.kcc.io/en';
        }

        return 'https://bscscan.com';
    }
}