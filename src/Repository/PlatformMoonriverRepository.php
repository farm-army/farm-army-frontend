<?php declare(strict_types=1);

namespace App\Repository;

class PlatformMoonriverRepository implements PlatformRepositoryInterface
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
                'id' => 'mautofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/moonriver/',
                'icon' => '/assets/platforms/auto.png',
                'token' => 'fauto',
            ],
            [
                'id' => 'solarbeam',
                'label' => 'Solarbeam',
                'url' => 'https://app.solarbeam.io/farm',
                'icon' => '/assets/platforms/solarbeam.png',
                'token' => 'solar',
            ],
            [
                'id' => 'huckleberry',
                'label' => 'Huckleberry',
                'url' => 'https://www.huckleberry.finance/',
                'icon' => '/assets/platforms/huckleberry.png',
                'token' => 'finn',
            ],
            [
                'id' => 'moonfarm',
                'label' => 'MoonFarm',
                'url' => 'https://v2.moonfarm.in/',
                'icon' => '/assets/platforms/moonfarm.png',
                'token' => 'moon',
            ],
            [
                'id' => 'moonkafe',
                'label' => 'MoonKafe',
                'url' => 'https://moon.kafe.finance/#/',
                'icon' => '/assets/platforms/kukafe.png',
                'token' => 'kafe',
            ],
            [
                'id' => 'mbeefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/moonriver',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
            ],
            [
                'id' => 'msushi',
                'label' => 'Sushi',
                'url' => 'https://app.sushi.com/',
                'icon' => '/assets/platforms/sushi.png',
                'token' => 'sushi',
            ],
        ];
    }
}