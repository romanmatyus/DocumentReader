<?php

use Tester\Assert;
use Nette\Utils\Image;

require __DIR__ . '/../../bootstrap.php';

$perspective = new RM\DocumentReader\Perspective($tempDir, $cacheStorage);

$original = Image::fromFile(__DIR__ . '/../../assets/perspective-original.png');
$final = $perspective->fix($original);

Assert::true($final instanceof Image);

$final->save($tempDir . '/perspective-final.png');
