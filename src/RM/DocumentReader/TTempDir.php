<?php

namespace RM\DocumentReader;

use Nette\Utils\Strings;

trait TTempDir
{
	public function getTempDir(string $tempDir)
	{
		if (!file_exists($tempDir)) {
			throw new InvalidArgumentException("Temporary directory '$tempDir' not exists");
		} elseif (!is_dir($tempDir)) {
			throw new InvalidArgumentException("Temporary directory '$tempDir' is not direcotry.");
		} elseif (!is_writable($tempDir)) {
			throw new InvalidArgumentException("Temporary directory '$tempDir' is not writable.");
		}
		$dir = $tempDir . '/' . Strings::webalize(__CLASS__);
		if (!file_exists($dir)) {
			mkdir($dir);
		}
		return $dir;
	}
}
