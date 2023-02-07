<?php

namespace Vyatsu\Events\Views;

use \Vyatsu\Events\Application\Description;

class EventsList implements \Vyatsu\Events\Interfaces\IRenderable
{
    use TShower;

    private array $events;
    private bool $hasOldEvents;
    private array $moderEventIds;

    public function __construct(array $events, bool $hasOldEvents = false, array $moderEventIds = [])
    {
        $this->events = $events;
        $this->hasOldEvents = $hasOldEvents;
        $this->moderEventIds = $moderEventIds;
    }

    public function render(): void
    {
        global $APPLICATION;

        $APPLICATION->SetPageProperty("tags", "Мероприятия, Регистрация, ВятГУ");
        $APPLICATION->SetPageProperty("keywords", "Регистрация на мероприятия, ВятГУ");
        $APPLICATION->SetPageProperty("description", "Регистрация на мероприятия ВятГУ");
        $APPLICATION->SetTitle('Регистрация на мероприятия');

        $this->renderEvents();
    }

    private function renderEvents()
    {
        $this->includeAll();

        $this->showFormContainer();

        $this->showSearch();

        $this->showEvents();

        $this->showAlertNotification();
        ?>

        <script type="text/javascript" src="/account/doc_creator/js/notification.js"></script>

        <?php
    }

    private function showSearch()
    {
        global $USER;
        ?>
        <div class="events_search">
            <div class="omrs-input-group">
                <div class="omrs-input-group">
                    <label class="omrs-input-underlined">
                        <input name="events-search_bar" type="text">
                        <span class="omrs-input-label">Поиск</span>
                    </label>
                </div>
            </div>
            <?php
            if ($USER->IsAdmin()) { ?>
                <link rel="stylesheet" href="/events_registration/new/assets/css/calendar.css">
                <script type="application/javascript" src="<?= SITE_TEMPLATE_PATH ?>/assets/js/libs/moment.min.js"></script>
                <script type="module" src="/events_registration/new/assets/js/modules/calendar.js"></script>
                <script type="module" src="/events_registration/new/assets/js/modules/load_more.js"></script>

                <div class="calendar_search">
                    <div class="calendar_opener">
                        <i class="fa fa-calendar calendar_ico" aria-hidden="true"></i>
                    </div>

                    <div class="calendar">
                        <div class="calendar_header">
                            <div class="calendar_arrows">
                                <div class="cursor-pointer set_prev_m">
                                    <img src="/events_registration/new/assets/arrow_calendar.svg">
                                </div>
                                <div class="cursor-pointer set_next_m">
                                    <img class="rotate-180" src="/events_registration/new/assets/arrow_calendar.svg">
                                </div>
                            </div>
                            <div id="month" class="calendar_header-month"></div>
                            <div id="year" class="calendar_header-year"></div>
                        </div>
                        <div id="days-of-week" class="days_of_weeks">
                            <div class="day_of_week"><div class="">Пн</div></div>
                            <div class="day_of_week"><div class="">Вт</div></div>
                            <div class="day_of_week"><div class="">Ср</div></div>
                            <div class="day_of_week"><div class="">Чт</div></div>
                            <div class="day_of_week"><div class="">Пт</div></div>
                            <div class="day_of_week"><div class="">Сб</div></div>
                            <div class="day_of_week"><div class="">Вс</div></div>
                        </div>
                        <div id="calendar" class="calendar_body">

                        </div>
                    </div>
                </div>

                <div class="button_search" style="width: 100px;margin-left: 1em;">
                    <div class="button-record-event" >
                        <div>Найти</div><div class="button-flare"></div>
                    </div>
                </div>
                <?php
            }?>
        </div>
        <?php
    }

    private function showEvents()
    {
        global $USER;

		if (\CSite::InGroup(['17'])) { ?>
            <div class="button-description-event" href="results">
                <div>Результаты</div>
            </div>
			<?php
		}

        if (!$this->events && !$this->hasOldEvents ) {?>

            <div class="title">
                Пока нет доступных мероприятий 😥
            </div>

            <div class="body-events"></div>
            <?php

            return;
        }

        echo '<div class="body-events">';

        foreach ($this->events as $event) {
            $this->renderEvent($event);
        }

        echo '</div>';

        if ($USER->IsAdmin()) {
            echo '<div class="load_more"><div>Подрузить еще</div></div>';
		}
    }

    private function renderEvent($event)
    {
		$desc = $event->getDescription();

		if ($photo = $desc->getPhoto()) {
            $link = $photo->getLink();
        }

        $additionalInfo = $desc->getAdditionalInfo();
        ?>

        <div class="container-event">
            <!-- картинка с текстом -->

            <?= $this->showSVG($desc->getName(), $additionalInfo->getHeadUnit(), $additionalInfo->getContacts()) ?>

            <div class="title-event">
                <?= $desc->getName() ?>
            </div>

            <div>
                <div class="description-event">
                    <span style="font-weight:bold;">Описание:</span>
                    <span style="font-weight:200;">
						<?= strip_tags($desc->getShortDesc()) ?: 'Описание отсутствует 😞' ?>
					</span>
                </div>
            </div>

            <div class="buttons-event">
                <?php
                $techInfo = $event->getTechInfo();

                $id = $techInfo->getElementId();

                $this->showRegisterButton(
                    $techInfo->isRegisterNeeded(),
                    $id,
                    $techInfo->isRegistrationAvailable(),
                    $techInfo->isMustFillForm(),
                    $techInfo->getOldId()
                );
                $this->showResultsButton($id, $this->moderEventIds);
                ?>
                <div class="button-description-event"
                     href="details/?id=<?= $id ?>"
                >
                    <div>
                        Подробнее
                    </div>
                </div>
            </div>

        </div>

        <?php
    }

}
