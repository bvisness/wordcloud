<?php

namespace WordCloud\Service;

use Equip\Env;
use WordCloud\Model\Rectangle;

class CloudMaker
{
    private $env;

    const SPIRAL_STEPS = 2000;

    public function __construct(Env $env)
    {
        $this->env = $env;
    }

    public function makeCloud($width, $height, $outputPath, array $sortedWords, $debug = false)
    {
        $tmpDirectoryPath = dirname(dirname(__DIR__)) . '/tmp/';
        if (!is_dir($tmpDirectoryPath)) {
            mkdir($tmpDirectoryPath);
        }

        $fontUrl = $this->env['STATIC_HOSTING_URL'] . 'fonts/' . $this->env['FONT_FILE'];
        $fontfile = $tmpDirectoryPath . uniqid();
        file_put_contents($fontfile, fopen($fontUrl, 'r'));

        // Calculate bounding boxes for words and sum up area
        $rectangles = [];
        $area = 0;
        foreach ($sortedWords as $word => $count) {
            $bbox = imagettfbbox($count, 0, $fontfile, $word);
            if ($bbox === false) {
                throw new \Exception("Error getting bounding box for string `$word` with fontfile `$fontfile`.");
            }

            $rectangles[$word] = new Rectangle(
                $bbox[0],
                $bbox[1],
                $bbox[4],
                -$bbox[5]
            );

            $area += $rectangles[$word]->area();
        }

        // Calculate playing field
        $pfAspect = $width / $height;
        $pfWidth = sqrt($area * $pfAspect);
        $pfHeight = $area / $pfWidth;
        $playingField = new Rectangle(0, 0, $pfWidth, $pfHeight);

        // Place rectangles into the cloud
        $placedRectangles = [];
        foreach ($rectangles as $word => $rectangle) {
            $posX = $this->randFloatMinMax(0, $playingField->width() - $rectangle->width()) - $rectangle->x1;
            $posY = $this->randFloatMinMax(0, $playingField->height() - $rectangle->height()) - $rectangle->y1;

            $placed = $rectangle->translated($posX, $posY);
            $originalPlaced = clone $placed;

            $loopCount = 0;
            while ($this->rectIntersects($placed, $placedRectangles)) {
                do {
                    $t = $loopCount / self::SPIRAL_STEPS;
                    list($spiralX, $spiralY) = $this->spiralOffset($t, 20, 20 * $sortedWords[$word]);

                    $placed = $originalPlaced->translated($spiralX, $spiralY);
                    $loopCount++;
                } while ($loopCount < self::SPIRAL_STEPS && !$playingField->contains($placed));
            }

            $placedRectangles[$word] = $placed;
        }

        // Find the min and max coordinates of placed rectangles
        $minX = current($placedRectangles)->x1;
        $minY = current($placedRectangles)->y1;
        $maxX = current($placedRectangles)->x2;
        $maxY = current($placedRectangles)->y2;
        foreach ($placedRectangles as $placedRectangle) {
            if ($placedRectangle->x1 < $minX) {
                $minX = $placedRectangle->x1;
            }
            if ($placedRectangle->y1 < $minY) {
                $minY = $placedRectangle->y1;
            }
            if ($placedRectangle->x2 > $maxX) {
                $maxX = $placedRectangle->x2;
            }
            if ($placedRectangle->y2 > $maxY) {
                $maxY = $placedRectangle->y2;
            }
        }

        // Get zoom factor to fit all text into playing field
        $widthZoomFactor = $playingField->width() / ($maxX - $minX);
        $heightZoomFactor = $playingField->height() / ($maxY - $minY);
        $textZoomFactor = min($widthZoomFactor, $heightZoomFactor);

        // Make the image!
        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        $gray = imagecolorallocate($image, 128, 128, 128);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $white);

        $scaleFactor = $textZoomFactor * ($width / $pfWidth);

        foreach ($placedRectangles as $word => $placed) {
            imagettftext(
                $image,
                $sortedWords[$word] * $scaleFactor, // size
                0, // angle
                ($placed->x1 - $minX) * $scaleFactor, // x
                ($placed->y2 - $rectangles[$word]->y1 - $minY) * $scaleFactor, // y
                $black, // color
                $fontfile, // fontfile
                $word // text
            );
            if ($debug) {
                imagerectangle(
                    $image,
                    ($placed->x1 - $minX) * $scaleFactor,
                    ($placed->y1 - $minY) * $scaleFactor,
                    ($placed->x2 - $minX) * $scaleFactor,
                    ($placed->y2 - $minY) * $scaleFactor,
                    $gray
                );
            }
        }
        if ($debug) {
            imagerectangle(
                $image,
                ($playingField->x1 - $minX) * $scaleFactor,
                ($playingField->y1 - $minY) * $scaleFactor,
                ($playingField->x2 - $minX) * $scaleFactor,
                ($playingField->y2 - $minY) * $scaleFactor,
                $black
            );
        }

        // Output the image
        if (!is_dir($outputPath)) {
            mkdir($outputPath);
        }

        $imageId = uniqid();
        $imageUrl = "$outputPath/$imageId.png";
        imagepng($image, $imageUrl);
        imagedestroy($image);

        // Delete the font
        unlink($fontfile);

        return $imageUrl;
    }

    private function randFloat()
    {
        return (float)mt_rand() / (float)mt_getrandmax();
    }

    private function randFloatMinMax($min, $max)
    {
        return $this->randFloat() * ($max - $min);
    }

    private function rectIntersects($rectangle, $rectangles)
    {
        foreach ($rectangles as $otherRectangle) {
            if ($rectangle === $otherRectangle) {
                continue;
            }

            if ($rectangle->intersects($otherRectangle)) {
                return true;
            }
        }

        return false;
    }

    private function spiralOffset($t, $revolutions, $radius)
    {
        $distance = $radius * $t;
        $angle = 2 * M_PI * $revolutions * $t;

        return [$distance * cos($angle), $distance * sin($angle)];
    }
}
