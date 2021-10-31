<?php declare(strict_types=1);

namespace App\Twig;

use App\Utils\ChainGuesser;
use App\Utils\ChainUtil;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class ContextExtension extends AbstractExtension implements GlobalsInterface
{
    private ChainGuesser $chainGuesser;

    public function __construct(ChainGuesser $chainGuesser)
    {
        $this->chainGuesser = $chainGuesser;
    }

    public function getGlobals(): array
    {
        return [
            'chain' => ChainUtil::getChain($this->chainGuesser->getChain()),
        ];
    }
}