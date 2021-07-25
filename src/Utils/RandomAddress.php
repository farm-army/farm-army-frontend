<?php


namespace App\Utils;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DomCrawler\Crawler;

class RandomAddress
{
    private CacheItemPoolInterface $cacheItemPool;
    private string $chain;

    public function __construct(CacheItemPoolInterface $cacheItemPool, string $chain)
    {
        $this->cacheItemPool = $cacheItemPool;
        $this->chain = $chain;
    }

    public function getRandomAddresses(): array
    {
        $cache = $this->cacheItemPool->getItem('random-address');

        if ($cache->isHit()) {
            return $cache->get();
        }

        if ($this->chain === 'bsc') {
            $urls = [
                '0x502AB55Cf22f38c4fd8663dEE041A96B72264c53', // beefy wbnb
                '0x4d0228EBEB39f6d2f29bA528e2d15Fc9121Ead56', // ato cake-bnb
            ];

            $scan = 'https://bscscan.com/txs?a=%s&f=3';
        } else if ($this->chain === 'polygon') {
            $urls = [
                '0xC8Bd86E5a132Ac0bf10134e270De06A8Ba317BFe', // pwault
                '0x8CFD1B9B7478E7B0422916B72d1DB6A9D513D734', // polycan
            ];

            $scan = 'https://polygonscan.com/txs?a=%s&f=3';
        } else if ($this->chain === 'fantom') {
            $urls = [
                '0x9083EA3756BDE6Ee6f27a6e996806FBD37F6F093', // spiritswap
                '0x2b2929e785374c651a81a63878ab22742656dcdd', // spookyswap
            ];

            $scan = 'https://polygonscan.com/txs?a=%s&f=3';
        } else {
            throw new \RuntimeException('Invalid chain');
        }

        $addresses = [];

        foreach($urls as $url) {
            if(!@$content = file_get_contents(sprintf($scan, $url))) {
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

        $cache->set($addresses)->expiresAfter(60 * 60 * 5);

        $this->cacheItemPool->save($cache);

        return $addresses;
    }
}