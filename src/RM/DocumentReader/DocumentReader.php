<?php

namespace RM\DocumentReader;

use Imagick;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\ICacheStorage;
use Nette\Utils\Image;


class DocumentReader
{
	use TTempDir;

	/** @var string */
	private $tempDir;

	/** @var [] of Image */
	private $sources;

	/** @var Cache */
	private $cache;

	/** @var Perspective */
	private $perspective;

	/** @var [] of IProcessor */
	private $processors;


	public function __construct(string $tempDir, ICacheStorage $cacheStorage, Perspective $perspective)
	{
		$this->tempDir = $this->getTempDir($tempDir);
		$this->cache = new Cache($cacheStorage);
		$this->perspective = $perspective;
	}

	public function addProcessor(IProcessor $processor) : DocumentReader
	{
		$this->processors[] = $processor;
		return $this;
	}

	public function read($source, IDocument $document = NULL) :? IDocument
	{
		$this->prepareSource($source);
		foreach ($this->processors as $processor) {
			$classOfDocument = get_class($processor->getDocumentClass());
			if ($document === NULL || $document instanceof $classOfDocument) {
				$document = $processor->process($source);
				if ($document instanceof IDocument) {
					return $document;
				}
			}
		}
		return NULL;
	}

	private function prepareSource($source)
	{
		if ($source instanceof Image) {
			$this->sources[(string) $source] = $source;
		} elseif (is_string($source)) {
			if ($this->isImage($source)) {
				$tmpSource = Image::fromFile($source);
				$this->sources[(string) $tmpSource] = $tmpSource;
			} elseif ($this->isPdf($source)) {
				for ($i=0;$i<=$this->getPdfPagesCount($source);$i++) {
					$image = new Imagick($source . '[' . $i . ']');
					$image->setResolution( 300, 300 );
					$image->setImageFormat( "png" );

					$tmpFilename = $this->tempDir . '/' . Random::generate . '.png';
					$image->writeImage($tmpFilename);

					$tmpSource = Image::fromFile($tmpFilename);
					$this->sources[(string) $tmpSource] = $tmpSource;
					unlink($tmpFilename);
				}
			} elseif (is_file($source)) {
				throw new InvalidArgumentException('Filetype "' . finfo_file(finfo_open(FILEINFO_MIME_TYPE), $source) . '"" of source is not supported.');
			} else {
				throw new InvalidArgumentException('File "' . $source . '" not exists');
			}
		} else {
			throw new InvalidArgumentException('Source is not supported. Please use Nette\Utils\Image or path to file.');
		}
	}

	private function isImage(string $source) : bool
	{
		return in_array(finfo_file(finfo_open(FILEINFO_MIME_TYPE), $source), ['image/gif', 'image/png', 'image/jpeg'], true);
	}

	private function isPdf(string $source) : bool
	{
		return in_array(finfo_file(finfo_open(FILEINFO_MIME_TYPE), $source), ['application/pdf'], true);
	}

	private function getPdfPagesCount(string $source) : int
	{
		$stream = fopen($source, "r");
		$content = fread ($stream, filesize($source));

		if(!$stream || !$content)
			return 0;

		$count = 0;
		$regex  = '/\/Count\s+(\d+)/';

		if(preg_match_all($regex, $content, $matches))
			$count = max($matches);

		return (int) $count;
	}
}
