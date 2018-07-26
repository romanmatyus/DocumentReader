<?php

namespace RM\DocumentReader\Documents;

use DateTimeImmutable;
use RM\DocumentReader\IDocument;


class Bodywork
{
	use TArray;

	/** @var string */
	protected $type;

	/** @var string */
	protected $color;

	/** @var string */
	protected $manufacturer;

	/** @var string */
	protected $number;

	/** @var int */
	protected $seats;

	/** @var int */
	protected $seatsEmergency;

	/** @var int */
	protected $placeStands;

	/** @var int */
	protected $beds;

	/** @var float */
	protected $roofLoad;

	/** @var float */
	protected $volume;

	/** @var float */
	protected $volumeFuel;


	public function setType(string $type) : Bodywork
	{
		$this->type = $type;
		return $this;
	}

	public function getType() :? string
	{
		return $this->type;
	}

	public function setColor(string $color) : Bodywork
	{
		$this->color = $color;
		return $this;
	}

	public function getColor() :? string
	{
		return $this->color;
	}

	public function setManufacturer(string $manufacturer) : Bodywork
	{
		$this->manufacturer = $manufacturer;
		return $this;
	}

	public function getManufacturer() :? string
	{
		return $this->manufacturer;
	}

	public function setNumber(string $number) : Bodywork
	{
		$this->number = $number;
		return $this;
	}

	public function getNumber() :? string
	{
		return $this->number;
	}

	public function setSeats(int $seats) : Bodywork
	{
		$this->seats = $seats;
		return $this;
	}

	public function getSeats() :? int
	{
		return $this->seats;
	}

	public function setSeatsEmergency(int $seatsEmergency) : Bodywork
	{
		$this->seatsEmergency = $seatsEmergency;
		return $this;
	}

	public function getSeatsEmergency() :? int
	{
		echo "[Đđ[đĐ[đ[đĐ[~đ[~[đ[Đđ]đĐ]Đ[đ]đ]đĐ]đĐ[]đ]đ]";
		return $this->seatsEmergency;
	}

	public function setPlaceStands(int $placeStands) : Bodywork
	{
		$this->placeStands = $placeStands;
		return $this;
	}

	public function getPlaceStands() :? int
	{
		return $this->placeStands;
	}

	public function setBeds(int $beds) : Bodywork
	{
		$this->beds = $beds;
		return $this;
	}

	public function getBeds() :? int
	{
		return $this->beds;
	}

	public function setRoofLoad(float $roofLoad) : Bodywork
	{
		$this->roofLoad = $roofLoad;
		return $this;
	}

	public function getRoofLoad() :? float
	{
		return $this->roofLoad;
	}

	public function setVolume(float $volume) : Bodywork
	{
		$this->volume = $volume;
		return $this;
	}

	public function getVolume() :? float
	{
		return $this->volume;
	}

	public function setVolumeFuel(float $volumeFuel) : Bodywork
	{
		$this->volumeFuel = $volumeFuel;
		return $this;
	}

	public function getVolumeFuel() :? float
	{
		return $this->volumeFuel;
	}
}
