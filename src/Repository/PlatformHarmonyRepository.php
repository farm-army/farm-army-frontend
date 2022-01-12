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
            'id' => $id,
            'label' => ucfirst($id),
            'icon' => '/assets/platforms/unknown.png',
            'chain' => 'harmony',
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
                'chain' => 'harmony',
            ],
            [
                'id' => 'hsushi',
                'label' => 'Sushi',
                'url' => 'https://app.sushi.com/',
                'icon' => '/assets/platforms/sushi.png',
                'token' => '1sushi',
                'chain' => 'harmony',
            ],
            [
                'id' => 'openswap',
                'label' => 'OpenSwap',
                'url' => 'https://app.openswap.one/',
                'icon' => '/assets/platforms/openswap.png',
                'token' => 'openx',
                'chain' => 'harmony',
            ],
            [
                'id' => 'viper',
                'label' => 'Viper',
                'url' => 'https://viperswap.one/',
                'icon' => '/assets/platforms/viper.png',
                'token' => 'viper',
                'chain' => 'harmony',
            ],
            [
                'id' => 'hcurve',
                'label' => 'Curve',
                'url' => 'https://harmony.curve.fi/',
                'icon' => '/assets/platforms/curve.png',
                'token' => 'curve',
                'chain' => 'harmony',
            ],
            [
                'id' => 'artemis',
                'label' => 'Artemis',
                'url' => 'https://app.artemisprotocol.one/',
                'icon' => '/assets/platforms/artemis.png',
                'token' => 'mis',
                'chain' => 'harmony',
            ],
            [
                'id' => 'defikingdoms',
                'label' => 'DefiKingdoms',
                'url' => 'https://app.defikingdoms.com/',
                'icon' => '/assets/platforms/defikingdoms.png',
                'token' => 'jewel',
                'chain' => 'harmony',
            ],
            [
                'id' => 'farmersonly',
                'label' => 'FarmersOnly',
                'url' => 'https://app.farmersonly.fi/',
                'icon' => '/assets/platforms/farmersonly.png',
                'token' => 'fox',
                'chain' => 'harmony',
            ],
            [
                'id' => 'tranquil',
                'label' => 'Tranquil',
                'url' => 'https://app.tranquil.finance/markets',
                'icon' => '/assets/platforms/tranquil.png',
                'token' => 'tranq',
                'chain' => 'harmony',
            ],
            [
                'id' => 'hsynapse',
                'label' => 'Synapse',
                'url' => 'https://synapseprotocol.com/pools',
                'icon' => '/assets/platforms/synapse.png',
                'token' => 'syn',
                'chain' => 'harmony',
            ],
            [
                'id' => 'hautofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/harmony',
                'icon' => '/assets/platforms/auto.png',
                'token' => 'auto',
                'chain' => 'harmony',
            ],
            [
                'id' => 'euphoria',
                'label' => 'Euphoria',
                'url' => 'https://app.euphoria.money',
                'icon' => '/assets/platforms/euphoria.png',
                'token' => 'wagmi',
                'chain' => 'harmony',
            ],
            [
                'id' => 'hundred',
                'label' => 'Hundred',
                'url' => 'https://hundred.finance',
                'icon' => '/assets/platforms/hundred.png',
                'token' => 'hnd',
                'chain' => 'harmony',
            ],
            [
                'id' => 'lootswap',
                'label' => 'Lootswap',
                'url' => 'https://lootswap.finance',
                'icon' => '/assets/platforms/lootswap.png',
                'token' => 'loot',
                'chain' => 'harmony',
            ],
        ];
    }
}