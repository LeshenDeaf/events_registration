<?php

namespace Vyatsu\Events\Interfaces;

interface IGroup
{
	public function __construct(
		string $groupName, string $inputName, array $fields
	);


}
