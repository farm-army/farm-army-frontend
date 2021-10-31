<?php


namespace App\Utils;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DomCrawler\Crawler;

class RandomAddress
{
    private CacheItemPoolInterface $cacheItemPool;
    private ChainGuesser $chainGuesser;

    public function __construct(CacheItemPoolInterface $cacheItemPool, ChainGuesser $chainGuesser)
    {
        $this->cacheItemPool = $cacheItemPool;
        $this->chainGuesser = $chainGuesser;
    }

    public function getRandomAddresses(): array
    {
        $cache = $this->cacheItemPool->getItem('random-address');

        if ($cache->isHit()) {
            return $cache->get();
        }

        $chain = $this->chainGuesser->getChain();

        if ($chain === 'bsc') {
            $urls = [
                '0x502AB55Cf22f38c4fd8663dEE041A96B72264c53', // beefy wbnb
                '0x4d0228EBEB39f6d2f29bA528e2d15Fc9121Ead56', // ato cake-bnb
            ];

            $scan = 'https://bscscan.com/txs?a=%s&f=3';
        } else if ($chain === 'polygon') {
            $urls = [
                '0xC8Bd86E5a132Ac0bf10134e270De06A8Ba317BFe', // pwault
                '0x8CFD1B9B7478E7B0422916B72d1DB6A9D513D734', // polycan
            ];

            $scan = 'https://polygonscan.com/txs?a=%s&f=3';
        } else if ($chain === 'fantom') {
            $urls = [
                '0x9083EA3756BDE6Ee6f27a6e996806FBD37F6F093', // spiritswap
                '0x2b2929e785374c651a81a63878ab22742656dcdd', // spookyswap
            ];

            $scan = 'https://polygonscan.com/txs?a=%s&f=3';
        } else if ($chain === 'kcc') {
            $urls = [
                '0x0cc7fb3626c55ce4eff79045e8e7cb52434431d4', // kuswap
            ];

            $scan = 'https://explorer.kcc.io/api/kcs/address/normal/%s/1/50';
        } else if ($chain === 'moonriver') {
            $urls = [
                '0xf03b75831397D4695a6b9dDdEEA0E578faa30907', // solarbeam
            ];

            $scan = 'https://blockscout.moonriver.moonbeam.network/address/%s/token-transfers?items_count=20&type=JSON';
        } else if ($chain === 'celo') {
            $urls = [
                '0x0769fd68dFb93167989C6f7254cd0D766Fb2841F', // sushi
            ];

            $scan = 'https://explorer.celo.org/address/%s/token-transfers?items_count=20&type=JSON';
        } else {
            throw new \RuntimeException('Invalid chain');
        }

        $addresses = [];

        if ($chain === 'kcc') {
            foreach ($urls as $url) {
                if (!@$content = file_get_contents(sprintf($scan, $url))) {
                    continue;
                }

                if (!$json = json_decode($content, true)) {
                    continue;
                }

                $array = array_filter($json['data'] ?? [], static function (array $item) use ($url) {
                    return isset($item['from']) && $item['from']
                        && !str_starts_with($item['from'], '0x000000000000000000000')
                        && strtolower($item['from']) !== strtolower($url);
                });

                $array = array_values(array_unique(array_map(static fn(array $item) => $item['from'], $array)));
                $addresses = array_merge($addresses, $array);
            }
        } else if ($chain === 'moonriver' || $chain === 'celo')       {
            $addresses = [];

            foreach ($urls as $url) {
                if (!@$content = file_get_contents(sprintf($scan, $url))) {
                    continue;
                }

                if (!$json = json_decode($content, true)) {
                    continue;
                }

                foreach ($json['items'] ?? [] as $item) {
                    if (preg_match('#address/(0x[a-fA-F0-9]{40})#i', $item, $result)) {
                        if (!in_array($result[1], $addresses, true)) {
                            $addresses[] = $result[1];
                        }
                    }
                }
            }
        } else {
            foreach ($urls as $url) {
                if (!@$content = file_get_contents(sprintf($scan, $url))) {
                    continue;
                }

                $crawler = new Crawler($content);
                $crawler = $crawler->filter('#paywall_mask tbody tr a');

                foreach ($crawler as $domElement) {
                    if (!$href = $domElement->attributes->getNamedItem('href')) {
                        continue;
                    }

                    if (!$href->textContent || !str_contains($href->textContent, 'address/')) {
                        continue;
                    }

                    if (!preg_match('(0x[a-fA-F0-9]{40})', $href->textContent, $matches)) {
                        continue;
                    }

                    $address = $matches[0];

                    if (in_array($address, $addresses, true)) {
                        continue;
                    }

                    $addresses[] = $address;

                    if (count($addresses) === 8) {
                        break;
                    }
                }
            }
        }

        $cache->set($addresses)->expiresAfter(60 * 60 * 10);

        $this->cacheItemPool->save($cache);

        return $addresses;
    }
}