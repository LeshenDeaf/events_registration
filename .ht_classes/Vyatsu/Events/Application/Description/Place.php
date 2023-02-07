<?php

namespace Vyatsu\Events\Application\Description;

class Place
{
	/**
	 * @var array<string>
	 */
	private array $places;
	/**
	 * @var array<string>
	 */
	private array $links;
	/**
	 * @var array<string>
	 */
	private array $auditories;

	public function __construct(array $places = [],
	                            array $links = [],
	                            array $auditories = []
	) {
		$this->places = $places;
		$this->links = $links;
		$this->auditories = $auditories;
	}

	/**
	 * @return string[]
	 */
	public function getPlaces(): array
	{
		return $this->places;
	}

	/**
	 * @param string[] $places
	 */
	public function setPlaces(array $places): void
	{
		$this->places = $places;
	}

	/**
	 * @return string[]
	 */
	public function getLinks(): array
	{
		return $this->links;
	}

	/**
	 * @param string[] $links
	 */
	public function setLinks(array $links): void
	{
		$this->links = $links;
	}

	/**
	 * @return string[]
	 */
	public function getAuditories(): array
	{
		return $this->auditories;
	}

	/**
	 * @param string[] $auditories
	 */
	public function setAuditories(array $auditories): void
	{
		$this->auditories = $auditories;
	}

	public function isEmpty(): bool
	{
		return empty($this->places)
			&& empty($this->auditories)
			&& empty($this->links);
	}

}
