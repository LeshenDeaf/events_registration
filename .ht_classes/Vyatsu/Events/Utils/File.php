<?php

namespace Vyatsu\Events\Utils;

class File
{
	private int $id;
	private ?string $link = null;
	private ?string $path = null;
	private ?string $name = null;
	private array $fileArr = [];

	/**
	 * @param int $id Photo id
	 */
	public function __construct(int $id)
	{
		$this->id = $id;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getLink(): string
	{
		if ($this->link) {
			return $this->link;
		}

		$this->link
			= stripos($this->getPath(), '/upload/download/private') === false
			? $this->getPath()
			: '/download_files/?FILENAME='
			. str_replace(
				'/upload/download/private',
				'',
				$this->getPath()
			);

		return $this->link;
	}

	public function getPath(): string
	{
		if ($this->path) {
			return $this->path;
		}

		$this->makeFileArrIfNeeded();

		$this->path = $this->fileArr['SRC'];

		return $this->path;
	}

	public function getName(): string
	{
		if ($this->name) {
			return $this->name;
		}

		$this->makeFileArrIfNeeded();

		$this->name = $this->fileArr['ORIGINAL_NAME'];

		return $this->name;
	}

	private function makeFileArrIfNeeded()
	{
		if (!$this->fileArr) {
			$this->fileArr = \CFile::GetFileArray($this->id);
		}
	}


}
