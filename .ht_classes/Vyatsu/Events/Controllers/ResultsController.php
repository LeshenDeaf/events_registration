<?php

namespace Vyatsu\Events\Controllers;

use \Vyatsu\Events\Application\EventResult;
use \Vyatsu\Events\Utils\EventGenerator;
use \Vyatsu\Events\Views\Results;
use \Vyatsu\Events\RuntimeExceptions\AccessException;

class ResultsController
{
	private int $userId;
	private int $eventId;
	private EventGenerator $eventGenerator;

	public function __construct(int $userId)
	{
		if (!$userId) {
			throw new \RuntimeException("Пользователь не авторизирован");
		}

		$this->userId = $userId;
		$this->eventId = filter_input(
			INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT
		) ?? 0;
		$this->eventGenerator = new EventGenerator();
	}

	public function control()
	{
		if ($this->eventId) {
			$this->show();
		} else {
			$this->index();
		}
	}

	public function show()
	{
		try {
			$event = $this->getEvents()[0];
		} catch (AccessException $accessException) {
			Results\Event::renderError($accessException->getMessage());
			return;
		}

		$ec = new EventController($event->getTechInfo()->getElementId());

		$view = new Results\Event(
			new EventResult(
				$event->getTechInfo()->getElementId(),
				$event->getDescription()->getName(),
				$ec->findForm(),
                $event->getTechInfo()->isCovidCertificateRequired()
			)
		);

		$view->render();
	}

	public function index()
	{
		$events = $this->getEvents();
		$view = new Results\EventsList($events);

		$view->render();
	}

	private function getEvents()
	{
		return $this->eventGenerator->generate(
			EventController::generateRes(
				$this->generateFilter()
			)
		);
	}

	/**
	 * @throws AccessException if access denied
	 * @return array
	 */
	private function generateFilter(): array
	{
		$availableIds = $this->getAvailableEventIds();

		if ($this->eventId) {
			if (in_array($this->eventId, $availableIds)) {
				return $this->makeFilter();
			}

			throw new AccessException(
				'У Вас нет доступа к этой странице'
			);
		}

		return $this->makeFilterList($availableIds);
	}

	private function makeFilter(): array
	{
		return [
			'IBLOCK_ID' => EventController::EVENTS_IBLOCK_ID,
			'ACTIVE' => ['Y', 'N'],
			'ID' => $this->eventId,
		];
	}

	private function makeFilterList(array $ids): array
	{
		return [
			'IBLOCK_ID' => EventController::EVENTS_IBLOCK_ID,
			[
				"LOGIC" => "OR",
				['ID' => $ids],
				['PROPERTY_HEAD_USERS' => $this->userId],
			],

		];
	}

	public function getAvailableEventIds(): array
	{
		return array_merge(
			$this->getIdsFromAccessIblock(),
			$this->getIdsFromEventsIblock()
		);
	}

	private function getIdsFromAccessIblock(): array
	{
		$arFilter = [
			'IBLOCK_ID' => EventController::ACCESS_EVENTS_IBLOCK_ID,
			'ACTIVE'    => 'Y',
			'PROPERTY_MODER_USERS' => $this->userId,
		];

		$res = EventController::generateRes(
			$arFilter, ['IBLOCK_ID', 'ID', 'PROPERTY_EVENT_IDS']
		);


		$ids = [];

		while ($arFields = $res->Fetch()) {
			$ids = array_merge($ids, $arFields['PROPERTY_EVENT_IDS_VALUE']);
		}

		return $ids;
	}

	private function getIdsFromEventsIblock(): array
	{
		$ids = [];

		$arFilter = [
			'IBLOCK_ID' => EventController::EVENTS_IBLOCK_ID,
			'ACTIVE' => 'Y',
			'PROPERTY_HEAD_USERS' => $this->userId,
		];

		$res = EventController::generateRes(
			$arFilter, ['IBLOCK_ID', 'ID',]
		);

		while ($arFields = $res->Fetch()) {
			$ids[] = $arFields['ID'];
		}

		return $ids;
	}


}
