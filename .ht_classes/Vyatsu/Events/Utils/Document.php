<?php

namespace Vyatsu\Events\Utils;

class Document extends File
{
	public function __construct(int $id)
	{
		parent::__construct($id);
	}

	/**
	 * @param int[] $docIds
	 * @return Document[]
	 */
	public static function makeDocumentsArray(array $docIds): array
	{
		$docs = [];
		foreach ($docIds as $docId) {
			$docs[] = new Document($docId);
		}
		return $docs;
	}
}
