<?php

namespace App\Command;

use App\Client\NodeClient;
use App\Symbol\IconResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FarmCommand extends Command
{
    protected static $defaultName = 'app:fetch_farm';
    private NodeClient $nodeClient;
    private IconResolver $iconResolver;

    public function __construct(
        NodeClient $nodeClient,
        IconResolver $iconResolver
    ) {
        parent::__construct();
        $this->nodeClient = $nodeClient;
        $this->iconResolver = $iconResolver;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->nodeClient->getFarms(true);

        return 0;
    }
}