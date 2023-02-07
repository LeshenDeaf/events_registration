<?php

namespace Vyatsu\Events\Views\Results;

use \Vyatsu\Events\Application\Application;
use \Vyatsu\Events\Views\TShower;
use CLesson;

class EventsList implements \Vyatsu\Events\Interfaces\IRenderable
{
	use TShower;

	private array $event;

	public function __construct(array $events)
	{
		$this->events = $events;
	}

	public function render(): void
	{
		global $APPLICATION;

		$APPLICATION->SetPageProperty("tags", "Мероприятия, Регистрация, ВятГУ");
		$APPLICATION->SetPageProperty("keywords", "Регистрация на мероприятия ВятГУ");
		$APPLICATION->SetPageProperty("description", "Зарегистрированные на мероприятия");
		$APPLICATION->SetTitle('Зарегистрированные на мероприятия');
		$APPLICATION->AddChainItem('Зарегистрированные на мероприятия');

		$this->renderEvents();
	}

	private function renderEvents() { ?>

		<link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH ?>/assets/css/declarations.min.css">

        <link rel="stylesheet" href="/events_registration/new_register.css">
        <link rel="stylesheet" href="/events_registration/new/assets/css/styles.css">
        <link rel="stylesheet" href="/events_registration/new/assets/css/notification.css">
        <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH ?>/assets/css/modal-image.css">

		<?php

		if ($this->events) {?>
			<div class="body-events">
				<?php
				foreach ($this->events as $event) {
					$this->renderEvent($event);
				} ?>
			</div>
			<?php
		} else { ?>
			<div class="title-event">
				Пока нет доступных мероприятий
			</div>
			<?php
		} ?>

		<div class="alert-notification" style="display: none"></div>

		<script type="module" src="/events_registration/new/assets/js/events_main.js"></script>

		<?php
	}

	private function renderEvent($event)
	{?>

		<div class="container-event">
			<!-- картинка с текстом -->

<!--			<img class="card-event"-->
<!--			     src="--><?//=$link ?? '/events_registration/new/assets/Frame 1.svg'?><!--"-->
<!--			     draggable="false"-->
<!--			>-->
            <?= $this->showSVG($event->getDescription()->getName()) ?>

			<div class="title-event">
				<?= $event->getDescription()->getName() ?>
			</div>

			<div>
				<div class="description-event">
					<span style="font-weight:bold;">Описание:</span>
					<span style="font-weight:200;">
						<?= $event->getDescription()->getShortDesc() ?: 'Описание отсутствует 😞' ?>
					</span>
				</div>
			</div>

			<div class="buttons-event">
				<?php
				$this->showGetInfoButton(
					$event->getTechInfo()->getElementId()
				);
				?>
			</div>
			<div class="buttons-event">
				<?php 
				$Course_id = EventsList::GetCourseEvent($event->getTechInfo()->getElementId());
				//PR($Course_id);
				if ($Course_id !== "") {
					$this->showGetCertButton($Course_id);
				}?>
			</div>
		</div>

		<?php
	}

	private function showGetInfoButton(int $elementId)
	{?>
		<div class="button-record-event get-table"
		     id="<?= $elementId ?>"
		     href="?id=<?= $elementId ?>"
		>
			<div>Результаты</div>
		</div>
		<?php
	}
	private function showGetCertButton(int $course_id)
	{?>
		<div class="button-record-event get-table"
		     id="cert_<?= $course_id ?>"
		     href="/account/testing/moderator_test_result/lists_cert/?COURSE_ID=<?= $course_id ?>"
		>
			<div>Сертификация</div>
		</div>
		<?php
	}

	public static function GetCourseEvent($event_id) {
		if (\CModule::IncludeModule("learning"))
		{
			$LESSON_ID = EventsList::CourseGetUFEVENT($event_id)['VALUE_ID'];
			//PR($LESSON_ID);
			if (!empty($LESSON_ID)) {
				$res = \CLearnLesson::GetList([], array("LESSON_ID" => $LESSON_ID, 'CHECK_PERMISSIONS' => "Y"), array("COURSE_ID"), []);

				if ($arLesson = $res->GetNext())
				{
					return $arLesson["COURSE_ID"];
				}
			} else {
				return "";
			}
		}
	}

	public static function CourseGetUFEVENT($event_id)
    {
        global $DB;
        $where = "F.UF_EVENT=" . $event_id;
        $strSql = "
            SELECT
                F.VALUE_ID
            FROM b_uts_learning_lessons as F
            WHERE 
                $where
            ";
        $res = $DB->Query($strSql);
        while ($row = $res->Fetch())
        {
            $result = $row;		
        }
        return $result;
    }

}
