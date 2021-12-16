<?php declare(strict_types=1);

namespace App\Repository;

class PlatformCronosRepository implements PlatformRepositoryInterface
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
                'id' => 'vvs',
                'label' => 'VVS',
                'url' => 'https://vvs.finance/',
                'icon' => '/assets/platforms/vvs.png',
                'token' => 'vvs',
            ],
            [
                'id' => 'cronaswap',
                'label' => 'Cronaswap',
                'url' => 'https://app.cronaswap.org',
                'icon' => '/assets/platforms/cronaswap.png',
                'token' => 'crona',
            ],
            [
                'id' => 'crodex',
                'label' => 'Crodex',
                'url' => 'https://swap.crodex.app',
                'icon' => '/assets/platforms/crodex.png',
                'token' => 'crx',
            ],
            [
                'id' => 'crokafe',
                'label' => 'Crokafe',
                'url' => 'https://cro.kafe.finance/#/',
                'icon' => '/assets/platforms/kukafe.png',
            ],
            [
                'id' => 'crautofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/cronos',
                'icon' => '/assets/platforms/auto.png',
            ],
            [
                'id' => 'crbeefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/cronos',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
            ],
            [
                'id' => 'crannex',
                'label' => 'Annex',
                'url' => 'https://cronosapp.annex.finance/',
                'icon' => '/assets/platforms/annex.png',
                'token' => 'ann',
            ],
            [
                'id' => 'mmf',
                'label' => 'MMFinance',
                'url' => 'https://mm.finance/?ref=MHg4OThlOTk2ODFDMjk0NzliODYzMDQyOTJiMDMwNzFDODBBNTc5NDhG',
                'icon' => '/assets/platforms/mmf.png',
                'token' => 'mmf',
            ],
        ];
    }
}