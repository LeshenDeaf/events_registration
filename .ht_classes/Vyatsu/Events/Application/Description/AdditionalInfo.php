<?php

namespace Vyatsu\Events\Application\Description;

use Vyatsu\Events\Utils\Photo;
use Vyatsu\Events\Utils\Document;

class AdditionalInfo
{
	/**
	 * @var Photo[]
	 */
	private array $photos;
	/**
	 * @var Document[]
	 */
	private array $docs;
	/**
	 * @var ?Place
	 */
	private ?Place $place;
	/**
	 * @var array|int[]
	 */
	private array $dates;
	/**
	 * @var \Vyatsu\Events\Moderator[]
	 */
	private array $moderators;
	/**
	 * @var string[]
	 */
	private array $directions;
    /**
     * @var string
     */
    private string $headUnit;
    /**
     * @var array
     */
    private array $contacts;

	private float $cost = 0;

	/**
	 * @param Photo[]                    $photos     [optional] photos of event
	 * @param Document[]                 $docs       [optional] documents of event
	 * @param ?Place                     $place      [optional] Where an event will be held
	 * @param array{string: int,}        $dates      [optional] Must contain start and end dates in UNIX Timestamp format!
	 * @param \Vyatsu\Events\Moderator[] $moderators [optional] Moderators of event
	 * @param float                      $cost       [optional] Cost of entering to event
	 * @param array<string>              $directions [optional] Directions of educational work and activity
	 */
	public function __construct(
		array $photos = [],
		array $docs = [],
		?Place $place = null,
		array $dates = ['start' => 0, 'end' => 0],
		array $moderators = [],
		float $cost = 0,
		array $directions = [
			'science' => '',
			'education' => '',
		],
        string $headUnit = '',
        array $contacts = []
	) {
		$this->photos = $photos;
		$this->docs = $docs;
		$this->place = $place;
		$this->dates = $dates;
		$this->moderators = $moderators;
		$this->cost = $cost;
		$this->directions = $directions;
        $this->headUnit = $headUnit;
        $this->contacts = $contacts;
	}

	public function getPhotos(): array
	{
		return $this->photos;
	}

	public function getPlace(): ?Place
	{
		return $this->place;
	}

	/**
	 * @return array|int[]
	 */
	public function getDates(): array
	{
		return $this->dates;
	}

	/**
	 * @return Vyatsu\Events\Moderator[]
	 */
	public function getModerators(): array
	{
		return $this->moderators;
	}

	/**
	 * @return Document[]
	 */
	public function getDocs(): array
	{
		return $this->docs;
	}

	/**
	 * @return float|int
	 */
	public function getCost()
	{
		return $this->cost;
	}

	/**
	 * @return string[]
	 */
	public function getDirections(): array
	{
		return $this->directions;
	}

    /**
     * @return string
     */
    public function getHeadUnit(): string
    {
        return $this->headUnit;
    }

    /**
     * @return array
     */
    public function getContacts(): array
    {
        return $this->contacts;
    }

	/**
	 * @param array $dates dates in format Date/Time
	 * @return int[]
	 */
	public static function makeDatesArr(array $dates, array $descriptions): array
	{
//		return [
//			'start' => $dates[0] ? strtotime($dates[0]) : 0,
//			'end'   => $dates[1] ? strtotime($dates[1]) : 0,
//		];
//
		return array_combine(
			$descriptions,
			array_map(fn($date) => strtotime($date) ?: 0, $dates)
		);
	}

}
