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
            'id' => $id,
            'label' => ucfirst($id),
            'icon' => '/assets/platforms/unknown.png',
            'chain' => 'moonriver',
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
                'id' => 'mautofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/moonriver/',
                'icon' => '/assets/platforms/auto.png',
                'token' => 'fauto',
                'chain' => 'moonriver',
                'group' => 'autofarm',
            ],
            [
                'id' => 'solarbeam',
                'label' => 'Solarbeam',
                'url' => 'https://app.solarbeam.io/farm',
                'icon' => '/assets/platforms/solarbeam.png',
                'token' => 'solar',
                'chain' => 'moonriver',
            ],
            [
                'id' => 'huckleberry',
                'label' => 'Huckleberry',
                'url' => 'https://www.huckleberry.finance/',
                'icon' => '/assets/platforms/huckleberry.png',
                'token' => 'finn',
                'chain' => 'moonriver',
            ],
            [
                'id' => 'moonfarm',
                'label' => 'MoonFarm',
                'url' => 'https://v2.moonfarm.in/',
                'icon' => '/assets/platforms/moonfarm.png',
                'token' => 'moon',
                'chain' => 'moonriver',
            ],
            [
                'id' => 'moonkafe',
                'label' => 'MoonKafe',
                'url' => 'https://moon.kafe.finance/#/',
                'icon' => '/assets/platforms/kukafe.png',
                'token' => 'kafe',
                'chain' => 'moonriver',
            ],
            [
                'id' => 'mbeefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/moonriver',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
                'chain' => 'moonriver',
                'group' => 'beefy',
            ],
            [
                'id' => 'msushi',
                'label' => 'Sushi',
                'url' => 'https://app.sushi.com/',
                'icon' => '/assets/platforms/sushi.png',
                'token' => 'sushi',
                'chain' => 'moonriver',
                'group' => 'psushi',
            ],
            [
                'id' => 'mtemplar',
                'label' => 'Templar',
                'url' => 'https://templar.finance/#/bonds',
                'icon' => '/assets/platforms/templar.png',
                'token' => 'tem',
                'chain' => 'moonriver',
                'group' => 'templar',
            ],
        ];
    }
}