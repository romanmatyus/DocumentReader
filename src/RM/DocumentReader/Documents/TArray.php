<?php

namespace RM\DocumentReader\Documents;

use DateTimeImmutable;
use Nette\Utils\ArrayHash;


trait TArray
{
	public function toArray() : ArrayHash
	{
		$out = [];
		foreach (get_class_methods($this) as $method) {
			if (substr($method, 0, 3) === 'get') {
				$val = $this->$method();
				$key = lcfirst(substr($method, 3));
				if ($val instanceof DateTimeImmutable) {
					$out[$key] = (string) $this->$method()->format('Y-m-d');
				} elseif (is_object($val)) {
					if (count($this->$method()->toArray())) {
						$out[$key] = $this->$method()->toArray();
					}
				} elseif ($val === 0) {
					$out[$key] = $val;
				} elseif (strlen($val)) {
					$out[$key] = $val;
				}
			}
		}
		return ArrayHash::from($out, TRUE);
	}
}
