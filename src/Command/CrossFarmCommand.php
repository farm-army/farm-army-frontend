<?php

namespace App\Command;

use App\Client\NodeClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrossFarmCommand extends Command
{
    protected static $defaultName = 'app:cross_fetch_farm';
    private NodeClient $nodeClient;

    public function __construct(
        NodeClient $nodeClient
    ) {
        parent::__construct();
        $this->nodeClient = $nodeClient;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->nodeClient->updateFarms(true);

        return 0;
    }
}