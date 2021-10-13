<?php

namespace App\Controller;

use App\Symbol\IconResolver;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class IconController
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @Route("/token/{symbolA}-{symbolB}-{symbolC}-{symbolD}-{symbolE}.{format}", name="token_icon_abcde", methods={"GET"}, requirements={
     *  "format"="png|webp"
     * })
     * @Route("/{chain}/token/{symbolA}-{symbolB}-{symbolC}-{symbolD}-{symbolE}.{format}", name="chain_token_icon_abcde", methods={"GET"}, requirements={
     *  "format"="png|webp"
     * })
     */
    public function icon5(
        string $symbolA,
        string $symbolB,
        string $symbolC,
        string $symbolD,
        string $symbolE,
        string $format,
        ?string $chain,
        IconResolver $iconResolver,
        ImagineInterface $imagine,
        string $projectDir
    ): Response {
        if (strlen($symbolA) > 42 || strlen($symbolB) > 42 || strlen($symbolC) > 42 || strlen($symbolD) > 42 || strlen($symbolE) > 42) {
            throw new NotFoundHttpException('Invalid symbol');
        }

        if (!$files = $iconResolver->getTokenIconForSymbolAddressReverse($symbolA . '-' . $symbolB . '-' . $symbolC . '-' . $symbolD . '-' . $symbolE, $chain)) {
            return new BinaryFileResponse($iconResolver->getLocalImage('unknown' , $chain));
        }

        $imgPath = $projectDir . "/public/token/$symbolA-$symbolB-$symbolC-$symbolD-$symbolE.$format";
        if ($chain) {
            $imgPath = $projectDir . "/public/token/$chain/$symbolA-$symbolB-$symbolC-$symbolD-$symbolE.$format";
            $this->filesystem->mkdir(dirname($imgPath));
        }

        $size = 64;

        $img1 = $imagine->open($files[0])
            ->resize(new Box($size / 2, $size / 2))
            ->crop(new Point(0, 0), new Box($size / 2, $size / 2));

        $img2 = $imagine->open($files[1])
            ->resize(new Box($size / 2, $size / 2))
            ->crop(new Point(0, 0), new Box($size / 2, $size / 2));

        $img3 = $imagine->open($files[2])
            ->resize(new Box($size / 2, $size / 2))
            ->crop(new Point(0, 0), new Box($size / 2, $size / 2));

        $img4 = $imagine->open($files[3])
            ->resize(new Box($size / 2, $size / 2))
            ->crop(new Point(0, 0), new Box($size / 2, $size / 2));

        $img5 = $imagine->open($files[4])
            ->resize(new Box($size / 2, $size / 2))
            ->crop(new Point(0, 0), new Box($size / 2, $size / 2));

        $image = $imagine->create(new Box($size, $size), (new RGB())->color('#FFFFFF', 0));

        $image->paste($img1, new Point(0, 0));
        $image->paste($img2, new Point(0, $size / 2));
        $image->paste($img3, new Point($size / 2, 0));
        $image->paste($img4, new Point($size / 2, $size / 2));
        $image->paste($img5, new Point($size / 4, $size / 4));

        $image->save($imgPath, [
            'quality' => 75,
        ]);

        $response = new BinaryFileResponse($imgPath);

        $response->setPublic();
        $response->setMaxAge(60 * 60 * 24 * 7);

        return $response;
    }

    /**
     * @Route("/token/{symbolA}-{symbolB}-{symbolC}-{symbolD}.{format}", name="token_icon_abcd", methods={"GET"}, requirements={
     *  "format"="png|webp"
     * })
     * @Route("/{chain}/token/{symbolA}-{symbolB}-{symbolC}-{symbolD}.{format}", name="chain_token_icon_abcd", methods={"GET"}, requirements={
     *  "format"="png|webp"
     * })
     */
    public function icon4(
        string $symbolA,
        string $symbolB,
        string $symbolC,
        string $symbolD,
        string $format,
        ?string $chain,
        IconResolver $iconResolver,
        ImagineInterface $imagine,
        string $projectDir
    ): Response {
        if (strlen($symbolA) > 42 || strlen($symbolB) > 42 || strlen($symbolC) > 42 || strlen($symbolD) > 42) {
            throw new NotFoundHttpException('Invalid symbol');
        }

        if (!$files = $iconResolver->getTokenIconForSymbolAddressReverse($symbolA . '-' . $symbolB . '-' . $symbolC . '-' . $symbolD, $chain)) {
            return new BinaryFileResponse($iconResolver->getLocalImage('unknown' , $chain));
        }

        $imgPath = $projectDir . "/public/token/$symbolA-$symbolB-$symbolC-$symbolD.$format";
        if ($chain) {
            $imgPath = $projectDir . "/public/token/$chain/$symbolA-$symbolB-$symbolC-$symbolD.$format";
            $this->filesystem->mkdir(dirname($imgPath));
        }

        $size = 64;

        $img1 = $imagine->open($files[0])
            ->resize(new Box($size / 2, $size / 2))
            ->crop(new Point(0, 0), new Box($size / 2, $size / 2));

        $img2 = $imagine->open($files[1])
            ->resize(new Box($size / 2, $size / 2))
            ->crop(new Point(0, 0), new Box($size / 2, $size / 2));

        $img3 = $imagine->open($files[2])
            ->resize(new Box($size / 2, $size / 2))
            ->crop(new Point(0, 0), new Box($size / 2, $size / 2));

        $img4 = $imagine->open($files[3])
            ->resize(new Box($size / 2, $size / 2))
            ->crop(new Point(0, 0), new Box($size / 2, $size / 2));

        $image = $imagine->create(new Box($size, $size), (new RGB())->color('#FFFFFF', 0));

        $image->paste($img1, new Point(0, 0));
        $image->paste($img2, new Point(0, $size / 2));
        $image->paste($img3, new Point($size / 2, 0));
        $image->paste($img4, new Point($size / 2, $size / 2));

        $image->save($imgPath, [
            'quality' => 75,
        ]);

        $response = new BinaryFileResponse($imgPath);

        $response->setPublic();
        $response->setMaxAge(60 * 60 * 24 * 7);

        return $response;
    }

    /**
     * @Route("/token/{symbolA}-{symbolB}-{symbolC}.{format}", name="token_icon_abc", methods={"GET"}, requirements={
     *  "format"="png|webp"
     * })
     * @Route("/{chain}/token/{symbolA}-{symbolB}-{symbolC}.{format}", name="chain_token_icon_abc", methods={"GET"}, requirements={
     *  "format"="png|webp"
     * })
     */
    public function icon3(
        string $symbolA,
        string $symbolB,
        string $symbolC,
        string $format,
        ?string $chain,
        IconResolver $iconResolver,
        ImagineInterface $imagine,
        string $projectDir
    ): Response {
        if (strlen($symbolA) > 42 || strlen($symbolB) > 42 || strlen($symbolC) > 42) {
            throw new NotFoundHttpException('Invalid symbol');
        }

        if (!$files = $iconResolver->getTokenIconForSymbolAddressReverse($symbolA . '-' . $symbolB . '-' . $symbolC, $chain)) {
            return new BinaryFileResponse($iconResolver->getLocalImage('unknown' , $chain));
        }

        $imgPath = $projectDir . "/public/token/$symbolA-$symbolB-$symbolC.$format";
        if ($chain) {
            $imgPath = $projectDir . "/public/token/$chain/$symbolA-$symbolB-$symbolC.$format";
            $this->filesystem->mkdir(dirname($imgPath));
        }

        $size = 64;

        $img1 = $imagine->open($files[0])
            ->resize(new Box($size / 1.75, $size / 1.75))
            ->crop(new Point(0, 0), new Box($size / 1.75, $size / 1.75));

        $img2 = $imagine->open($files[1])
            ->resize(new Box($size / 1.75, $size / 1.75))
            ->crop(new Point(0, 0), new Box($size / 1.75, $size / 1.75));

        $img3 = $imagine->open($files[2])
            ->resize(new Box($size / 1.75, $size / 1.75))
            ->crop(new Point(0, 0), new Box($size / 1.75, $size / 1.75));

        $image = $imagine->create(new Box($size, $size), (new RGB())->color('#FFFFFF', 0));

        $image->paste($img1, new Point(0, 0));
        $image->paste($img2, new Point(($size / 1.75 / 3), ($size / 1.75 / 3)));
        $image->paste($img3, new Point($size - ($size / 1.75), $size - ($size / 1.75)));

        $image->save($imgPath, [
            'quality' => 75,
        ]);

        $response = new BinaryFileResponse($imgPath);

        $response->setPublic();
        $response->setMaxAge(60 * 60 * 24 * 7);

        return $response;
    }

    /**
     * @Route("/token/{symbolA}-{symbolB}.{format}", name="token_icon_pair", methods={"GET"}, requirements={
     *  "format"="png|webp"
     * })
     * @Route("/{chain}/token/{symbolA}-{symbolB}.{format}", name="chain_token_icon_pair", methods={"GET"}, requirements={
     *  "format"="png|webp"
     * })
     */
    public function iconPair(string $symbolA, string $symbolB, string $format, ?string $chain, IconResolver $iconResolver, ImagineInterface $imagine, string $projectDir): Response
    {
        if (strlen($symbolA) > 42 || strlen($symbolB) > 42) {
            throw new NotFoundHttpException('Invalid symbol');
        }

        if (!$files = $iconResolver->getTokenIconForSymbolAddressReverse($symbolA . '-' . $symbolB, $chain)) {
            return new BinaryFileResponse($iconResolver->getLocalImage('unknown' , $chain));
        }

        $imgPath = $projectDir . "/public/token/$symbolA-$symbolB.$format";
        if ($chain) {
            $imgPath = $projectDir . "/public/token/$chain/$symbolA-$symbolB.$format";
            $this->filesystem->mkdir(dirname($imgPath));
        }

        $size = 64;

        $img1 = $imagine->open($files[0])
            ->resize(new Box($size / 1.5, $size / 1.5))
            ->crop(new Point(0, 0), new Box($size / 1.5, $size / 1.5));

        $img2 = $imagine->open($files[1])
            ->resize(new Box($size / 1.5, $size / 1.5))
            ->crop(new Point(0, 0), new Box($size / 1.5, $size / 1.5));

        $image = $imagine->create(new Box($size, $size), (new RGB())->color('#FFFFFF', 0));

        $image->paste($img1, new Point(0, 0));
        $image->paste($img2, new Point($size / 3, $size / 3));

        $image->save($imgPath, [
            'quality' => 75,
        ]);

        $response = new BinaryFileResponse($imgPath);

        $response->setPublic();
        $response->setMaxAge(60 * 60 * 24 * 7);

        return $response;
    }

    /**
     * @Route("/token/{symbol}.{format}", name="token_icon", methods={"GET"}, requirements={
     *  "format"="png|webp",
     * })
     * @Route("/{chain}/token/{symbol}.{format}", name="chain_token_icon", methods={"GET"}, requirements={
     *  "format"="png|webp",
     * })
     */
    public function icon(string $symbol, string $format, ?string $chain, ImagineInterface $imagine, IconResolver $iconResolver, string $projectDir): Response
    {
        if (strlen($symbol) > 42) {
            throw new NotFoundHttpException('Invalid symbol');
        }

        if (!$files = $iconResolver->getTokenIconForSymbolAddressReverse($symbol, $chain)) {
            return new BinaryFileResponse($iconResolver->getLocalImage('unknown' , $chain));
        }

        $file = $files[0];

        $imgPath = $projectDir . "/public/token/$symbol.$format";
        if ($chain) {
            $imgPath = $projectDir . "/public/token/$chain/$symbol.$format";
            $this->filesystem->mkdir(dirname($imgPath));
        }

        $size = 64;

        $image = $imagine->create(new Box($size, $size), (new RGB())->color('#FFFFFF', 0));

        $image1 = $imagine->open($file)
            ->resize(new Box($size, $size))
            ->crop(new Point(0, 0), new Box($size, $size));

        $image->paste($image1, new Point(0, 0));

        $image->save($imgPath, [
            'quality' => 75,
        ]);

        $response = new BinaryFileResponse($imgPath);

        $response->setPublic();
        $response->setMaxAge(60 * 60 * 24 * 7);

        return $response;
    }
}