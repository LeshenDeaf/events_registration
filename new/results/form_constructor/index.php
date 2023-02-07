<?php


require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php";

if (!$USER->IsAdmin()) {
    echo 'доступ только у администраторов';
    require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php";
}

require_once $_SERVER["DOCUMENT_ROOT"] . '/events_registration/.ht_classes/Vyatsu/Events/User.php';

$autofillNames['students'] = [...array_keys(Vyatsu\Events\User::reformatStudInfo([])), 'parent_fio', 'parent_birthdate'];
$autofillNames['employees'] = array_keys(Vyatsu\Events\User::reformatEmployeeInfo([]));

$APPLICATION->SetTitle('Конструктор форм');

?>
    <link rel="stylesheet" href="/events_registration/new_register.css">
    <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH ?>/assets/css/modal-image.css">

    <link rel="stylesheet" href="/events_registration/new/assets/css/styles.css">
    <link rel="stylesheet" href="/events_registration/new/assets/css/notification.css">
    <link rel="stylesheet" href="/events_registration/new/assets/css/form_constructor.css">

    <script type="text/javascript" src="/account/doc_creator/js/notification.js"></script>
    <script type="module" src="/events_registration/new/assets/js/form_constructor.js"></script>


    <div class="constructor">
        <div class="field_creator">
            <div class="creator_form">
                <div>
                    Type
                    <select placeholder="Type" name="type">
                        <option value="header">header</option>
                        <option value="text">text</option>
                        <option value="password">password</option>
                        <option value="input">input</option>
                        <option value="file">file</option>
                        <option value="checkbox">checkbox</option>
                        <option value="radio">radio</option>
                        <option value="select">select</option>
                    </select>
                </div>
                <div class="omrs-input-group">
                    <label class="omrs-input-underlined">
                        <input name="text" type="text">
                        <span class="omrs-input-label">Text</span>
                    </label>
                </div>
                <div class="omrs-input-group">
                    <label class="omrs-input-underlined">
                        <input name="name" type="text">
                        <span class="omrs-input-label">Name</span>
                    </label>
                </div>
                <div class="omrs-input-group">
                    <label class="omrs-input-underlined">
                        <input name="value" type="text">
                        <span class="omrs-input-label">Value</span>
                    </label>
                </div>
                <div class="omrs-input-group">
                    <label class="omrs-input-underlined">
                        <input name="label" type="text">
                        <span class="omrs-input-label">Label</span>
                    </label>
                </div>
                <div class="omrs-input-group">
                    <div>
                        <input type="checkbox"
                               class="custom-checkbox"
                               name="is_required"
                               id="is_required"
                               value="1"
                        >
                        <label for="is_required">Is required</label>
                    </div>
                </div>
                <div class="omrs-input-group">
                    <div>
                        <input type="checkbox"
                               class="custom-checkbox"
                               name="is_multiple"
                               id="is_multiple"
                               value="1"
                        >
                        <label for="is_multiple">Is multiple</label>
                    </div>
                </div>
            </div>

            <div class="options">
                <button class="add_option">
                    +
                </button>
            </div>

            <div class="autofill_names">
                <div style="flex-basis: 100%; margin-left: .5em; font-size: 18px">Автодополнения для студентов:</div>
                <?php foreach ($autofillNames['students'] as $autofillName) {?>
                    <div class="autofill_name"><?= $autofillName ?></div>
                <?php } ?>
                <div style="flex-basis: 100%; margin-left: .5em; margin-top: 1em; font-size: 18px">Автодополнения для сотрудников:</div>
                <?php foreach ($autofillNames['employees'] as $autofillName) {?>
                    <div class="autofill_name"><?= $autofillName ?></div>
                <?php } ?>
            </div>
        </div>

        <button class="add_field">Добавить</button>


        <button class="copy" title="Копировать">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500">
                <path d="M502.6 70.63l-61.25-61.25C435.4 3.371 427.2 0 418.7 0H255.1c-35.35 0-64 28.66-64 64l.0195 256C192 355.4 220.7 384 256 384h192c35.2 0 64-28.8 64-64V93.25C512 84.77 508.6 76.63 502.6 70.63zM464 320c0 8.836-7.164 16-16 16H255.1c-8.838 0-16-7.164-16-16L239.1 64.13c0-8.836 7.164-16 16-16h128L384 96c0 17.67 14.33 32 32 32h47.1V320zM272 448c0 8.836-7.164 16-16 16H63.1c-8.838 0-16-7.164-16-16L47.98 192.1c0-8.836 7.164-16 16-16H160V128H63.99c-35.35 0-64 28.65-64 64l.0098 256C.002 483.3 28.66 512 64 512h192c35.2 0 64-28.8 64-64v-32h-47.1L272 448z"/>
            </svg>
        </button>

        <div class="constructor_result" style="">
            <div class="form" style=""></div>
            <pre class="json">[]</pre>
        </div>

    </div>
    <div class="alert-notification" style="display: none">
        <div class="logo"></div>
    </div>
<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php";

