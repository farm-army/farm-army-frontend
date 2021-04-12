<?php

namespace App\Controller;

use App\Symbol\IconResolver;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IconController
{
    /**
     * @Route("/token/{symbolA}-{symbolB}.{format}", name="token_icon_pair", methods={"GET"}, requirements={
     *  "format"="png|webp"
     * })
     */
    public function iconPair(string $symbolA, string $symbolB, string $format, IconResolver $iconResolver, ImagineInterface $imagine, string $projectDir): Response
    {
        if (!($fileA = $iconResolver->getLocalImage($symbolA)) || !$fileB = $iconResolver->getLocalImage($symbolB)) {
            return new BinaryFileResponse($iconResolver->getLocalImage('unknown'));
        }

        $imgPath = $projectDir . "/public/token/$symbolA-$symbolB.$format";

        $size = 64;

        $img1 = $imagine->open($fileA)
            ->resize(new Box($size / 1.5, $size / 1.5))
            ->crop(new Point(0, 0), new Box($size / 1.5, $size / 1.5));

        $img2 = $imagine->open($fileB)
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
     */
    public function icon(string $symbol, string $format, ImagineInterface $imagine, IconResolver $iconResolver, string $projectDir): Response
    {
        if (!$file = $iconResolver->getLocalImage($symbol)) {
            return new BinaryFileResponse($iconResolver->getLocalImage('unknown'));
        }

        $imgPath = $projectDir . "/public/token/$symbol.$format";
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