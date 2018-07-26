<?php

namespace RM\DocumentReader;

use RM\DocumentReader\IDocument;


interface IProcessor
{
	public function process(array $source) :? IDocument;
	public function getDocumentClass() : IDocument;
}
