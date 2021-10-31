<?php declare(strict_types=1);

namespace App\Command;

use App\Symbol\TokenResolver;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
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
    private ClientInterface $client;

    public function __construct(
        string $projectDir,
        ImagineInterface $imagine,
        Filesystem $filesystem,
        TokenResolver $tokenResolver,
        LoggerInterface $logger,
        ClientInterface $client
    ) {
        parent::__construct();
        $this->projectDir = $projectDir;
        $this->imagine = $imagine;
        $this->filesystem = $filesystem;
        $this->tokenResolver = $tokenResolver;
        $this->logger = $logger;
        $this->client = $client;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tokens = $this->tokenResolver->getPancakeTokens();

        $this->resolveTokenFolders('bsc', $tokens, $this->projectDir . '/remotes/pancake-frontend/public/images/tokens');
        $this->trustwalletTokens('bsc');

        $this->tokenList('https://tokens.pancakeswap.finance/pancakeswap-top-100.json');
        $this->tokenList('https://tokens.pancakeswap.finance/pancakeswap-extended.json');

        $this->tokenList('https://unpkg.com/@sushiswap/default-token-list/build/sushiswap-default.tokenlist.json');
        $this->tokenList('https://unpkg.com/quickswap-default-token-list/build/quickswap-default.tokenlist.json');

        $this->tokenList('https://raw.githubusercontent.com/Ubeswap/default-token-list/master/ubeswap.token-list.json');
        $this->tokenList('https://raw.githubusercontent.com/Ubeswap/default-token-list/master/ubeswap-experimental.token-list.json');

        $this->tokenList('https://charts-bomb.zoocoin.cash/api/tokenlistbot');

        return Command::SUCCESS;
    }

    private function trustwalletTokens(string $chain): void
    {
        $map = $this->tokenResolver->getChainTokens($chain);

        $targetDir = $this->projectDir . '/var/tokens/' . $chain;

        foreach ($map as $token) {
            if (!isset($token['icon'])) {
                $this->logger->debug('Skip icon:' . $token['symbol'] ?? '');
                continue;
            }

            $createImage = function () use ($token) {
                return $this->imagine->open($token['icon'])
                    ->resize(new Box(64, 64))
                    ->crop(new Point(0, 0), new Box(64, 64));
            };

            if (isset($token['address']) && str_starts_with($token['address'], '0x') && strlen($token['address']) > 10) {
                $targetAddressIcon = $targetDir . '/address/' . strtolower($token['address']) . '.png';

                if (!is_file($targetAddressIcon)) {
                    $createImage()->save($targetAddressIcon, ['quality' => 75]);
                }
            }

            if (isset($token['symbol']) && preg_match('#^[\w-]+$#', $token['symbol'])) {
                $targetSymbolIcon = $targetDir . '/symbol/' . strtolower($token['symbol']) . '.png';
                if (!is_file($targetSymbolIcon)) {
                    $createImage()->save($targetSymbolIcon, ['quality' => 75]);
                }

                // general
                $targetGeneralSymbolIcon = $this->projectDir . '/var/tokens/' . strtolower($token['symbol']) . '.png';
                if (!is_file($targetGeneralSymbolIcon)) {
                    $this->filesystem->copy($targetSymbolIcon, $targetGeneralSymbolIcon);
                }
            } else {
                $this->logger->debug('Skip icon:' . $token['symbol'] ?? '');
            }
        }
    }

    private function tokenList(string $url): void
    {
        try {
            $decode = json_decode($this->client->request('GET', $url)->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->logger->error('Error ' . $e->getMessage());
            return;
        }

        $chains = [
            56 => 'bsc',
            137 => 'polygon',
            250 => 'fantom',
            321 => 'kcc',
            42220 => 'celo',
            1285 => 'moonriver',
        ];

        foreach ($decode['tokens'] ?? [] as $token) {
            if (!isset($token['chainId'], $chains[$token['chainId']])) {
                continue;
            }

            $chain = $chains[$token['chainId']];

            $targetDir = $this->projectDir . '/var/tokens/' . $chain;
            $this->filesystem->mkdir([$targetDir . '/symbol', $targetDir . '/address']);

            if (!isset($token['logoURI'])) {
                $this->logger->debug('Skip icon:' . $token['logoURI'] ?? '');
                continue;
            }

            $createImage = function () use ($token): ?ImageInterface {
                try {
                    $response = $this->client->request('GET', $token['logoURI']);
                } catch (GuzzleException $e) {
                    $this->logger->debug(sprintf("icon error:%s - %s - %s", $token['symbol'] ?? '', $token['logoURI'] ?? '', $e->getMessage()));
                    return null;
                }

                $type = $response->getHeaderLine('content-type');

                if (!$type || strtolower($type) !== 'image/png') {
                    $this->logger->warning('icon content-type invalid: ' . $type);
                    return null;
                }

                return $this->imagine->load($response->getBody()->getContents())
                    ->resize(new Box(64, 64))
                    ->crop(new Point(0, 0), new Box(64, 64));
            };

            if (isset($token['address']) && str_starts_with($token['address'], '0x') && strlen($token['address']) > 10) {
                $targetAddressIcon = $targetDir . '/address/' . strtolower($token['address']) . '.png';

                if (!is_file($targetAddressIcon) && $img = $createImage()) {
                    $img->save($targetAddressIcon, ['quality' => 75]);
                }
            }

            if (isset($token['symbol']) && preg_match('#^[\w-]+$#', $token['symbol'])) {
                $targetSymbolIcon = $targetDir . '/symbol/' . strtolower($token['symbol']) . '.png';
                if (!is_file($targetSymbolIcon) && $img = $createImage()) {
                    $img->save($targetSymbolIcon, ['quality' => 75]);

                    // general
                    $targetGeneralSymbolIcon = $this->projectDir . '/var/tokens/' . strtolower($token['symbol']) . '.png';
                    if (!is_file($targetGeneralSymbolIcon)) {
                        $this->filesystem->copy($targetSymbolIcon, $targetGeneralSymbolIcon);
                    }
                }
            } else {
                $this->logger->debug('Skip icon:' . $token['symbol'] ?? '');
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
