<?php

namespace Vyatsu\Events;

class Moderator
{
	private int $id;
	private string $fio;
	/**
	 * It's not work position, it's position on event.
	 * For example, it can be moderator, analyst, tester, etc.
	 * @var string
	 */
	private string $position;

	/**
	 * @param int $id
	 * @param string $position
	 * @param ?string $fio [optional] <p>
	 *  If not provided, then it's taken from
	 *  {@link \CUser::GetByID()}
	 *  @see \CUser::GetByID()
	 * </p>
	 */
	public function __construct(int $id, string $position = '', string $fio = '')
	{
		$this->id = $id;
		$this->fio = $fio ?: \CUser::GetByID($id)->Fetch()['SECOND_NAME'] ?? '' ;
		$this->position = $position;
	}

	public static function makeModeratorsArr(array $moderIds): array
	{
		$moderators = [];
		foreach ($moderIds as $moderId) {
			/**
			 * @TODO Добавить Должность на мероприятии
			 */
            $moderators[] = new Moderator($moderId);
		}
		return $moderators;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getFio()
	{
		return $this->fio;
	}

	public function getPosition(): string
	{
		return $this->position;
	}

}
