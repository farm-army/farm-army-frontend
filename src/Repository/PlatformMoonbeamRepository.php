<?php declare(strict_types=1);

namespace App\Repository;

class PlatformMoonbeamRepository implements PlatformRepositoryInterface
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
            'chain' => 'moonbeam',
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

        $chunks = array_chunk($chunks, 5);

        return $chunks;
    }

    /**
     * @return \string[][]
     */
    public function getPlatforms(): array
    {
        return [
            [
                'id' => 'stellaswap',
                'label' => 'StellaSwap',
                'url' => 'https://app.stellaswap.com',
                'icon' => '/assets/platforms/stellaswap.png',
                'token' => 'stella',
                'chain' => 'moonbeam',
            ],
            [
                'id' => 'solarflare',
                'label' => 'solarflare',
                'url' => 'https://app.solarflare.io',
                'icon' => '/assets/platforms/solarflare.png',
                'token' => 'flare',
                'chain' => 'moonbeam',
            ],
            [
                'id' => 'mbbeefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/moonbeam',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
                'chain' => 'moonbeam',
                'group' => 'beefy',
            ],
            [
                'id' => 'thorus',
                'label' => 'Thorus',
                'url' => 'https://app.thorus.fi',
                'icon' => '/assets/platforms/thorus.png',
                'token' => 'tho',
                'chain' => 'moonbeam',
            ],
        ];
    }
}