<?php

namespace Vyatsu\Events\Controllers\OldEvents;

require_once $_SERVER["DOCUMENT_ROOT"] . '/events_registration/require_all.php';

class OldEventsController
{
	use \Vyatsu\Events\Views\TShower;

	const EVENT_IBLOCK = 227;
	const REGISTER_IBLOCK = 228;

	public function __construct()
	{
	}

	public function getOldEvents()
	{
		return $this->makeApplicationDescriptions($this->getEventRes());
	}

	public function render(array $descriptions)
	{?>
		<div class="body-events">
<?php
		foreach($descriptions as $description) {?>
			<div class="container-event">
				<?= $this->showSVG($description->getName(), '') ?>
				<div class="title-event"><?= $description->getName() ?></div>
				<div class="">
					<div class="description-event">
						<span style="font-weight:bold;">Описание:</span>
						<span style="font-weight:200;">
						<?= $description->getDescription() ?>
					</span>
					</div>
				</div>
				<div class="buttons-event">
					<div class="button-description-event" href="/events_registration/<?= $description->getLink() ?>">
						<div>
							Записаться
						</div>
					</div>
				</div>
			</div>
			<?php
		} ?>
		</div>
		<?php
	}

	private function showButton(string $link, string $label, string $width = '100%')
	{?>
		<a href="<?=$link?>"
		   class="minus_btn btn btn--block card__btn"
		   style="width: <?= $width ?> !important;"
		>
			<?= $label ?>
		</a>
		<?php
	}

	private function getEventRes(): \CIBlockResult
	{
		$arSelect = [
			'IBLOCK_ID', 'ID', 'NAME', 'ACTIVE',
			'PROPERTY_NAME', 'PROPERTY_DESCRIPTION', 'PROPERTY_RELATIVE_LINK',
		];

		$arFilter = [
			'IBLOCK_ID' => static::EVENT_IBLOCK,
			'ACTIVE' => 'Y',
			'ACTIVE_DATE' => 'Y'
		];

		return \CIBlockElement::GetList(
			["ID" => "ASC"], $arFilter, false, [], $arSelect
		);
	}

	private function makeApplicationDescriptions(\CIBlockResult $res)
	{
		$descriptions = [];

		while ($ob = $res->GetNextElement()) {
			$arFields = $ob->GetFields();
			$link = rtrim($arFields['PROPERTY_RELATIVE_LINK_VALUE'], '/');

			$descriptions[] = new \ApplicationDescription(
				$arFields['PROPERTY_NAME_VALUE'],
				$arFields['PROPERTY_DESCRIPTION_VALUE'],
				"$link/?id={$arFields['ID']}",
				(int)$arFields['ID']
			);
		}

		return $descriptions;
	}

}
