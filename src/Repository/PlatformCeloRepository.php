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
            'id' => 'unknown',
            'label' => 'Unknown',
            'icon' => '/assets/platforms/unknown.png',
        ];
    }

    public function getPlatformChunks(): array
    {
        $chunks = array_map(
            fn($p) => $p['id'],
            $this->getPlatforms()
        );

        // $middleGroup = ['pancakebunny'];
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
            ],
            [
                'id' => 'mobius',
                'label' => 'Mobius',
                'url' => 'https://www.mobius.money/#/farm/',
                'icon' => '/assets/platforms/mobius.png',
                'token' => 'mobi',
            ],
            [
                'id' => 'csushi',
                'label' => 'Sushi',
                'url' => 'https://app.sushi.com/',
                'icon' => '/assets/platforms/sushi.png',
                'token' => 'sushi',
            ],
            [
                'id' => 'moola',
                'label' => 'Moola',
                'url' => 'https://app.moola.market',
                'icon' => '/assets/platforms/moola.png',
                'token' => 'moo',
            ],
            [
                'id' => 'cbeefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/celo',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
            ],
            [
                'id' => 'cautofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/celo/',
                'icon' => '/assets/platforms/auto.png',
            ],
        ];
    }
}