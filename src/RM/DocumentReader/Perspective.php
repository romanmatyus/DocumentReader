<?php

namespace RM\DocumentReader;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\Image;
use Nette\Utils\Strings;
use Nette\Utils\Random;
use Imagick;

class Perspective
{
	use TTempDir;

	/** @var string */
	private $tempDir;

	/** @var Cache */
	private $cache;

	public function __construct(string $tempDir, IStorage $storage)
	{
		$this->tempDir = $this->getTempDir($tempDir);
		$this->cache = new Cache($storage);
	}

	public function fix(Image $source) : Image
	{
		$imageString = $this->cache->load(md5((string) $source), function (&$dependencies) use ($source) {
			$dependencies = [
				Cache::EXPIRE => '20 minutes',
				Cache::SLIDING => TRUE,
			];

			$tmpFileOriginal = $this->tempDir . '/' . Random::generate() . '-source.png';
			$tmpFileFinal = $this->tempDir . '/' . Random::generate() . '-final.png';

			$source->save($tmpFileOriginal, 100);
			$image = clone $source;
			$image->filter(IMG_FILTER_GRAYSCALE);
			$image->filter(IMG_FILTER_CONTRAST, -100);

			$originalCorners = self::findCorners($image);
			$newCorners = $this->findDestinationCorners($originalCorners);

			$im = new Imagick(realpath($tmpFileOriginal));
			$im->setImageFormat('png');
			$controlPoints = [
				$originalCorners['topLeft'][0], $originalCorners['topLeft'][1],
				$newCorners['topLeft'][0], $newCorners['topLeft'][1],

				$originalCorners['topRight'][0], $originalCorners['topRight'][1],
				$newCorners['topRight'][0], $newCorners['topRight'][1],

				$originalCorners['bottomRight'][0], $originalCorners['bottomRight'][1],
				$newCorners['bottomRight'][0], $newCorners['bottomRight'][1],

				$originalCorners['bottomLeft'][0], $originalCorners['bottomLeft'][1],
				$newCorners['bottomLeft'][0], $newCorners['bottomLeft'][1],
			];

			$im->distortImage(Imagick::DISTORTION_PERSPECTIVE, $controlPoints, true);
			$im->writeImage($tmpFileFinal);

			$out = Image::fromFile($tmpFileFinal);
			unlink($tmpFileOriginal);
			unlink($tmpFileFinal);

			return (string) $out;
		});

		return Image::fromString($imageString);
	}

	private function findCorners(Image $image) :? array
	{
		$out = [];
		$r = $image->getWidth() / 150;
		for ($i=0;$i<min($image->getWidth(), $image->getHeight());$i++) {
			for ($x=$i; $x>=0;$x--) {
				$y = $i - $x;
				$rgb = $image->colorAt($x, $y);
				if ($rgb !== 0) {
					$out['topLeft'] = [$x, $y];
					$image->setPixel($x, $y, $image->colorAllocate(255, 0, 0));
					break 2;
				}
			}
		}

		for ($i=$image->getWidth()-1;$i>0;$i--) {
			for ($x=$i;$x<=$image->getWidth()-1;$x++) {
				$y = $x - $i;
				$rgb = $image->colorAt($x, $y);
				if ($rgb !== 0) {
					$hasNotBlack = TRUE;
					for ($a=280;$a<=350;$a++) {
						$radX = $x + sin(deg2rad($a)) * $r;
						$radY = $y + cos(deg2rad($a)) * $r;
						if ($image->colorAt($radX, $radY) === 0) {
							$hasNotBlack = FALSE;
							break;
						}
					}
					if ($hasNotBlack) {
						$out['topRight'] = [$x, $y];
						$image->setPixel($x, $y, $image->colorAllocate(0, 255, 0));
						break 2;
					}
				}
			}
		}

		$tested = [];
		$testR = $image->getWidth() / 150;
		for ($r=0;$r<=min($image->getWidth() - 1, $image->getHeight() -1);$r++) {
			for ($a=90;$a<=180;$a++) {
				$radX = (int) (0 + sin(deg2rad($a)) * $r);
				$radY = (int) ($image->getHeight() - 1 + cos(deg2rad($a)) * $r);
				if (in_array($radX . 'x' . $radY, $tested))
					continue;
				$tested[] = $radX . 'x' . $radY;
				if ($image->colorAt($radX, $radY) !== 0) {
					$x = $radX;
					$y = $radY;
					$hasNotBlack = TRUE;
					for ($a2=100;$a2<=170;$a2++) {
						$radX = (int) ($x + sin(deg2rad($a2)) * $testR);
						$radY = (int) ($y + cos(deg2rad($a2)) * $testR);
						if ($image->colorAt($radX, $radY) === 0) {
							$hasNotBlack = FALSE;
							break;
						}
					}
					if ($hasNotBlack) {
						$out['bottomLeft'] = [$x, $y];
						$image->setPixel($x, $y, $image->colorAllocate(0, 0, 255));
						break 2;
					}
				}
			}
		}

		$tested = [];
		for ($r=0;$r<=min($image->getWidth() - 1, $image->getHeight() -1);$r++) {
			for ($a=180;$a<=270;$a++) {
				$radX = (int) ($image->getWidth() -1 + sin(deg2rad($a)) * $r);
				$radY = (int) ($image->getHeight() - 1 + cos(deg2rad($a)) * $r);
				if (in_array($radX . 'x' . $radY, $tested))
					continue;
				$tested[] = $radX . 'x' . $radY;
				if ($image->colorAt($radX, $radY) !== 0) {
					$x = $radX;
					$y = $radY;
					$hasNotBlack = TRUE;
					for ($a2=190;$a2<=260;$a2++) {
						$radX = (int) ($x + sin(deg2rad($a2)) * $testR);
						$radY = (int) ($y + cos(deg2rad($a2)) * $testR);
						if ($image->colorAt($radX, $radY) === 0) {
							$hasNotBlack = FALSE;
							break;
						}
					}
					if ($hasNotBlack) {
						$out['bottomRight'] = [$x, $y];
						$image->setPixel($x, $y, $image->colorAllocate(0, 255, 255));
						break 2;
					}
				}
			}
		}

		return $out;
	}

	private function findDestinationCorners(array $originalCorners) :? array
	{
		$out['topLeft'] = $originalCorners['topLeft'];

		$width = (int) max(
			sqrt(abs(pow($originalCorners['topRight'][0] - $originalCorners['topLeft'][0], 2)) + abs(pow($originalCorners['topRight'][1] - $originalCorners['topLeft'][1], 2))),
			sqrt(abs(pow($originalCorners['bottomRight'][0] - $originalCorners['bottomLeft'][0], 2)) + abs(pow($originalCorners['bottomRight'][1] - $originalCorners['bottomLeft'][1], 2)))
		);

		$height = (int) max(
			sqrt(abs(pow($originalCorners['bottomLeft'][1] - $originalCorners['topLeft'][1], 2)) + abs(pow($originalCorners['bottomLeft'][0] - $originalCorners['topLeft'][0], 2))),
			sqrt(abs(pow($originalCorners['bottomRight'][1] - $originalCorners['topRight'][1], 2)) + abs(pow($originalCorners['bottomRight'][0] - $originalCorners['topRight'][0], 2)))
		);

		$out['topRight'] = [
			$out['topLeft'][0] + $width,
			$out['topLeft'][1]
		];
		$out['bottomLeft'] = [
			$out['topLeft'][0],
			$out['topLeft'][1] + $height,
		];
		$out['bottomRight'] = [
			$out['topLeft'][0] + $width,
			$out['topLeft'][1] + $height,
		];

		return $out;
	}
}
