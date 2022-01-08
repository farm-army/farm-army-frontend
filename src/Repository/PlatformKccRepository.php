<?php declare(strict_types=1);

namespace App\Repository;

class PlatformKccRepository implements PlatformRepositoryInterface
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
            'chain' => 'kcc',
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
                'id' => 'kuswap',
                'label' => 'KuSwap',
                'url' => 'https://kuswap.finance/',
                'icon' => '/assets/platforms/kuswap.png',
                'token' => 'kus',
                'chain' => 'kcc',
            ],
            [
                'id' => 'kudex',
                'label' => 'Kudex',
                'url' => 'https://kudex.finance/',
                'icon' => '/assets/platforms/kudex.png',
                'token' => 'kud',
                'chain' => 'kcc',
            ],
            [
                'id' => 'kukafe',
                'label' => 'Kukafe',
                'url' => 'https://kukafe.finance/',
                'icon' => '/assets/platforms/kukafe.png',
                'token' => 'kafe',
                'chain' => 'kcc',
            ],
            [
                'id' => 'boneswap',
                'label' => 'BoneSwap',
                'url' => 'https://farm-kcc.boneswap.finance/?ref=0k898r99681P29479o86304292o03071P80N57948S',
                'icon' => '/assets/platforms/boneswap.png',
                'token' => 'bone',
                'chain' => 'kcc',
            ],
            [
                'id' => 'mojito',
                'label' => 'Mojito',
                'url' => 'https://app.mojitoswap.finance/referral?code=5CD2F29F',
                'icon' => '/assets/platforms/mojito.png',
                'token' => 'mjt',
                'chain' => 'kcc',
            ],
        ];
    }
}