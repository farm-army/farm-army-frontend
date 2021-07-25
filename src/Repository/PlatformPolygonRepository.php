<?php declare(strict_types=1);

namespace App\Repository;

class PlatformPolygonRepository implements PlatformRepositoryInterface
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
                'id' => 'pwault',
                'label' => 'Wault Finance',
                'url' => 'https://app.wault.finance/',
                'icon' => '/assets/platforms/wault.png',
                'token' => 'wexpoly',
            ],
            [
                'id' => 'polyzap',
                'label' => 'Polyzap',
                'url' => 'https://farm.polyzap.finance?ref=0x898e99681C29479b86304292b03071C80A57948F',
                'icon' => '/assets/platforms/polyzap.png',
                'token' => 'pzap',
            ],
            [
                'id' => 'polycat',
                'label' => 'Polycat',
                'url' => 'https://polycat.finance/?ref=0k898r99681P29479o86304292o03071P80N57948S',
                'icon' => '/assets/platforms/polycat.png',
                'token' => 'fish',
            ],
            [
                'id' => 'pjetswap',
                'label' => 'JetSwap',
                'url' => 'https://polygon.jetswap.finance/',
                'icon' => '/assets/platforms/jetswap.png',
                'token' => 'pwings',
            ],
            [
                'id' => 'augury',
                'label' => 'Augury',
                'url' => 'https://augury.finance/',
                'icon' => '/assets/platforms/augury.png',
                'token' => 'omen',
            ],
            [
                'id' => 'pswamp',
                'label' => 'Swamp',
                'url' => 'https://swamp.finance/polygon/',
                'icon' => '/assets/platforms/swamp.png',
                'token' => 'pswamp',
            ],
            [
                'id' => 'ppancakebunny',
                'label' => 'Bunny',
                'url' => 'https://polygon.pancakebunny.finance/',
                'icon' => '/assets/platforms/pancakebunny.png',
                'token' => 'polybunny',
            ],
            [
                'id' => 'mai',
                'label' => 'Mai.finance',
                'url' => 'https://app.mai.finance/farm',
                'icon' => '/assets/platforms/mai.png',
                'token' => 'qi',
            ],
            [
                'id' => 'pfarmhero',
                'label' => 'FarmHero',
                'url' => 'https://polygon.farmhero.io/',
                'icon' => '/assets/platforms/farmhero.png',
                'token' => 'honor',
            ],
            [
                'id' => 'polycrystal',
                'label' => 'PolyCrystal',
                'url' => 'https://polycrystal.finance/',
                'icon' => '/assets/platforms/polycrystal.png',
                'token' => 'crystl',
            ],
            [
                'id' => 'mstable',
                'label' => 'mStable',
                'url' => 'https://app.mstable.org/#/musd/save',
                'icon' => '/assets/platforms/mstable.png',
                'token' => 'mta',
            ],
            [
                'id' => 'pbeefy',
                'label' => 'Beefy',
                'url' => 'https://polygon.beefy.finance/',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
            ],
            [
                'id' => 'dinoswap',
                'label' => 'DinoSwap',
                'url' => 'https://dinoswap.exchange/',
                'icon' => '/assets/platforms/dinoswap.png',
                'token' => 'dino',
            ],
            [
                'id' => 'pautofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/polygon',
                'icon' => '/assets/platforms/auto.png',
                'token' => 'pauto',
            ],
        ];
    }
}