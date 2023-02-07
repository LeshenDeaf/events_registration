<?php

namespace Vyatsu\Events\Application;

use \Vyatsu\Events\Application\Description\Description;

class Application
{
	private Description $description;
	private ?Form $form;
	private ?TechInfo $info;

	/**
	 * @param Description $description  Event application description
	 * @param ?Form       $form         Event form
	 * @param ?TechInfo   $info         Event application technical info
	 */
	public function __construct(
		Description $description, ?Form $form = null, ?TechInfo $info = null
	) {
		$this->description = $description;
		$this->form = $form;
		$this->info = $info;
	}

	public function getDescription(): Description
	{
		return $this->description;
	}

	public function getForm(): ?Form
	{
		return $this->form;
	}

	public function getTechInfo(): ?TechInfo
	{
		return $this->info;
	}

}
