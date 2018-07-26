<?php

namespace RM\DocumentReader\Documents;

use RM\DocumentReader\IDocument;
use Nette\Utils\ArrayHash;


class VehicleTechnicalLicenseSlovak2From2016Document implements IDocument
{
	use TArray;

	/** @var string */
	protected $country;

	/** @var string */
	protected $number;

	/** @var DateTimeImmutable */
	protected $makeDate;

	/** @var DateTimeImmutable */
	protected $firstRegistryInCountry;

	/** @var string */
	protected $vin;

	/** @var string */
	protected $plateNumber;

	/** @var string */
	protected $owner;

	/** @var DateTimeImmutable */
	protected $ownerBirthDate;

	/** @var string */
	protected $ownerAddress;

	/** @var string */
	protected $holder;

	/** @var DateTimeImmutable */
	protected $holderBirthDate;

	/** @var string */
	protected $holderAddress;

	/** @var string */
	protected $place;

	/** @var string */
	protected $dateCreation;

	/** @var Vehicle */
	protected $vehicle;

	/** @var MotorGear */
	protected $motorGear;

	/** @var Bodywork */
	protected $bodywork;

	public function getVehicle() : Vehicle
	{
		if ($this->vehicle instanceof Vehicle)
			return $this->vehicle;

		return $this->vehicle = new Vehicle;
	}

	public function getMotorGear() : MotorGear
	{
		if ($this->motorGear instanceof MotorGear)
			return $this->motorGear;

		return $this->motorGear = new MotorGear;
	}

	public function getBodywork() : Bodywork
	{
		if ($this->bodywork instanceof Bodywork)
			return $this->bodywork;

		return $this->bodywork = new Bodywork;
	}
}
