<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\CrossFarm;
use App\Form\FarmSearchType;
use App\Pools\FarmPools;
use App\Repository\CrossFarmRepository;
use App\Repository\CrossPlatformRepository;
use App\Utils\ChainUtil;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FarmSearchController extends AbstractController
{
    private CrossPlatformRepository $crossPlatformRepository;
    private CrossFarmRepository $crossFarmRepository;
    private FarmPools $farmPools;

    public function __construct(
        CrossPlatformRepository $crossPlatformRepository,
        CrossFarmRepository $crossFarmRepository,
        FarmPools $farmPools
    ) {
        $this->crossPlatformRepository = $crossPlatformRepository;
        $this->crossFarmRepository = $crossFarmRepository;
        $this->farmPools = $farmPools;
    }

    /**
     * @Route("/farm-pools", name="pools", methods={"GET", "POST"})
     */
    public function getConfig(Request $request, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(FarmSearchType::class, null, [
            'providers' => array_values($this->getPlatformMap()),
            'chains' => ChainUtil::getChains(),
        ]);

        $platforms = [];
        $chains = [];
        $query = null;
        $tags = [];
        $page = 1;
        $sort = 'tvl_desc';

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $platforms = array_map(static fn(\ArrayObject $i) => $i['id'], $data['providers'] ?? []);
            $chains = array_map(static fn(\ArrayObject $i) => $i['id'], $data['chains'] ?? []);
            $query = $data['query'] ?? null;
            $tags = $data['tags'] ?? [];
            $page = (int)($data['page'] ?? 1);
            $sort = $data['sort'] ?? null;

            $providers = array_values($this->getPlatformMap());

            if (count($chains) > 0) {
                $providers = array_values(array_filter($providers, static function (array $i) use ($chains) {
                    foreach ($chains as $chain) {
                        if (in_array($chain, $i['chains'], true)) {
                            return true;
                        }
                    }

                    return false;
                }));
            }

            $form = $this->createForm(FarmSearchType::class, null, [
                'providers' => $providers,
                'chains' => ChainUtil::getChains(),
            ]);

            $form->handleRequest($request);
        }

        if ($sort === null) {
            $sort = 'tvl_desc';
        }

        $platformIds = $this->createSelectedPlatforms($platforms);

        $query = $this->crossFarmRepository->search($platformIds, $chains, $query, $tags, $page, $sort);

        $pagination = $paginator->paginate($query, $page,50);

        $crossNew = array_map(static fn(CrossFarm $f) => $f->getJson(), $pagination->getItems());

        $farms = $this->farmPools->renderFarms($crossNew, 'components/farms_mini.html.twig', [
            'cross_chain' => true,
        ]);

        return $this->render('search/index.html.twig', [
            'form' => $form->createView(),
            'farms' => $farms,
            'pagination' => $pagination,
            'sort' => $sort,
        ]);
    }

    private function getPlatformMap(): array
    {
        $platforms = $this->crossPlatformRepository->getPlatforms();

        $result = [];
        foreach ($platforms as $platform) {
            if (!isset($platform['group'])) {
                $result[$platform['id']] = $platform;
                $result[$platform['id']]['chains'] = [$result[$platform['id']]['chain']];
                continue;
            }

            if (!isset($result[$platform['group']])) {
                $result[$platform['group']] = $this->crossPlatformRepository->getPlatform($platform['group']);
                $result[$platform['group']]['children'] = [];
                $result[$platform['group']]['chains'] = [$result[$platform['group']]['chain']];
            }

            if ($platform['id'] !== $platform['group']) {
                $result[$platform['group']]['children'][] = $platform['id'];
                $result[$platform['group']]['chains'][] = $platform['chain'];
            }
        }

        return $result;
    }

    private function createSelectedPlatforms(array $platforms): array
    {
        $x = $this->getPlatformMap();

        $xxx = [];

        foreach ($platforms as $ps) {
            $xxx[] = $ps;

            if (isset($x[$ps]['children'])) {
                $xxx = array_merge($xxx, $x[$ps]['children']);
            }
        }

        return $xxx;
    }
}