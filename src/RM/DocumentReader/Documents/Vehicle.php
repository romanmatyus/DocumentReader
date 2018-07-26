<?php

namespace RM\DocumentReader\Documents;

use DateTimeImmutable;
use Nette\Utils\ArrayHash;
use RM\DocumentReader\IDocument;


class Vehicle
{
	use TArray;

	/** @var string */
	protected $kind;

	/** @var string */
	protected $category;

	/** @var string */
	protected $vin;

	/** @var string */
	protected $manufacturer;

	/** @var string */
	protected $marketName;

	/** @var string */
	protected $type;

	/** @var string */
	protected $variant;

	/** @var string */
	protected $version;

	/** @var string */
	protected $manufacturerChassis;

	/** @var string */
	protected $approvalNumber;

	/** @var DateTimeImmutable */
	protected $approvalDate;


	public function setKind(string $kind) : Vehicle
	{
		$this->kind = $kind;
		return $this;
	}

	public function getKind() :? string
	{
		return $this->kind;
	}


	public function setCategory(string $category) : Vehicle
	{
		$this->category = $category;
		return $this;
	}

	public function getCategory() :? string
	{
		return $this->category;
	}

	public function setVin(string $vin) : Vehicle
	{
		$this->vin = $vin;
		return $this;
	}

	public function getVin() :? string
	{
		return $this->vin;
	}

	public function setManufacturer(string $manufacturer) : Vehicle
	{
		$this->manufacturer = $manufacturer;
		return $this;
	}

	public function getManufacturer() :? string
	{
		return $this->manufacturer;
	}

	public function setMarketName(string $marketName) : Vehicle
	{
		$this->marketName = $marketName;
		return $this;
	}

	public function getMarketName() :? string
	{
		return $this->marketName;
	}

	public function setType(string $type) : Vehicle
	{
		$this->type = $type;
		return $this;
	}

	public function getType() :? string
	{
		return $this->type;
	}


	public function setVariant(string $variant) : Vehicle
	{
		$this->variant = $variant;
		return $this;
	}

	public function getVariant() :? string
	{
		return $this->variant;
	}

	public function setVersion(string $version) : Vehicle
	{
		$this->version = $version;
		return $this;
	}

	public function getVersion() :? string
	{
		return $this->version;
	}

	public function setManufacturerChassis(string $manufacturerChassis) : Vehicle
	{
		$this->manufacturerChassis = $manufacturerChassis;
		return $this;
	}

	public function getManufacturerChassis() :? string
	{
		return $this->manufacturerChassis;
	}

	public function setApprovalNumber(string $approvalNumber) : Vehicle
	{
		$this->approvalNumber = $approvalNumber;
		return $this;
	}

	public function getApprovalNumber() :? string
	{
		return $this->approvalNumber;
	}

	public function setApprovalDate(DateTimeImmutable $approvalDate) : Vehicle
	{
		$this->approvalDate = $approvalDate;
		return $this;
	}

	public function getApprovalDate() :? DateTimeImmutable
	{
		return $this->approvalDate;
	}

}
