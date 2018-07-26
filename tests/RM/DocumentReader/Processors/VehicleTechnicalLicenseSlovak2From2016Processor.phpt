<?php

use Nette\Utils\ArrayHash;
use Nette\Utils\Finder;
use Nette\Utils\Image;
use Nette\Utils\Json;
use RM\DocumentReader\Processors\VehicleTechnicalLicenseSlovak2From2016Processor;
use RM\DocumentReader\Documents\VehicleTechnicalLicenseSlovak2From2016Document;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

$processor = new VehicleTechnicalLicenseSlovak2From2016Processor($cacheStorage, $vision);
$processor->setDebugDir(__DIR__ . '/../../../../temp');

foreach (Finder::findDirectories()->in(__DIR__ . '/../../../assets/VehicleTechnicalLicenseSlovak2From2016') as $path => $dir) {
	$source = [];
	foreach (Finder::findFiles('*.png', '*.jpg')->from($path) as $filePath => $file) {
		$source[] = Image::fromFile($filePath);
	}
	$docParsed = $processor->process($source);

	Assert::true($docParsed instanceof VehicleTechnicalLicenseSlovak2From2016Document);

	Assert::equal(
		ArrayHash::from(Json::decode(file_get_contents($path . '/content.json'), Json::FORCE_ARRAY), TRUE),
		$docParsed->toArray()
	);
}
