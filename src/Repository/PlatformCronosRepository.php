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
            'id' => $id,
            'label' => ucfirst($id),
            'icon' => '/assets/platforms/unknown.png',
            'chain' => 'cronos',
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
                'id' => 'vvs',
                'label' => 'VVS',
                'url' => 'https://vvs.finance/',
                'icon' => '/assets/platforms/vvs.png',
                'token' => 'vvs',
                'chain' => 'cronos',
            ],
            [
                'id' => 'cronaswap',
                'label' => 'Cronaswap',
                'url' => 'https://app.cronaswap.org',
                'icon' => '/assets/platforms/cronaswap.png',
                'token' => 'crona',
                'chain' => 'cronos',
            ],
            [
                'id' => 'crodex',
                'label' => 'Crodex',
                'url' => 'https://swap.crodex.app',
                'icon' => '/assets/platforms/crodex.png',
                'token' => 'crx',
                'chain' => 'cronos',
            ],
            [
                'id' => 'crokafe',
                'label' => 'Crokafe',
                'url' => 'https://cro.kafe.finance/#/',
                'icon' => '/assets/platforms/kukafe.png',
                'chain' => 'cronos',
            ],
            [
                'id' => 'crautofarm',
                'label' => 'Autofarm',
                'url' => 'https://autofarm.network/cronos',
                'icon' => '/assets/platforms/auto.png',
                'chain' => 'cronos',
                'group' => 'autofarm',
            ],
            [
                'id' => 'crbeefy',
                'label' => 'Beefy',
                'url' => 'https://app.beefy.finance/#/cronos',
                'icon' => '/assets/platforms/beefy.png',
                'token' => 'bifi',
                'chain' => 'cronos',
                'group' => 'beefy',
            ],
            [
                'id' => 'crannex',
                'label' => 'Annex',
                'url' => 'https://cronosapp.annex.finance/',
                'icon' => '/assets/platforms/annex.png',
                'token' => 'ann',
                'chain' => 'cronos',
                'group' => 'annex',
            ],
            [
                'id' => 'mmf',
                'label' => 'MMFinance',
                'url' => 'https://mm.finance/?ref=MHg4OThlOTk2ODFDMjk0NzliODYzMDQyOTJiMDMwNzFDODBBNTc5NDhG',
                'icon' => '/assets/platforms/mmf.png',
                'token' => 'mmf',
                'chain' => 'cronos',
            ],
            [
                'id' => 'tectonic',
                'label' => 'Tectonic',
                'url' => 'https://app.tectonic.finance/markets/',
                'icon' => '/assets/platforms/tectonic.png',
                'token' => 'tonic',
                'chain' => 'cronos',
            ],
            [
                'id' => 'mmo',
                'label' => 'Mmo',
                'url' => 'https://vaults.mm.finance',
                'icon' => '/assets/platforms/mmo.png',
                'token' => 'mmo',
                'chain' => 'cronos',
            ],
        ];
    }
}