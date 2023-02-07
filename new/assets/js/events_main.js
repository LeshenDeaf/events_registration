/**
 * <strong>fieldMaker</strong> Создает поля для формы<br>
 * <strong>formReader</strong> При проверке формы их считывает <i>(см. его использования)</i>
 */
import { fieldMaker, formReader } from './modules/fields.js';

/**
 * Предоставляет работу с кэшэм
 */
import * as cache from './modules/cache.js';

/**
 * Позволяет открывать/закрывать картинки
 */
import * as modal from './modules/modal.js';

/**
 * Предоставляет функцию поиска мероприятий
 */
import * as search from './modules/search.js';

/**
 * Подгружает svg картинки
 */
import * as svg from './modules/svg.js';

/**
 * Календарь
 */
// import * as calendar from './modules/calendar.js';

/**
 * Загрузка следующих мероприятий
 */
// import * as loadMore from './modules/load_more.js';

/**
 * Some functions that may be needed
 */
import * as utils from './modules/utils.js';

/** Class representing a consent */
class Consent {
    /**
     * Create a consent
     * @param {int} id id of consent
     * @param {string} label label of consent checkbox
     * @param {string} text text of consent
     */
    constructor (id, label, text)  {
        this._id = +id;
        this._label = label;
        this._text = text;
    }

    get id() {
        return this._id;
    }

    get label() {
        return this._label;
    }

    get text() {
        return this._text;
    }

    static fromJson(json) {
        let parsed = json;

        if (typeof json === 'string') {
            parsed = JSON.parse(json);
        }

        return new Consent(parsed.id, parsed.label, parsed.text);
    }
}

/**
 * Consents grouped by types
 */
class Consents {
    /**
     *
     * @param {Consent[]} adults [optional]
     * @param {Consent[]} children [optional]
     * @param {Consent[]} foreign [optional]
     */
    constructor(
        adults = [],
        children = [],
        foreign = []
    ) {
        this._adults = adults;
        this._children = children;
        this._foreign = foreign;
    }

    /**
     * @return {Consent[]}
     */
    get adults() {
        return this._adults;
    }

    /**
     * @return {Consent[]}
     */
    get children() {
        return this._children;
    }

    /**
     * @return {Consent[]}
     */
    get foreign() {
        return this._foreign;
    }

    get hasAll() {
        return this.adults.length > 0
            && this.children.length > 0
            && this.foreign.length > 0
    }

    /**
     * @return {boolean}
     */
    get areAllEmpty() {
        return !this.adults.length
            && !this.children.length
            && !this.foreign.length
    }

    /**
     * @return {boolean}
     */
    get isOnlyAdults() {
        return this.adults.length > 0
            && !this.children.length
            && !this.foreign.length
    }

    /**
     * @return {boolean}
     */
    get isOnlyForeign() {
        return !this.adults.length
            && !this.children.length
            && this.foreign.length > 0
    }

    /**
     * @return {boolean}
     */
    get hasAdultsAndChildren() {
        return this.adults.length > 0
            && this.children.length > 0;
    }

    /**
     * @return {boolean}
     */
    get hasAdultsAndForeign() {
        return this.adults.length > 0
            && this.foreign.length > 0
    }

    add(type, consent) {
        this[type].push(Consent.fromJson(consent))
    }

    findConsent(id, type = '') {
        const findAmongType = consentGroup => consentGroup.find(
            obj => +obj.id === +id
        );

        if (isNaN(id) || !id || id < 0) {
            return undefined;
        }

        if (type) {
            return findAmongType(this[type]);
        }

        return findAmongType(this.adults)
            || findAmongType(this.children)
            || findAmongType(this.foreign);
    }

}

/**
 * Form
 */
class Form {
    /**
     * @param {int|string} element_id
     * @param {Object[]} form must contain {string} fields[].type
     * @param {Consents} consents
     * @param {number} minAge
     */
    constructor(element_id, form, consents, minAge = 0) {
        this._element_id = +element_id;
        this._form = form;
        this._consents = consents;
        this._minAge = minAge;
    }

    /**
     * @return {int}
     */
    get element_id() {
        return this._element_id;
    }

    /**
     * @return {Object[]} form must contain {string} fields[].type
     */
    get form() {
        return this._form;
    }

    /**
     * @return {Consents}
     */
    get consents() {
        return this._consents;
    }

    /**
     * @return {boolean}
     */
    get minAge() {
        return this._minAge;
    }

    /**
     * Checks if form has age field
     * @return {boolean}
     */
    get hasAge() {
        return this.hasField('age');
    }

    get hasBirthdate() {
        return this.hasField('birthdate');
    }

    get hasForeign() {
        return this.hasField('is_foreign[]')
    }

    get hasParentFio() {
        return this.hasField('parent_fio');
    }

    get hasParentBirthDate() {
        return this.hasField('parent_birthdate');
    }

    hasField(fieldName) {
        return this._form.some(field => field.name === fieldName)
    }

    static fromJson(json) {
        let parsed = json;

        if (typeof json === 'string') {
            parsed = JSON.parse(json);
        }

        return new Form(parsed.element_id, parsed.form, parsed.consents, parsed.minAge ?? 0);
    }
}


/**
 * Check form before sending
 * @property {Form} form
 * @property {FormData} fd
 */
class Checker {
    /**
     * @param {FormData} fd
     * @param {Form} form
     */
    constructor(fd, form) {
        this._fd = fd;
        this._form = form;
    }

    /**
     * @return {FormData}
     */
    get fd() {
        return this._fd;
    }

    /**
     * @return {Form}
     */
    get form() {
        return this._form;
    }

    /**
     * @return {null|number}
     */
    static get age() {
        return Checker.getAgeFromInputs({age: 'age', birthdate: 'birthdate'});
    }

    static get parentAge() {
        return Checker.getAgeFromInputs({
            age: 'parent_age', birthdate: 'parent_birthdate'
        });
    }

    static get isForeign() {
        const foreignInput = utils.form.window.find('input[name="is_foreign[]"');

        return !foreignInput.length
            ? null
            : foreignInput.is(':checked');
    }

    /**
     * @param age - age input name
     * @param birthdate - birthdate input name
     * @return null|number
     */
    static getAgeFromInputs({age, birthdate}) {
        const ageInput = utils.form.window.find(`input[name="${age}"]`);
        const birthDateInput = utils.form.window.find(`input[name="${birthdate}"]`);

        if ((!ageInput.length || isNaN(ageInput.val()))
            && (!birthDateInput.length || isNaN(utils.calculateAge(birthDateInput.val())))
        ) {
            return null;
        }

        if (ageInput.length) {
            return +ageInput.val();
        }
        if (birthDateInput){
            return utils.calculateAge(birthDateInput.val());
        }
    }

    check() {
        if (!this.checkForm()) {
            return null;
        }

        if (!this.checkConsents()) {
            showNotification(
                false,
                utils.makeNotificationText(
                    'Невозможно записаться без согласий на обработку <span title="Персональные Данные">ПД</span>'
                ),
                4000
            );
            return null;
        }

        return this.fd;
    }

    checkForm() {
        if (Checker.checkUserAge(this.form.minAge) === false
            || Checker.checkEmailInput() === false
            || Checker.checkParentAge() === false
        ) {
            return null;
        }

        for (const [index, field] of Object.entries(this.form.form)) {
            const addedSuccessfully
                = formReader[field['type']](field, this.fd);

            if (!addedSuccessfully && addedSuccessfully !== null) {
                return null;
            }
        }

        return true;
    }

    checkConsents() {
        const consents = this.form.consents;

        const inputs = (type) => ({
            all: $(`.consents .${type} input`),
            checked: $(`.consents .${type} input:checked`)
        })

        const areChecked = type => {
            const inputsOfType = inputs(type);
            return inputsOfType.all.length === inputsOfType.checked.length;
        }

        const addConsents = type => {
            const fd = this.fd;

            inputs(type).all.each(function () {
                fd.append($(this).attr('name'), $(this).val())
            });

            this._fd = fd;
        }

        const addAndCheck = type => {
            addConsents(type);

            return areChecked(type);
        }

        if (Checker.checkUserAge() === false
            || Checker.checkParentAge() === false
        ) {
            return null;
        }

        if (consents.areAllEmpty) {
            return true;
        }

        if (consents.isOnlyAdults) {
            return addAndCheck('adults');
        }

        if (consents.isOnlyForeign) {
            return addAndCheck('foreign');
        }

        if (consents.hasAll) {
            if (Checker.isForeign) {
                return addAndCheck('foreign');
            }

            return Checker.age > 17
                ? addAndCheck('adults')
                : addAndCheck('children');
        }

        if (consents.hasAdultsAndChildren) {
            return Checker.age > 17
                ? addAndCheck('adults')
                : addAndCheck('children');
        }

        if (consents.hasAdultsAndForeign) {
            return Checker.isForeign
                ? addAndCheck('foreign')
                : addAndCheck('adults');
        }
    }

    static checkAge(age, minAge = 0, inputName = 'age', customMessage = '') {
        if (age > 4 && age < 131 && !isNaN(age)) {
            if (minAge < age) {
                return true
            }
        }

        let message = 'Установлен недопустимый возраст';
        if (age < minAge) {
            message = `Мероприятие доступно лишь с ${minAge} лет`;
        }

        showNotification(
            false,
            utils.makeNotificationText(
                customMessage.trim().length > 0 ? customMessage.trim() : message
            ),
            2000
        );

        utils.highlightField({ name: inputName });

        return false;
    }

    /**
     * Checks age in either birthdate or age inputs. Returns false if none of inputs presented
     * @param {number}minAge
     * @param {{age: string, birthdate: string}}inputNames
     * @param {string}customErrorMessage
     * @param {boolean}onlyRequired=true
     * @return {null|boolean}
     */
    static checkAgeInputs(minAge, inputNames, customErrorMessage = '', onlyRequired = false) {
        const birthdateInput = utils.form.window.find(`input[name="${inputNames.birthdate}"]`);

        if (birthdateInput.length) {
            const age = utils.calculateAge(birthdateInput.val());

            if (onlyRequired && !birthdateInput.prop('required')) {
                return null;
            }

            return Checker.checkAge(age, minAge, inputNames.birthdate, customErrorMessage);
        }

        const ageInput = utils.form.window.find(`input[name="${inputNames.age}"]`);

        if (!ageInput.length
            || onlyRequired && !ageInput.prop('required')
        ) {
            return null;
        }

        return Checker.checkAge(ageInput.val(), minAge, inputNames.age, customErrorMessage);
    }

    static checkUserAge(minAge = 0) {
        const inputNames = { birthdate: 'birthdate', age: 'age', }

        return Checker.checkAgeInputs(minAge, inputNames)
    }

    static checkParentAge() {
        const inputNames = {
            birthdate: 'parent_birthdate',
            age: 'parent_age',
        }
        const errorMessage = 'Законный представитель должен быть совершеннолетним';

        return Checker.checkAgeInputs(18, inputNames, errorMessage, true);
    }

    /**
     * @param {string} email
     * @return {boolean}
     */
    static checkEmail(email) {

        if (String(email)
            .toLowerCase()
            .match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            )
        ) {
            return true;
        }
        showNotification(
            false,
            utils.makeNotificationText('Введен некорректный email'),
            2000
        );

        utils.highlightField({ name: "email" });

        return false;
    }

    /**
     * @param {string} email
     * @return {boolean|null}
     */
    static checkEmailInput(email = '') {
        if (email) {
            Checker.checkEmail(email);
        }

        const emailInput = utils.form.window.find('input[name="email"]');

        if (!emailInput.length) {
            return null;
        }

        return Checker.checkEmail(emailInput.val());
    }
}

class ConsentShower
{
    /**
     * @param {Form} form
     */
    constructor(form) {
        this._form = form;
    }

    /**
     * @return {Form}
     */
    get form() {
        return this._form;
    }

    work () {
        const showOnlyNeeded = () => {
            consentsDiv.every.hide();

            if (Checker.isForeign) {
                consentsDiv.foreign.show();
                return;
            }

            if (Checker.checkUserAge() === false) {
                return;
            }

            const age = Checker.age;
            let parentFioRequired;

            if (age > 17 || age === null) {
                consentsDiv.adults.show();
                parentFioRequired = false;
            } else {
                consentsDiv.children.show();
                parentFioRequired = true;
            }

            if (this.form.hasParentFio) {
                utils.form.window.find('input[name="parent_fio"]')
                    .prop('required', parentFioRequired)
                    .prop('disabled', !parentFioRequired);
            }
            if (this.form.hasParentBirthDate) {
                utils.form.window.find('input[name="parent_birthdate"]')
                    .prop('required', parentFioRequired)
                    .prop('disabled', !parentFioRequired);
            }
            if (this.form.hasField('parent_age')) {
                utils.form.window.find('input[name="parent_age"]')
                    .prop('required', parentFioRequired)
                    .prop('disabled', !parentFioRequired);
            }

            if (Checker.checkParentAge() === false) {
                consentsDiv.every.hide();
                return;
            }
        }

        const consentsDiv = {
            all: $('.consents'),
            every: $('.consents .adults, .consents .children, .consents .foreign'),
            adults: $('.consents .adults'),
            children: $('.consents .children'),
            foreign: $('.consents .foreign'),
        }

        const consents = this.form.consents;

        if (consents.areAllEmpty
            || consents.isOnlyForeign
            || consents.isOnlyAdults
        ) {
            return;
        }

        if (!consents.children.length && utils.xor(consents.adults.length > 0, consents.foreign.length > 0)
            || !(consents.hasAdultsAndChildren && (this.form.hasAge || this.form.hasBirthdate) && !consents.foreign.length)
        ) {
            consentsDiv.every.show();
        } else {
            consentsDiv.every.hide();
        }

        if (this.form.hasAge || this.form.hasBirthdate) {
            showOnlyNeeded();
            // if (this.form.hasParentBirthDate || this.form.hasField('parent_age')) {
            //     showOnlyNeeded();
            // }
        }

        if (this.form.hasForeign) {
            showOnlyNeeded();
        }

        utils.form.window.find('input[name="age"]').on('change', showOnlyNeeded);
        utils.form.window.find('input[name="birthdate"]').on('change', showOnlyNeeded);
        utils.form.window.find('input[name="parent_age"]').on('change', showOnlyNeeded);
        utils.form.window.find('input[name="parent_birthdate"]').on('change', showOnlyNeeded);
        utils.form.window.find('input[name="is_foreign[]"]').on('change', showOnlyNeeded);
    }
}

/**
 *
 * @param {Object} response Response from server
 * @param {String} response.status Status of the response
 * @param {Object[]} response.form The form
 * @param {Consents} response.consents Consents of the form
 * @param {HTMLElement} button
 * @param elementId
 */
const formReceived = function (response, elementId, button) {
    /**
     * @type {Consents}
     */
    const consents = new Consents();

    for (const [consentType, consentGroup] of Object.entries(response.consents)) {
        for (const consent of consentGroup) {
            consents.add(consentType, consent)
        }
    }

    const createdForm = new Form(
        elementId,
        response.form,
        consents,
        response.min_age ?? 0
    );

    cache.addToCache(createdForm);

    showForm(createdForm);
    $(button).find('div i').remove();
}

const getForm = function () {
    if ($(this).hasClass("button-record-event-disabled")) {
        return;
    }

    const elementId = $(this).attr('id');

    const cachedForm = cache.findCachedForm(elementId);

    const button = $(this);

    if (cachedForm) {
        showForm(cachedForm);
        return;
    }

    utils.addSpinner(button);

    const fd = new FormData();
    fd.append('id', elementId);

    $.ajax({
        type: 'POST',
        url: '/events_registration/new/ajax/get_event_form.php',
        data: fd,
        processData: false,
        contentType: false,
        success: response => formReceived(response, elementId, button),
        error: response => {
            utils.removeSpinner(button);
            if (response.status) {
                makeDisabled(elementId);
            }

            response = JSON.parse(response.responseText);
            showNotification(
                false,
                response.message ?? utils.makeNotificationText(
                    'Обратитесь с этой ошибкой к <a href="https://new.vyatsu.ru/account/notifications/?ELEMENT_ID=789249"> администратору </a>'
                ),
                4000
            );
        }
    });
}


const getAuthForm = function () {
    if ($(this).hasClass("button-record-event-disabled")) {
        return;
    }

    const cachedForm = cache.findCachedForm(0);
    const button = $(this);

    if (cachedForm) {
        showForm(cachedForm);
        return;
    }

    utils.addSpinner(button)

    $.ajax({
        type: 'GET',
        url: '/events_registration/new/ajax/get_auth_form.php',
        processData: false,
        contentType: false,
        success: response => formReceived(response, 0, button),
        error: response => {
            utils.removeSpinner(button);

            if (response.status) {
                makeDisabled(0);
            }

            response = JSON.parse(response.responseText);
            showNotification(
                false,
                response.message ?? utils.makeNotificationText('Обратитесь с этой ошибкой к <a href="https://new.vyatsu.ru/account/notifications/?ELEMENT_ID=789249"> администратору </a>'),
                4000
            );
        }
    });
}

/**
 * Renders the provided form
 * @param {Form} formObj
 */
const showForm = function (formObj) {
    $('body').css('overflow', 'hidden');

    utils.form.container.fadeIn(100);
    utils.form.window.html('');

    for (const [index, field] of Object.entries(formObj.form)) {
        if (!field.type || fieldMaker[field.type] === undefined) continue;

        if (field.type === 'header' || field.type === 'text') {
            utils.form.window.append(
                '<div class="omrs-input-group clear_span">'
                + fieldMaker[field.type](field)
                + '</div>'
            );
            continue;
        }

        utils.form.window.append(
            '<div class="omrs-input-group">'
            + fieldMaker[field.type](field)
            + '</div>'
        );
    }

    utils.renderConsents(formObj.consents, formObj.hasAge, formObj.hasForeign, formObj.element_id === 0);

    utils.form.window.append(`<div class="button-record-event button-register clear_span" id="${formObj.element_id}"><div>${formObj.element_id === 0 ? 'Авторизоваться' : 'Записаться'}</div><div class="button-flare"></div></div>`);

    if (formObj.hasForeign || formObj.hasAge || formObj.hasBirthdate) {
        const cs = new ConsentShower(formObj);
        cs.work();
    }

    $('input[name="phone"]').inputmask("+9\(9{3}\)9{3}-9{4}")
}

const hideForm = () => {
    utils.form.container.fadeOut({duration: 400,});
    $('body').css('overflow', 'auto');
}


/**
 * @param {int} id if of event
 * @param {boolean} mustFillForm is form empty and should not be filled
 */
const generateFormData = function (id, mustFillForm) {
    const form = cache.findCachedForm(id);

    if (!form && mustFillForm) {
        showNotification(
            false,
            utils.makeNotificationText(
                'Невозможно отправить форму<br>Обратитесь с этой ошибкой к <a href="https://new.vyatsu.ru/account/notifications/?ELEMENT_ID=789249"> администратору </a>'
            ),
            4000
        );
        return null;
    }

    if (mustFillForm
        && !$('#consents_pd').is(':checked')
        && id !== '0'
    ) {
        showNotification(
            false,
            utils.makeNotificationText('Не поставлена галка о согласии с обработкой ПД'),
            4000
        );
        return null;
    }

    const fd = new FormData();
    fd.append('id', `${id}`);

    if (!mustFillForm) {
        return fd;
    }

    const checker = new Checker(fd, form);

    return checker.check();
}

const uploadedSuccessfully = function (response, elementId, button) {
    utils.removeSpinner(button);

    utils.showNotification(
        true, utils.makeNotificationText(response.message), 0
    );
    hideForm();

    makeDisabled(elementId, response.payload);

    if (response.refresh) {
        location.reload();
    }
}

const upload = function () {
    const elementId = $(this).attr('id');
    const button = $(this);

    const fd = generateFormData(elementId, !$(this).hasClass("not-fill"));

    if (fd === null) {
        return;
    }

    utils.addSpinner(button);

    $.ajax({
        type: 'POST',
        url: '/events_registration/new/ajax/event_register.php',
        data: fd,
        processData: false,
        contentType: false,
        success: response => uploadedSuccessfully(response, elementId, button),
        error: response => {
            utils.removeSpinner(button);

            try {
                response = JSON.parse(response.responseText);
            } catch (e) {
                showNotification(
                    false,
                    '<div class="logo"></div> ' +
                    '<div class="notif_text">Обратитесь с этой ошибкой к <a href="https://new.vyatsu.ru/account/notifications/?ELEMENT_ID=789249"> администратору </a></div>',
                    4000
                );

                return;
            }

            showNotification(
                false,
                response.message ?? '<div class="logo"></div> ' +
                '<div class="notif_text">Обратитесь с этой ошибкой к <a href="https://new.vyatsu.ru/account/notifications/?ELEMENT_ID=789249"> администратору </a></div>',
                4000
            );

            utils.highlightField(response.payload)
        }
    });
}

const makeDisabled = function (elementId, regId = 0) {
    const buttons = {
        get_form: $("#" + elementId + '.get_form' ).eq(0),
        not_fill: $("#" + elementId + '.not-fill' ).eq(0)
    }
    const disabledClass = 'button-record-event-disabled';

    buttons.get_form.addClass(disabledClass);
    buttons.not_fill.addClass(disabledClass);

    if (regId === 0) {
        return;
    }

    const regInfo = `Ваш номер регистрации: ${regId}`;

    buttons.get_form.find('div').eq(0).html(regInfo);
    buttons.not_fill.find('div').eq(0).html(regInfo);
}

const showConsentPopup = function () {
    const makeButton = function (classes, text) {
        return `<div class="${classes}">` +
            '<div>' + text + '</div>' +
            '<div class="button-flare"></div>' +
            '</div>'
    }

    const agree = function (consentCheckbox) {
        return () => {
            consentCheckbox.prop('checked', true);
            hideConsentPopup();
        }
    }

    const consentCheckbox = $(this).find('input');
    if (consentCheckbox.prop('name') === 'pd') {
        return;
    }

    const consentId = consentCheckbox.val();

    consentCheckbox.prop('checked', false);

    const eventId   = $('.button-register').attr('id');
    const consentType = $(this).parent().prop('className');

    /** @type {Consent} */
    const consent = cache.findConsent(eventId, consentId, consentType);

    const consentDiv = {
        window: $(".consent-window"),
        container: $(".consent-container")
    }

    consentDiv.window.html('');
    consentDiv.window.append(
        `<h2 class="title-form">${consent.label}</h2><div class="consent-text"><p>${consent.text}</p></div>`
    )
    consentDiv.window.append(
        '<div class="button-group">'
        + makeButton('button-record-event', 'Принимаю')
        + makeButton('button-record-event-negative', 'Не принимаю')
        + '</div>'
    )

    $('.button-record-event').on('click', agree(consentCheckbox));

    $('body').css('overflow', 'hidden');

    consentDiv.container.fadeIn(100);
}

const hideConsentPopup = function () {
    const consentDiv = {
        window: $(".consent-window"),
        container: $(".consent-container")
    }

    consentDiv.window.html('');

    if (utils.form.container.is(':hidden')) {
        $('body').css('overflow', 'auto');
    }

    consentDiv.container.fadeOut(100);
}

// $(".button-description-event").on('mousedown', utils.jumpToEventPage);

$(".get-table").on('mousedown', utils.jumpToEventPage);

$(".make-excel").on('mousedown', utils.jumpToEventPage);

$(document).on('click', '.get_form', getForm)
$(document).on('click', '.get_auth_form', getAuthForm)
$(document).on('mousedown', '.button-description-event', utils.jumpToEventPage)
// $(".get_form").on('click', getForm);

// $(".get_auth_form").on('click', getAuthForm);

$(document).on('click', '.consent-checkbox', showConsentPopup)

$(document).on('click', '.consent-window .button-record-event-negative', hideConsentPopup);

$(document).on('click', '.button-register', upload)

$(".check-photo-event-wrapper").on('click', utils.rotateCheckMark);

$(document).mousedown(function (e) {
    const container = utils.form.container;
    const alertNotification = $('.alert-notification');

    if (container.has(e.target).length === 0
        && alertNotification.eq(0).has(e.target).length === 0
        && alertNotification[0] !== e.target
        && $(".form-window").is(":visible")
        && e.which === 1
    ){
        hideForm();
    }
});

$(document).on("focus", "input[name*='date']", function() {
    $(this).mask("99.99.9999", {reverse: true});
});

