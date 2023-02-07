<?php

namespace Vyatsu\Events\Views;

use \Vyatsu\Events\Application\Application;
use \Vyatsu\Events\Application\Description\Place;

class Event implements \Vyatsu\Events\Interfaces\IRenderable
{
    use TShower;
	private Application $event;
    private bool $isUserModer;

	public function __construct(Application $event, bool $isUserModer = false)
	{
		$this->event = $event;
        $this->isUserModer = $isUserModer;
	}

	public function render(): void
	{
		global $APPLICATION;

		$APPLICATION->SetPageProperty("tags", "Мероприятия, Регистрация, ВятГУ");
		$APPLICATION->SetPageProperty("keywords", "Регистрация на мероприятия ВятГУ");
		$APPLICATION->SetPageProperty("description", "Регистрация на мероприятия");

        $eventName = $this->event->getDescription()->getName();

		$APPLICATION->SetTitle("Регистрация на \"$eventName\"");
        $APPLICATION->AddChainItem($eventName);

		$this->renderEvent();
	}

    public static function accessDenied()
    {
	    global $APPLICATION;

	    $APPLICATION->SetPageProperty("tags", "Мероприятия, Регистрация, ВятГУ");
	    $APPLICATION->SetPageProperty("keywords", "Регистрация на мероприятия ВятГУ");
	    $APPLICATION->SetPageProperty("description", "Регистрация на мероприятия");
	    $APPLICATION->SetTitle('Доступ закрыт');
	    $APPLICATION->AddChainItem('Доступ закрыт');
	    ?>

        <link rel="stylesheet" href="/events_registration/new_register.css">
        <link rel="stylesheet" href="/events_registration/new/assets/css/styles.css">
        <link rel="stylesheet" href="/events_registration/new/assets/css/notification.css">
        <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH ?>/assets/css/modal-image.css">

        <div class="container">
            <div class="title-event">
                Доступ к данному мероприятию для Вас закрыт
            </div>
        </div>
       <?php
    }

	private function renderEvent()
	{
        $desc = $this->event->getDescription();
        $additionalInfo = $desc->getAdditionalInfo();
        $this->includeAll();
        $this->showFormContainer();
        ?>


        <div class="container">
            <div class="title-event">
                <?= $desc->getName() ?>
            </div>

            <div class="wrapper">
                <div class="card-event">
                    <?= $this->showSVG($desc->getName(), $additionalInfo->getHeadUnit(), $additionalInfo->getContacts()) ?>


                    <div class="info-event">
		                <?php
		                $techInfo = $this->event->getTechInfo();
                        $id = $techInfo->getElementId();

		                $this->showRegisterButton(
			                $techInfo->isRegisterNeeded(),
                            $id,
			                $techInfo->isRegistrationAvailable(),
			                $techInfo->isMustFillForm(),
                            $techInfo->getOldId()
		                );
                        $this->showResultsButton($id, [], $this->isUserModer);

                        $this->showCost($additionalInfo->getCost());
		                $this->showDates($additionalInfo->getDates());
		                $this->showPlace($additionalInfo->getPlace());
		                $this->showHeads($additionalInfo->getModerators());
		                $this->showDirections($additionalInfo->getDirections());
		                ?>
                    </div>
                </div>


                <div class="description-event">
                    <?php
                    if ($techInfo->isCovidCertificateRequired()) {?>
                        <div class="covid_notif">
                            <svg class="covid_notif_i" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z"></path></svg>
                            <p>На мероприятии требуется сертификат Covid-19</p>
                        </div>
                        <?php
                    }
                    ?>
                    <span style="font-size: 1.2em">Описание:</span>
                    <br><?= $desc->getFullDesc() ?: 'Тут пока ничего нет 😞' ?>
                    <br><?php $this->showDocs($additionalInfo->getDocs()) ?>
                </div>
            </div>

            <?php $this->showPhotos($additionalInfo->getPhotos()); ?>

        </div>
        <?php $this->showAlertNotification() ?>
        <script type="text/javascript" src="/account/doc_creator/js/notification.js"></script>
		<?php
	}

    private function showCost(float $cost): void
    {
        if ($cost <= 0) {
            return;
        }
        ?>
        <div class="info-details-event">
            <div>Стоимость входа на мероприятие: <span><?= $cost ?>₽</span></div>
        </div>
    <?php
    }

    private function showDates(array $dates): void
    {
        ?>
        <div class="info-details-event">
            <?php if (!count($dates)) {
	            echo "<div>Дата проведения мероприятия еще не определена</div></div>";
                return;
            }

            echo "<div>Дат" . (count($dates) > 1 ? 'ы' : 'а')
                . " проведения мероприятия:</div>";

            foreach ($dates as $description => $date) {
                $date = date('d.m.Y H:i', $date);
	            echo "<div>{$date} <span style='font-weight: normal'> &#8212; {$description}</span></div>";
            }
            ?>
        </div>
       <?php
    }

    private function showPlace(Place $place): void
    {
        if ($place->isEmpty()) {?>
            <div class="info-details-event">
                <div>Место проведения еще не определено</div>
            </div>
            <?php
            return;
        } ?>

        <div class="info-details-event">
            <?php
            if ($place->getPlaces()) { ?>
                <div>Место проведения:</div>
                <?php
                foreach ($place->getPlaces() as $p) {
                    echo "<div style='font-weight: normal'>$p</div>";
                }
            }
            if ($place->getAuditories()) { ?>
                <div>Аудитории:</div>
	            <?php
                foreach ($place->getAuditories() as $auditory) {
		            echo "<div style='font-weight: normal'>$auditory</div>";
	            }
            }
            if ($place->getLinks()) { ?>
                <div>Ссылки:</div>
	            <?php
	            foreach ($place->getLinks() as $link) {
		            echo "<div style='font-weight: normal'><a href=\"$link\">$link</a></div>";
	            }
            }
            ?>
        </div>
       <?php
    }

    private function showHeads(array $moders): void
    {
	    if (empty($moders)) {
		    echo "<div class=\"info-details-event\"><div>Руководители мероприятия не указаны</div></div>";
		    return;
	    }

	    echo "<div class=\"info-details-event\"><div>Руководител" . (count($moders) > 1 ? 'и' : 'ь')
            . " мероприятия:</div>";
	    foreach ($moders as $moder) {
		    echo "<div style='font-weight: normal'>"
			    . "{$moder->getFio()}"
                . ($moder->getPosition()
                    ? "<span class=\"posiiton-event\" style=\"color: #333\"> — "
                        . $moder->getPosition()
                        . "</span>"
                    : '')
                . " </div>";
	    }
        echo "</div>";
    }

    private function showDirections(array $directions): void
    {
	    if (!$directions['education'] && !$directions['science']) {
		    return;
	    }

        if ($directions['education']) {?>
            <div class="info-details-event">
                <div>Направление воспитательной работы: </div>
                <div><?= $directions['education']?></div>
            </div>
            <?php
        }
	    if ($directions['science']) { ?>
            <div class="info-details-event">
                <div>Научное направление:</div>
                <div><?= $directions['science']?></div>
            </div>
            <?php
	    }
    }

    private function showPhotos($photos): void
    {
        if (!$photos) { return; } ?>

        <div id="modal" class="modal">
            <span class="close">&times;</span>

            <img class="modal-content" id="img01">

            <div id="caption"></div>
        </div>

        <div class="check-photo-event-wrapper">
            <div class="check-photo-text-event unselectable">
                Посмотреть фотографии с предыдущего мероприятия
            </div>
            <img src="../assets/не актив.svg" />
        </div>
        <div class="gallery">
            <?php foreach ($photos as $photo) { ?>
                <div class="image-event">
                    <img class="image-event-picture" src="<?= $photo->getLink() ?>" />
                    <p class="caption-event" style="display: none">
                        <?= $photo->getName(); ?>
                    </p>
                </div>
            <?php } ?>
        </div>

       <?php
    }

    private function showDocs($docs): void
    {
        if (!$docs) { return; } ?>
        <div class="unselectable" style="font-size: 1.2em; margin-top: 1em;">
            Регламентирующие документы:
        </div>
        <?php
	    foreach ($docs as $doc) {
            echo "<div><a href=\"{$doc->getLink()}\">{$doc->getName()}</a></div>";
        }
    }
}
