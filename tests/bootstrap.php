<?php

use Google\Cloud\Vision\VisionClient;
use Nette\Caching\Storages\FileStorage;
use Nette\Neon\Neon;

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}

$config = Neon::decode(file_get_contents(__DIR__ . '/secret.neon'));

$tempDir = __DIR__ . '/../temp';
@mkdir($tempDir);

$cacheStorage = new FileStorage($tempDir);

$vision = new VisionClient([
	'projectId' => $config['visionClient']['projectId'],
	'keyFilePath' => __DIR__ . '/secret-google-owner.json',
]);

Tester\Environment::setup();
