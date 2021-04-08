<?php


namespace App\Utils;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DomCrawler\Crawler;

class RandomAddress
{
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    public function getRandomAddresses(): array
    {
        $cache = $this->cacheItemPool->getItem('random-address');

        if ($cache->isHit()) {
            return $cache->get();
        }

        $urls = [
            '0x502AB55Cf22f38c4fd8663dEE041A96B72264c53', // beefy wbnb
            '0x4d0228EBEB39f6d2f29bA528e2d15Fc9121Ead56', // ato cake-bnb
        ];

        $addresses = [];

        foreach($urls as $url) {
            if(!@$content = file_get_contents("https://bscscan.com/txs?a=$url&f=3")) {
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