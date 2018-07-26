<?php

namespace RM\DocumentReader\Documents;

use DateTimeImmutable;
use RM\DocumentReader\IDocument;


class MotorGear
{
	use TArray;

	/** @var string */
	protected $manufacturer;

	/** @var string */
	protected $id;

	/** @var float */
	protected $value;

	/** @var string */
	protected $catalyst;

	/** @var float */
	protected $power;

	/** @var integer */
	protected $speed;

	/** @var string */
	protected $fuelType;

	/** @var float */
	protected $powerRatio;

	/** @var string */
	protected $gearType;

	/** @var int */
	protected $gearsNumber;

	public function setManufacturer(string $manufacturer) : MotorGear
	{
		$this->manufacturer = $manufacturer;
		return $this;
	}

	public function getManufacturer() :? string
	{
		return $this->manufacturer;
	}

	public function setId(string $id) : MotorGear
	{
		$this->id = $id;
		return $this;
	}

	public function getId() :? string
	{
		return $this->id;
	}

	public function setValue(float $value) : MotorGear
	{
		$this->value = $value;
		return $this;
	}

	public function getValue() :? float
	{
		return $this->value;
	}

	public function setCatalyst(string $catalyst) : MotorGear
	{
		$this->catalyst = $catalyst;
		return $this;
	}

	public function getCatalyst() :? string
	{
		return $this->catalyst;
	}

	public function setPower(float $power) : MotorGear
	{
		$this->power = $power;
		return $this;
	}

	public function getPower() :? float
	{
		return $this->power;
	}

	public function setSpeed(int $speed) : MotorGear
	{
		$this->speed = $speed;
		return $this;
	}

	public function getSpeed() :? int
	{
		return $this->speed;
	}

	public function setFuelType(string $fuelType) : MotorGear
	{
		$this->fuelType = $fuelType;
		return $this;
	}

	public function getFuelType() :? string
	{
		return $this->fuelType;
	}

	public function setPowerRatio(float $powerRatio) : MotorGear
	{
		$this->powerRatio = $powerRatio;
		return $this;
	}

	public function getPowerRatio() :? float
	{
		return $this->powerRatio;
	}

	public function setGearType(string $gearType) : MotorGear
	{
		$this->gearType = $gearType;
		return $this;
	}

	public function getGearType() :? string
	{
		return $this->gearType;
	}

	public function setGearsNumber(int $gearsNumber) : MotorGear
	{
		$this->gearsNumber = $gearsNumber;
		return $this;
	}

	public function getGearsNumber() :? int
	{
		return $this->gearsNumber;
	}
}
