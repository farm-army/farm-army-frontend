<?php declare(strict_types=1);

namespace App\Repository;

class PlatformHarmonyRepository implements PlatformRepositoryInterface
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
                'id' => 'hbeefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/harmony',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
            ],
            [
                'id' => 'hsushi',
                'label' => 'Sushi',
                'url' => 'https://app.sushi.com/',
                'icon' => '/assets/platforms/sushi.png',
                'token' => '1sushi',
            ],
            [
                'id' => 'openswap',
                'label' => 'OpenSwap',
                'url' => 'https://app.openswap.one/',
                'icon' => '/assets/platforms/openswap.png',
                'token' => 'openx',
            ],
            [
                'id' => 'viper',
                'label' => 'Viper',
                'url' => 'https://viperswap.one/',
                'icon' => '/assets/platforms/viper.png',
                'token' => 'viper',
            ],
            [
                'id' => 'hcurve',
                'label' => 'Curve',
                'url' => 'https://harmony.curve.fi/',
                'icon' => '/assets/platforms/curve.png',
                'token' => 'curve',
            ],
            [
                'id' => 'artemis',
                'label' => 'Artemis',
                'url' => 'https://app.artemisprotocol.one/',
                'icon' => '/assets/platforms/artemis.png',
                'token' => 'mis',
            ],
            [
                'id' => 'defikingdoms',
                'label' => 'DefiKingdoms',
                'url' => 'https://app.defikingdoms.com/',
                'icon' => '/assets/platforms/defikingdoms.png',
                'token' => 'jewel',
            ],
            [
                'id' => 'farmersonly',
                'label' => 'FarmersOnly',
                'url' => 'https://app.farmersonly.fi/',
                'icon' => '/assets/platforms/farmersonly.png',
                'token' => 'fox',
            ],
            [
                'id' => 'tranquil',
                'label' => 'Tranquil',
                'url' => 'https://app.tranquil.finance/markets',
                'icon' => '/assets/platforms/tranquil.png',
                'token' => 'tranq',
            ],
        ];
    }
}