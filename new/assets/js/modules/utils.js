(function($){
    $.fn.extend({
        addTemporaryClass: function(className, duration) {
            const elements = this;

            setTimeout(function() {
                elements.removeClass(className);
            }, duration);

            return this.each(function() {
                $(this).addClass(className);
            });
        }
    });
})(jQuery);

export const form = {
    window: $(".form-window"),
    container: $(".form-container")
};

export const highlightField = function (field) {
    let input;

    if (field.is_multiple || field.type === 'checkbox') {
        input = $(`input[name="${field.name}[]"]`)
    } else {
        input = $(`input[name="${field.name}"]`)
    }


    if (field.type === 'checkbox' || field.type === 'radio') {
        input.eq(0).parents('.omrs-input-group')
            .eq(0).addTemporaryClass("highlighted", 1000);
    }
    input.each(function () {
        $(this).addTemporaryClass("highlighted", 1000);
    });
}

/**
 * Decorates text for using in notification
 * @param {string} text may contain html
 * @return {`<div class="logo"></div><div class="notif_text">${string}</div>`}
 */
export const makeNotificationText = text =>
    `<div class="logo"></div><div class="notif_text">${text}</div><div class="close"><i class="fa fa-times" aria-hidden="true"></i></div>`;

const notifElement = $('.alert-notification').eq(0);

notifElement.on('click', '.close', () => notifElement.hide(200));

export const showNotification = (isSuccess, text, time = 2500) => {
    const notification = notifElement;

    const chooseNotificationClass = isSuccess => {
        notification.removeClass(isSuccess ? 'notification-error' : 'notification-success')

        const newClass = isSuccess ? 'notification-success' : 'notification-error';
        if (!notification.hasClass(newClass)) {
            notification.addClass(newClass);
        }
    }

    notification.html(text);

    chooseNotificationClass(isSuccess);

    notification.show(200);

    if (time !== 0) {
        setTimeout(() => {
            notification.hide(200);
        }, time)
    }
}

/**
 * @param {Consents} consents
 * @param {boolean} hasAge
 * @param {boolean} hasForeign
 */
export const renderConsents = function (consents, hasAge, hasForeign, isAuthForm = false) {
    if (isAuthForm) {
        form.window.append(`<div class="consents clear_span"><div class="consent-container"><div class="consent-window"></div></div></div`);
        return;
    }
    if (consents.areAllEmpty) {
        form.window.append(`<div class="consents clear_span">`
            + '<div class="consent-checkbox"><input type="checkbox" class="custom-checkbox" name="pd" id="consents_pd" value="1" required> <label for="consents_pd"> <a href="https://www.vyatsu.ru/files/agreement/%D0%9F%D0%BE%D0%BB%D0%B8%D1%82%D0%B8%D0%BA%D0%B0%20%D0%BE%D0%B1%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D0%BA%D0%B8%20%D0%BF%D0%B5%D1%80%D1%81%D0%BE%D0%BD%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D1%85%20%D0%B4%D0%B0%D0%BD%D0%BD%D1%8B%D1%85.pdf" target="_blank">Согласен(а) с политикой персональных данных (ПД)</a></label></div>'
            + '<div class="consent-container"><div class="consent-window"></div></div>'
            + '</div>'
        );
        return;
    }

    form.window.append(`<div class="consents clear_span">`
        + '<div class="consent-checkbox"><input type="checkbox" class="custom-checkbox" name="pd" id="consents_pd" value="1" required> <label for="consents_pd"> <a href="https://www.vyatsu.ru/files/agreement/%D0%9F%D0%BE%D0%BB%D0%B8%D1%82%D0%B8%D0%BA%D0%B0%20%D0%BE%D0%B1%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D0%BA%D0%B8%20%D0%BF%D0%B5%D1%80%D1%81%D0%BE%D0%BD%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D1%85%20%D0%B4%D0%B0%D0%BD%D0%BD%D1%8B%D1%85.pdf" target="_blank">Согласен(а) с политикой персональных данных (ПД)</a></label></div>'
        + implodeConsents(consents.adults, 'adults')
        + implodeConsents(consents.children, 'children')
        + implodeConsents(consents.foreign, 'foreign')
        + '<div class="consent-container"><div class="consent-window"></div></div>'
        + '</div>'
    );
}

export const rotateCheckMark = function () {
    $(this).find("img").toggleClass("rotate");

    $(this).next(".gallery").slideToggle("250");
}

/**
 * Element must have "href" attr.
 * After applying this function on "mousedown" to an element is acting like "a" tag
 * @param e
 */
export const jumpToEventPage = function (e) {
    if (e.which === 1) {
        window.location = $(this).attr('href');
    }
    if (e.which === 2) {
        window.open($(this).attr('href'), '_blank');
    }
}

export const addSpinner = function (button) {
    button.find('div').eq(0).prepend('<i class="fa fa-spinner fa-spin"></i>');
}

export const removeSpinner = function (button) {
    button.find('div i').remove();
}

export const checkFile = (field, file) => {
    const allowedExtensions =
        /(\.doc|\.docx|\.odt|\.pdf|\.tex|\.txt|\.rtf|\.png)$/i;

    if (!allowedExtensions.exec(file.name)) {
        showNotification(
            false, makeNotificationText(`У файла "${file.name}" в поле "${field.label}" недопустимое расширение`), 2500
        );

        highlightField(field);

        return false;
    }
    if (file.size > 4194304) {
        showNotification(false, `Файл "${file.name}" превысил максимально допустимый размер файла 4 МБ`);

        highlightField(field);

        return false;
    }

    return true;
}

/**
 * @param {Consent[]} consentGroup
 * @param {string} groupName adults|children|foreign
 * @return {string}
 */
const implodeConsents = function (consentGroup, groupName) {
    let group = `<div class="${groupName}">`;
    consentGroup.forEach((consent, index) => {
        group += `<div class="consent-checkbox"><input type="checkbox" class="custom-checkbox" name="consents[]" id="consents_${groupName}${index}" value="${consent.id}" required> <label for="consents_${groupName}${index}">${consent.label}</label></div>`
    })

    return group + '</div>';
}

export const calculateAge = date => {
    date = date.split('.');

    return new Date(
        Date.now() - new Date(`${date[2]}/${date[1]}/${date[0]}`).getTime()
    ).getUTCFullYear() - 1970;
}

export const xor = (a, b) => (a && !b) || (!a && b);
