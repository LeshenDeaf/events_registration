<?php

namespace Vyatsu\Events\Application\Description;

use Vyatsu\Events\Utils\Photo;

class Description
{
	private string $name;
	private string $fullDesc;
	private AdditionalInfo $additionalInfo;
	private string $shortDesc;
	private ?Photo $photo;

	/**
	 * @param string         $name Event name
	 * @param string         $fullDesc full description of event
	 * @param AdditionalInfo $additionalInfo Addiitonal info about event
	 * @param ?string $shortDesc [optional] <p>
	 *  Short description. If not provided, then first N symbols of full desc would be used
	 * </p>
	 * @see Vyatsu\Events\Application\Description\AdditionalInfo
	 */
	public function __construct(
		string $name,
		string $fullDesc,
		AdditionalInfo $additionalInfo,
		?string $shortDesc = '',
		?Photo $photo = null
	) {
		$this->name = $name;
		$this->fullDesc = $fullDesc;
		$this->additionalInfo = $additionalInfo;
		$this->shortDesc = $shortDesc ?: mb_strimwidth(
			$fullDesc, 0, 100, '...'
		);
		$this->photo = $photo;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getFullDesc(): string
	{
		return $this->fullDesc;
	}

	public function getShortDesc(): string
	{
		return $this->shortDesc;
	}

	public function getPhoto(): ?Photo
	{
		return $this->photo;
	}

	public function getAdditionalInfo(): AdditionalInfo
	{
		return $this->additionalInfo;
	}


}
