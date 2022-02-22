<?php declare(strict_types=1);

namespace App\Repository;

class PlatformCeloRepository implements PlatformRepositoryInterface
{
    public function getPlatform(string $id): array
    {
        foreach($this->getPlatforms() as $platform) {
            if ($platform['id'] === $id) {
                return $platform;
            }
        }

        return [
            'id' => $id,
            'label' => ucfirst($id),
            'icon' => '/assets/platforms/unknown.png',
            'chain' => 'celo',
        ];
    }

    public function getPlatformChunks(): array
    {
        $chunks = array_map(
            fn($p) => $p['id'],
            $this->getPlatforms()
        );

        $slowGroup = [];

        $chunks = array_diff($chunks, $slowGroup);
        // $chunks = array_diff($chunks, $middleGroup);

        $chunks = array_chunk($chunks, 5);

        //$chunks[] = $slowGroup;
        // $chunks[] = $middleGroup;

        return $chunks;
    }

    /**
     * @return \string[][]
     */
    public function getPlatforms(): array
    {
        return [
            [
                'id' => 'ubeswap',
                'label' => 'Ubeswap',
                'url' => 'https://app.ubeswap.org/',
                'icon' => '/assets/platforms/ubeswap.png',
                'token' => 'ube',
                'chain' => 'celo',
            ],
            [
                'id' => 'mobius',
                'label' => 'Mobius',
                'url' => 'https://www.mobius.money/#/farm/',
                'icon' => '/assets/platforms/mobius.png',
                'token' => 'mobi',
                'chain' => 'celo',
            ],
            [
                'id' => 'csushi',
                'label' => 'Sushi',
                'url' => 'https://app.sushi.com/',
                'icon' => '/assets/platforms/sushi.png',
                'token' => 'sushi',
                'chain' => 'celo',
                'group' => 'psushi',
            ],
            [
                'id' => 'moola',
                'label' => 'Moola',
                'url' => 'https://app.moola.market',
                'icon' => '/assets/platforms/moola.png',
                'token' => 'moo',
                'chain' => 'celo',
            ],
            [
                'id' => 'cbeefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/celo',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
                'chain' => 'celo',
                'group' => 'beefy',
            ],
            [
                'id' => 'cautofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/celo/',
                'icon' => '/assets/platforms/auto.png',
                'chain' => 'celo',
                'group' => 'autofarm',
            ],
            [
                'id' => 'celodex',
                'label' => 'Celodex',
                'url' => 'https://www.celodex.org/?referral=2v9mkob4akJHESUqU14ZegxgBh2N',
                'icon' => '/assets/platforms/celodex.png',
                'token' => 'clx',
                'chain' => 'celo',
            ],
        ];
    }
}