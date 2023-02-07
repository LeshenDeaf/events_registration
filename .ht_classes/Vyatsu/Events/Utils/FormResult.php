<?php

namespace Vyatsu\Events\Utils;

class FormResult
{
	private int $id;
	private int $userId = 0;
	private array $result;
    private array $files;

	public function __construct(int $id, int $userId, array $result, array $files = [])
	{
		$this->id = $id;
		$this->userId = $userId;
		$this->result = $result;
        $this->files = $files;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

    public function getFiles(): array
    {
        return $this->files;
    }

	public function getResult(): array
	{
		return $this->result;
	}
}
