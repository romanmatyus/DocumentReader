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
		@mkdir($tempDir . '/' . Strings::webalize(__CLASS__));
		return $tempDir . '/' . Strings::webalize(__CLASS__);
	}
}
