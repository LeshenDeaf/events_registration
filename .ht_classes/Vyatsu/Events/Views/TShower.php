<?php

namespace Vyatsu\Events\Views;

trait TShower
{
	private function showRegisterButton(
        bool $isRegisterNeeded,
        int $elementId,
        bool $isRegistrationAvailable,
        bool $isMustFillForm = true,
        int $oldId = 0
    ): void {
        global $USER;

		if (!$isRegisterNeeded) { return; }

        if ($USER->IsAuthorized()
            || !$USER->IsAuthorized() && $isRegistrationAvailable
        ) { ?>
            <div class="button-record-event
            <?= $isRegistrationAvailable ? '' : 'button-record-event-disabled' ?>
            <?= $isMustFillForm ? " get_form " : "button-register not-fill" ?>"
                 id="<?= $elementId ?>"
            >
                <div>
                    <?= !$isRegistrationAvailable && $oldId !== 1 ? "Ваш номер регистрации: $oldId" : "Записаться"?>
                </div>
                <div class="button-flare"></div>
            </div>
            <?php
        } else { ?>
            <div id="0" class="button-record-event get_auth_form" >
                <div>
                    Авторизоваться
                </div>
                <div class="button-flare"></div>
            </div>
            <?php
        }
	}

    /**
     * @param int $eventId
     * @param array<int> $availableIds
     * @param bool $isModer
     * @return void
     */
    private function showResultsButton(int $eventId, array $availableIds, bool $isModer = false)
    {
        if (!in_array($eventId, $availableIds) && !$isModer) {
            echo '';
            return;
        }

        echo "<div class=\"buttons-event\">
<div class=\"button-record-event get-table\" href=\"/events_registration/new/results/?id=$eventId\">
<div>Результаты</div></div>
</div>";
    }

    private function showFormContainer(): void
    {?>
        <div class="form-container" style="">
            <div class="form-window" style="">

            </div>
        </div>
    <?php
    }

    private function showAlertNotification(): void
    {?>
        <div class="alert-notification" style="display: none">
            <div class="logo"></div>
        </div>
       <?php
    }

    private function includeAll(): void
    {?>
        <link rel="stylesheet" href="/events_registration/new_register.css">
        <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH ?>/assets/css/modal-image.css">

        <link rel="stylesheet" href="/events_registration/new/assets/css/styles.css">
        <link rel="stylesheet" href="/events_registration/new/assets/css/notification.css">

        <script type="module" src="/events_registration/new/assets/js/events_main.js"></script>
    <?php
    }

    private function showSVG(string $text = '', string $head = '', array $contacts = []): void
    {  ?>
        <form class="svg_form" style="display:none">
            <input name="text" type="hidden" value="<?= $text ?>">
            <input name="head" type="hidden" value="<?= $head ?>">
            <?php foreach ($contacts as $contact) { ?>
                <input name="contacts[]" type="hidden" value="<?= $contact ?>">
            <?php } ?>
        </form>
<?php
    }
}
