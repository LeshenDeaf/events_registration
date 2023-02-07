<?php

namespace Vyatsu\Events\Utils;

class Photo extends File
{
	public function __construct(int $id)
	{
		parent::__construct($id);
	}

	/**
	 * @param int[] $photoIds
	 * @return Photo[]
	 */
	public static function makePhotosArray(array $photoIds): array
	{
		$photos = [];
		foreach ($photoIds as $photoId) {
			$photos[] = new Photo($photoId);
		}
		return $photos;
	}
}
