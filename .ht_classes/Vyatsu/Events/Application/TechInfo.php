<?php

namespace Vyatsu\Events\Application;

class TechInfo
{
	private int $elementId;
	private string $eventType;
	private bool $isOnline;
	private bool $isRegisterNeeded;
	private int $maxUsers;
	private int $registeredUsers;
	private bool $isRegistrationAvailable = false;
	private bool $mustFillForm;
	private bool $isCovidCertificateRequired;
	private array $consents;
    private int $minAge;
	private int $oldId;

	public function __construct(
		int $elementId,
		string $eventType,
		bool $isOnline,
		bool $isRegisterNeeded,
		int $maxUsers,
		int $registeredUsers,
		bool $isRegistrationAvailable,
		array $form,
		bool $isCovidCertificateRequired = false,
		array $consents = [],
        int $minAge = 0,
        int $oldId = 0
	) {
		$this->elementId = $elementId;
		$this->eventType = $eventType;
		$this->isOnline = $isOnline;
		$this->isRegisterNeeded = $isRegisterNeeded;
		$this->maxUsers = $maxUsers;
		$this->registeredUsers = $registeredUsers;
		$this->isRegistrationAvailable = $isRegistrationAvailable;
		$this->mustFillForm = !empty($form);
		$this->isCovidCertificateRequired = $isCovidCertificateRequired;
		$this->consents = $consents;
        $this->minAge = $minAge;
		$this->oldId = $oldId;
	}

	public function getElementId(): int
	{
		return $this->elementId;
	}

	public function getEventType(): string
	{
		return $this->eventType;
	}

	public function isOnline(): bool
	{
		return $this->isOnline;
	}

	public function isRegisterNeeded(): bool
	{
		return $this->isRegisterNeeded;
	}

	public function getMaxUsers(): int
	{
		return $this->maxUsers;
	}

	public function getRegisteredUsers(): int
	{
		return $this->registeredUsers;
	}

	public function isRegistrationAvailable(): bool
	{
		return $this->isRegistrationAvailable;
	}

	public function isMustFillForm(): bool
	{
		return $this->mustFillForm;
	}

	public function isCovidCertificateRequired(): bool
	{
		return $this->isCovidCertificateRequired;
	}

    public function getMinAge(): int
    {
        return $this->minAge;
    }

	public function getOldId(): int
	{
		return $this->oldId;
	}

}
