<?php declare(strict_types=1);

namespace App\Command;

use App\Symbol\TokenResolver;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class IconCollectorCommand extends Command
{
    protected static $defaultName = 'app:icon-collector';
    private string $projectDir;
    private ImagineInterface $imagine;
    private Filesystem $filesystem;
    private TokenResolver $tokenResolver;
    private LoggerInterface $logger;

    public function __construct(
        string $projectDir,
        ImagineInterface $imagine,
        Filesystem $filesystem,
        TokenResolver $tokenResolver,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->projectDir = $projectDir;
        $this->imagine = $imagine;
        $this->filesystem = $filesystem;
        $this->tokenResolver = $tokenResolver;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tokens = $this->tokenResolver->getPancakeTokens();

        $this->resolveTokenFolders('bsc', $tokens, $this->projectDir . '/remotes/pancake-frontend/public/images/tokens');
        $this->trustwalletTokens('bsc');

        return Command::SUCCESS;
    }

    private function trustwalletTokens(string $chain): void
    {
        $map = $this->tokenResolver->getTokenIconMap();

        $targetDir = $this->projectDir . '/var/tokens/' . $chain;

        foreach ($map as $key => $icon) {
            $createImage = function () use ($icon) {
                return $this->imagine->open($icon)
                    ->resize(new Box(64, 64))
                    ->crop(new Point(0, 0), new Box(64, 64));
            };

            if (str_starts_with($key, '0x') && strlen($key) > 10) {
                $targetAddressIcon = $targetDir . '/address/' . $key . '.png';

                if (!is_file($targetAddressIcon)) {
                    $createImage()->save($targetAddressIcon, ['quality' => 75]);
                }
            } elseif(preg_match('#^[\w-]+$#', $key)) {
                $targetSymbolIcon = $targetDir . '/symbol/' . $key . '.png';
                if (!is_file($targetSymbolIcon)) {
                    $createImage()->save($targetSymbolIcon, ['quality' => 75]);
                }

                // general
                $targetGeneralSymbolIcon = $this->projectDir . '/var/tokens/' . $key . '.png';
                if (!is_file($targetGeneralSymbolIcon)) {
                    $this->filesystem->copy($targetSymbolIcon, $targetGeneralSymbolIcon);
                }
            } else {
                $this->logger->debug('Skip icon:' . $key);
            }
        }
    }

    protected function resolveTokenFolders(string $chain, array $tokens, string $dir): void
    {
        $targetDir = $this->projectDir . '/var/tokens/' . $chain;

        $this->filesystem->mkdir([$targetDir . '/symbol', $targetDir . '/address']);

        $finder = new Finder();
        $finder->name('0x*.png');

        foreach ($finder->in($dir)->files() as $file) {
            $address = strtolower($file->getFilenameWithoutExtension());

            $targetAddressIcon = $targetDir . '/address/' . $address . '.png';

            if (!is_file($targetAddressIcon)) {
                $image = $this->imagine->open($file->getPathname())
                    ->resize(new Box(64, 64))
                    ->crop(new Point(0, 0), new Box(64, 64));

                $image->save($targetAddressIcon, ['quality' => 75]);
            }

            if (isset($tokens[$address]['symbol'])) {
                $targetSymbolIcon = $targetDir . '/symbol/' . strtolower($tokens[$address]['symbol']) . '.png';
                if (!is_file($targetSymbolIcon)) {
                    $this->filesystem->copy($targetAddressIcon, $targetSymbolIcon);
                }

                // general
                $targetGeneralSymbolIcon = $this->projectDir . '/var/tokens/' . strtolower($tokens[$address]['symbol']) . '.png';
                if (!is_file($targetGeneralSymbolIcon)) {
                    $this->filesystem->copy($targetAddressIcon, $targetGeneralSymbolIcon);
                }
            }
        }
    }
}
