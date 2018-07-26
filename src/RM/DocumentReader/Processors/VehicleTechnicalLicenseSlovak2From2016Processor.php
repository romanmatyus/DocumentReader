<?php

namespace RM\DocumentReader\Processors;

use DateTimeImmutable;
use Google\Cloud\Vision\VisionClient;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\Image;
use Nette\Utils\Strings;
use RM\DocumentReader\InvalidArgumentException;
use RM\DocumentReader\IProcessor;
use RM\DocumentReader\IDocument;
use RM\DocumentReader\Documents\VehicleTechnicalLicenseSlovak2From2016Document;
use RM\DocumentReader\TTempDir;
use stdClass;


class VehicleTechnicalLicenseSlovak2From2016Processor implements IProcessor
{
	use TTempDir;

	/** @var string */
	private $debugDir;

	/** @var bool */
	private $debug = FALSE;

	/** @var Cache */
	private $cache;

	/** @var VisionClient */
	private $visionClient;


	public function __construct(IStorage $storage, VisionClient $visionClient)
	{
		$this->cache = new Cache($storage);
		$this->visionClient = $visionClient;
	}

	public function setDebugDir(string $debugDir)
	{
		$this->debugDir = $this->getTempDir($debugDir);
		$this->debug = TRUE;
	}

	public function setDebug(bool $debug = TRUE)
	{
		if (empty($this->debugDir))
			throw new InvalidArgumentException("Please, first use setDebugDir($debugDir)");
		$this->debug = $debug;
	}

	/**
	 * @param  [] of Image $source
	 * @return IDocument
	 */
	public function process(array $source) :? IDocument
	{
		$document = new VehicleTechnicalLicenseSlovak2From2016Document;

		foreach ($source as $image) {
			if (!($image instanceof Image))
				throw new InvalidArgumentException("Argument $source must be array of Image");
			if ($this->debug) {
				$this->html($image);
			}
			$this->analyzeFirstPageVehicleRegistration($image, $document);
			$this->analyzeSecondPageVehicleRegistration($image, $document);
		}

		return $document;
	}

	function analyzeFirstPageVehicleRegistration(Image $source, VehicleTechnicalLicenseSlovak2From2016Document &$document) : VehicleTechnicalLicenseSlovak2From2016Document
	{
		$i = 0;
		foreach ($this->getFirstColItemsByRows($source) as $row) {
			$rData = [];
			$lowerData = [];
			foreach ($row as $col) {
				$rData[] = Strings::lower(Strings::toAscii($col->text));
				$lowerData[] = Strings::lower($col->text);
			}

			$s = implode(' ', $rData);
			$sLower = implode(' ', $lowerData);


			if ($i === 0 && $s !== 'zakladne udaje o evidenci')
				break;
			$i++;

			if (preg_match('/^.*vin.*\s(?<vin>\w{9,17})$/', $s, $o)) {
				$document->getVehicle()->setVin(Strings::upper($o['vin']));
			}
		}
		return $document;
	}

	function analyzeSecondPageVehicleRegistration(Image $source, VehicleTechnicalLicenseSlovak2From2016Document &$document) :? VehicleTechnicalLicenseSlovak2From2016Document
	{
		$i = 0;
		foreach ($this->getFirstColItemsByRows($source) as $row) {
			$originalData = [];
			$rData = [];
			$lowerData = [];
			foreach ($row as $col) {
				$originalData[] = $col->text;
				$rData[] = Strings::lower(Strings::toAscii($col->text));
				$lowerData[] = Strings::lower($col->text);
			}

			$s = implode(' ', $rData);
			$sOriginal = implode(' ', $originalData);
			$sLower = implode(' ', $lowerData);

			if ($i === 0 && $s !== 'vozidlo')
				break;
			$i++;

			if (preg_match('/^.*druh\s(?<kind>.+)$/', $s, $o) && $i < 3) {
				$document->getVehicle()->setKind(Strings::upper($o['kind']));
			} elseif (preg_match('/^.*kategoria\s(?<category>[^\s]+).*vin.*\s(?<vin>\w{9,17})$/', $s, $o)) {
				if (!empty($document->getVehicle()->getVin() && $document->getVehicle()->getVin() !== Strings::upper($o['vin'])))
					throw new InvalidArgumentException("Documents is not for same vehicle. VIN are " . $document->getVehicle()->getVin() . " and " . Strings::upper($o['vin']) . ".");
				$document->getVehicle()->setVin(Strings::upper($o['vin']));
				$document->getVehicle()->setCategory(Strings::upper($o['category']));
			} elseif (preg_match('/^.*znacka\s(?<manufacturer>.*)$/', $s, $o)) {
				$document->getVehicle()->setManufacturer(Strings::upper($o['manufacturer']));
			} elseif (preg_match('/^.*obchodny\s*nazov\s(?<marketName>.*)$/', $s, $o)) {
				$document->getVehicle()->setMarketName(Strings::upper($o['marketName']));
			} elseif (preg_match('/^.*typ\s*\/\s*variant\s*\/\s*verzia\s(?<content>.*)$/', $s, $o)) {
				$o = explode('/', $o['content']);
				$removeSpace = function ($s) {
					return Strings::replace($s, '~\s~i', '');
				};
				$document->getVehicle()->setType($removeSpace(Strings::upper(Strings::trim($o[0], ' -'))));
				if (isset($o[1]))
					$document->getVehicle()->setVariant($removeSpace(Strings::upper($o[1])));
				if (isset($o[2]))
					$document->getVehicle()->setVersion($removeSpace(Strings::upper($o[2])));
			} elseif (preg_match('/^\s*9\s*vyrobca\s*vozidla\s*\(\s*podvozku\s*\)\s*(?<manufacturerChassis>.*)$/', $s, $o)) {
				$o = Strings::upper($o['manufacturerChassis']);
				$o = str_replace(' . LTD . , ', '. LTD,', $o);
				$o = str_replace(' , ', ',', $o);
				$document->getVehicle()->setManufacturerChassis($o);
			} elseif (preg_match('/^\s*10.*cislo.*schvalenia\s*es\s*(?<approvalNumber>.*)$/', $s, $o)) {
				$o = $o['approvalNumber'];
				$o = Strings::trim($o);
				$o = str_replace(' ', '', $o);

				if (strlen($o) > 7) {
					if ($o[strlen($o)-3] !== '*') {
						$o = substr($o, 0, -2) . '*' . substr($o, -2);
					}
					if ($o[strlen($o)-8] !== '*') {
						$o = substr($o, 0, -7) . '*' . substr($o, -7);
					}
					$_o = explode('*', $o);
					if ($_o[1][strlen($_o[1])-3] !== '/') {
						$_o[1] = substr($_o[1], 0, -3) . '/' . substr($_o[1], -2);
						$o = implode('*', $_o);
					}
				}
				if ($o !== '-')
					$document->getVehicle()->setApprovalNumber($o);

			} elseif (preg_match('/^\s*11.*datum.*schvalenia\s*es\s*(?<approvalDate>.*)$/', $s, $o)) {
				$o = $o['approvalDate'];
				if ((int) $o) {
					list($d, $m, $y) = explode('.', $o);
					$d = (int) $d;
					$m = (int) $m;
					$y = (int) $y;

					$document->getVehicle()->setApprovalDate(new DateTimeImmutable($y . '-' . $m . '-' . $d));
				}
			} elseif (preg_match('/^\s*12\s*vyrobca\s*motora\s*(?<manufacturer>.*)$/', $s, $o)) {
				$o = Strings::upper($o['manufacturer']);
				$o = str_replace(' . LTD . , ', '. LTD,', $o);
				$o = str_replace(' , ', ',', $o);
				$document->getMotorGear()->setManufacturer($o);
			} elseif (preg_match('/identifikacne\s*cislo\s*motora\s*\(\s*typ\s*\)\s(?<id>.*)$/', $s, $o)) {
				$o = Strings::upper($o['id']);
				$o = Strings::trim($o);
				$document->getMotorGear()->setId($o);
			} elseif (preg_match('/zdvihovy\s*objem\s*valcov*\s(?<value>[\d ]+).*katalyzator\s*(?<catalyst>\w+)$/', $s, $o)) {
				$document->getMotorGear()->setValue((float) Strings::trim(Strings::upper(str_replace(' ', '', $o['value']))));
				$document->getMotorGear()->setCatalyst(Strings::trim(Strings::upper($o['catalyst'])));
			} elseif (preg_match('/najvacsi\s*vykon\s*motora.*otacky\s*(?<power>[0-9 \.,]*)[^0-9]*(?<speed>\d+)/', $s, $o)) {
				$document->getMotorGear()->setPower((float) str_replace(',', '.', Strings::replace($o['power'], '~\s~i', '')));
				$document->getMotorGear()->setSpeed((int) $o['speed']);
			} elseif (preg_match('/druh\s*paliva\s*\/\s*zdroj\s*energie\s*(?<fuelType>.*)/', $s, $o)) {
				$document->getMotorGear()->setFuelType(Strings::upper($o['fuelType']));
			} elseif (preg_match('/20\s*prevodovka.*pocet.*stupnov\s*(?<gearType>[^\s]+)\s*\/?\s*(?<gearsNumber>\d+)/', $s, $o)) {
				if (strpos($s, "cvti 1") && $o['gearType'] === "cvti") {
					$o['gearType'] = "cvt";
				}
				$document->getMotorGear()->setGearType(Strings::upper($o['gearType']));
				$document->getMotorGear()->setGearsNumber((int) $o['gearsNumber']);
			} elseif (preg_match('/\s*Druh.*typ\s*\)\s*(?<type>.*)/', $sOriginal, $o)) {
				$o = Strings::upper($o['type']);
				$o = str_replace(' .', '.', $o);
				$document->getBodywork()->setType($o);
			} elseif (preg_match('/22.*arba\s*(?<color>.+)/', $sOriginal, $o)) {
				$document->getBodywork()->setColor(Strings::upper($o['color']));
			} elseif (preg_match('/^\s*23\s*vyrobca\s*(?<manufacturer>.*)$/', $s, $o)) {
				$o = Strings::upper($o['manufacturer']);
				$o = str_replace(' . LTD . , ', '. LTD,', $o);
				$o = str_replace(' , ', ',', $o);
				$document->getBodywork()->setManufacturer($o);
			} elseif (preg_match('/24\s*vyrobne\s*cislo\s*(?<number>.+)/', $s, $o) && strlen($o['number'])) {
				$document->getBodywork()->setNumber(Strings::upper($o['number']));
			} elseif (preg_match('/25.*pocet\s*miest.*nudzovych\s*(?<seats>\d+)\s*(\/|7)\s*(?<seatsEmergency>[\d-]+)/', $s, $o)) {
				$document->getBodywork()->setSeats($o['seats']);
				if ($o['seatsEmergency'] !== "-") {
					$document->getBodywork()->setSeatsEmergency((int) $o['seatsEmergency']);
				}
			} elseif (preg_match('/25.*pocet\s*miest.*statie\s*(?<placeStands>[^\s]+).*25.*pocet\s*lozok\s*(?<beds>\d+)/', $s, $o)) {
				$document->getBodywork()->setPlaceStands((int) $o['placeStands']);
				$document->getBodywork()->setBeds((int) $o['beds']);
			} elseif (preg_match('/26\s*zatazenie.*strechy\s*(?<roofLoad>.+)$/', $s, $o)) {
				if (preg_match('/(?<roofLoad>\d+)\s*kg/', $o['roofLoad'], $o))
				$document->getBodywork()->setRoofLoad($o['roofLoad']);
			} elseif (preg_match('/27\s*objem\s*skrine.+cisterny\s*(?<volume>\d+)/', $s, $o)) {
				$document->getBodywork()->setVolume($o['volume']);
			} elseif (preg_match('/28.*objem\s*palivovej\s*nadrze\s*(?<fuelVolume>\d+)$/', $s, $o)) {
				$document->getBodywork()->setVolumeFuel($o['fuelVolume']);
			}
		}

		return $document;
	}

	public function getDocumentClass() : IDocument
	{
		return new VehicleTechnicalLicenseSlovak2From2016Document;
	}


	private function getFirstColRows(Image $source) : array
	{
		$usedY = [];
		foreach ($this->getFirstColItems($source) as $item) {
			for ($j = $item->y;$j <= $item->y+$item->height;$j++) {
				$usedY[] = $j;
			}
		}

		$i = 0;
		$j = 0;
		$rows = [];
		$activeRow = NULL;
		for ($i=min($usedY);$i<=max($usedY);$i++) {
			if (in_array($i, $usedY) && $activeRow === NULL) {
				$activeRow = (object) [
					'minY' => $i,
					'maxY' => $i,
				];
			}
			if (in_array($i, $usedY)) {
				$activeRow->maxY = $i;
			} else {
				if ($activeRow) {
					$rows[] = $activeRow;
				}
				$activeRow = NULL;
			}
		}
		return $rows;
	}

	private function getFirstColItemsByRows(Image $image) : array
	{
		return $this->cache->load('getFirstColItemsByRows-' . md5((string) $image), function (&$dependencies) use ($image) {
			$dependencies = [
				Cache::EXPIRE => '7 days',
				Cache::SLIDING => true,
			];

			$data = [];
			foreach ($this->getFirstColRows($image) as $row) {
				$rowData = [];
				foreach ($this->getFirstColItems($image) as $item) {
					if ($item->y >= $row->minY AND $item->y <= $row->maxY) {
						$rowData[$item->x] = $item;
					}
				}
				ksort($rowData);
				$data[] = $rowData;
			}
			return $data;
		});
	}

	private function getFirstColItems(Image $source) : array
	{
		$firstCol = $this->getFirstCol($source);
		$items = [];
		foreach ($this->getItems($source) as $item) {
			if ($item->x >= $firstCol->minX AND $item->x <= $firstCol->maxX) {
				$items[] = $item;
			}
		}
		return $items;
	}

	private function getFirstCol(Image $source) : stdClass
	{
		$usedX = [];
		foreach ($this->getItems($source) as $item) {
			for ($j = $item->x;$j <= $item->x+$item->width;$j++) {
				$usedX[] = $j;
			}
		}

		$usedX = array_unique($usedX);
		$i = 0;
		for ($i=min($usedX); $i<max($usedX);$i++) {
			if (!in_array($i, $usedX))
				break;
		}
		return (object) [
			'minX' => min($usedX),
			'maxX' => $i,
		];
	}

	private function getItems(Image $source) : array
	{
		$items = [];
		foreach ($this->parseByVision($source) as $row) {
			$row = array_values((array) $row);
			$xMin = NULL;
			$yMin = NULL;
			$xMax = NULL;
			$yMax = NULL;
			foreach ($row[0]['boundingPoly']['vertices'] as $d) {
				$xMin = ($xMin === NULL)
					? $d['x']
					: min($xMin, $d['x']);
				$yMin = ($yMin === NULL)
					? $d['y']
					: min($yMin, $d['y']);
				$xMax = ($xMax === NULL)
					? $d['x']
					: max($xMax, $d['x']);
				$yMax = ($yMax === NULL)
					? $d['y']
					: max($yMax, $d['y']);
			}
			$items[] = (object) [
				'text' => $row[0]['description'],
				'x' => $xMin,
				'y' => $yMin,
				'width' => $xMax - $xMin,
				'height' => $yMax - $yMin,
			];
		}

		$biggest = NULL;
		foreach ($items as $key => $item) {
			if ($biggest === NULL) {
				$biggest = (object) [
					'key' => $key,
					'data' => $item,
				];
			} else {
				if ($item->width * $item->height > $biggest->data->width * $biggest->data->height) {
					$biggest = (object) [
						'key' => $key,
						'data' => $item,
					];
				}
			}
		}
		unset($items[$biggest->key]);

		return $items;
	}

	private function html(Image $source)
	{
		$filename = $this->debugDir . '/preview-' . md5((string) $source) . '.html';
		file_put_contents($filename, '<head><meta charset="utf-8"></head>');

		$firstCol = $this->getFirstCol($source);
		file_put_contents($filename, '<div style="position:absolute;top:0px;left:' . $firstCol->minX . 'px;height:10px;width:' . ($firstCol->maxX - $firstCol->minX) . 'px;background-color:red"></div>', FILE_APPEND);

		foreach ($this->getFirstColRows($source) as $item) {
			file_put_contents($filename, '<div style="position:absolute;top:' . $item->minY . 'px;left:0px;height:' . ($item->maxY - $item->minY) . ';width:10px;background-color:red"></div>', FILE_APPEND);
		}

		$i = 0;
		foreach ($this->getItems($source) as $item) {
			file_put_contents($filename, '<div title="' . $i . '" style="border:1px solid #555;position:absolute;top:' . $item->y . 'px;left:' . $item->x . 'px;height:' . $item->height . 'px;width:' . $item->width . 'px;">' . $item->text . '</div>', FILE_APPEND);
			$i++;
		}
	}


	private function parseByVision(Image $image) : array
	{
		return $this->cache->load('parseByVision-' . md5((string) $image), function (&$dependencies) use ($image) {
			$dependencies = [
				Cache::EXPIRE => '7 days',
				Cache::SLIDING => true,
			];
			var_dump('Call Google Vision');
			$image = $this->visionClient->image((string) $image, ['DOCUMENT_TEXT_DETECTION']);
			$result = $this->visionClient->annotate($image);

			return (array) $result->text();
		});
	}
}
