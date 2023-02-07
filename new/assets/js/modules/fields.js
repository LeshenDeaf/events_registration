import { makeNotificationText, highlightField, checkFile } from './utils.js';

export const fieldMaker = {
    /**
     * @param {Object} field The title field
     * @param {string} field.text A text of the title itself
     * @returns {`<h2 style="text-align: center">${string}</h2>`}
     */
    header: field => `<h2 class="title-form">${field.text}</h2>`,

    /**
     * @param {Object} field The text field
     * @param {string} field.text A text of paragraph
     * @returns {string} HTML paragraph
     */
    text: field => `<p>${field.text}</p>`,

    /**
     * @param {Object} field The input
     * @param {string} field.label Input label
     * @param {string} field.name Input name
     * @param {string} field.value [optional] Input value
     * @param {boolean} field.is_required [optional] Is input required
     * @returns {`<div class="omrs-input-group"><label class="omrs-input-underlined"><input name="${string}" value="${string}" ${string} type="text"><span class="omrs-input-label">${string}</span></label></div>`}
     */
    input: (field) =>
        `<div class="omrs-input-group"><label class="omrs-input-underlined"><input name="${field.name}" value="${field.value}" ${field.is_required ? 'required' : ''} type="text"><span class="omrs-input-label">${field.label}</span></label></div>`,

    /**
     * @param {Object} field The input
     * @param {string} field.label Input label
     * @param {string} field.name Input name
     * @param {string} field.value [optional] Input value
     * @param {boolean} field.is_required [optional] Is input required
     * @returns {`<div class="omrs-input-group"><label class="omrs-input-underlined"><input name="${string}" value="${string}" ${string} type="password"><span class="omrs-input-label">${string}</span></label></div>`}
     */
    password: field =>
        `<div class="omrs-input-group"><label class="omrs-input-underlined"><input name="${field.name}" value="${field.value}" ${field.is_required ? 'required' : ''} type="password"><span class="omrs-input-label">${field.label}</span></label></div>`,

    /**
     * @param {Object} field The input
     * @param {string} field.label Input label
     * @param {string} field.name Input name
     * @param {boolean} field.is_required [optional] Is input required
     * @param {boolean} field.is_multiple [optional] Is input multiple
     * @returns {`<div class="omrs-input-group">
    <label class="omrs-input-underlined">
        <input name="${string}${string}"
               ${string}
               ${string}
               type="file"
        >
        <span class="omrs-input-label">${string}</span>
    </label>
</div>`}
     */
    file: field => `<div class="omrs-input-group">
    <label class="omrs-input-underlined">
        <input name="${field.name}${field.is_multiple ? '[]' : ''}"
               ${field.is_required ? 'required' : ''}
               ${field.is_multiple ? 'multiple' : ''}
               type="file"
        >
        <span class="omrs-input-label">${field.label}</span>
    </label>
</div>`,

    /**
     * @param {Object} field The checkbox group
     * @param {string} field.label Label of group
     * @param {string} field.name Name of group (input-name). MUST ends "[]"
     * @param {Object[]} field.options Array of checkboxes
     * @param {string} field.options[].value Value of checkbox
     * @param {string} field.options[].label Label of checkbox
     * @param {boolean} field.options[].is_required  Is checkbox required (then user must check checkbox)
     * @returns {string} HTML checkbox group
     */
    checkbox: (field) => {
        let checkboxesStr = `<div class="omrs-input-group">${field.label}<br>`;

        for (const [index, checkbox] of Object.entries(field.options)) {
            checkboxesStr += `<div style="margin: .3em 0;"><input type="checkbox" class="custom-checkbox" name="${field.name}[]" id="${field.name + index}" value="${checkbox.value}" ${checkbox.is_required ? 'required' : ''}> <label for="${field.name + index}">${checkbox.label}</label></div>`;
        }

        return checkboxesStr + '</div>';
    },

    /**
     * @param {Object} field The radio
     * @param {string} field.label Label of group
     * @param {string} field.name Name of group (input-name)
     * @param {boolean} field.is_required Is radio required
     * @param {Object[]} field.options Array of radio buttons
     * @param {string} field.options[].value Value of radio button
     * @param {string} field.options[].label Label of radio button
     * @returns {string} HTML checkbox group
     */
    radio: (field) => {
        let radiosStr = `<div class="omrs-input-group">${field.label}`;
        let isReq = field.is_required;

        for (const [index, radio] of Object.entries(field.options)) {
            radiosStr += `<div><input id="${field.name + index}" name="${field.name}" value="${radio.value}" ${isReq ? 'required' : ''} type="radio" class="custom-radio"><label for="${field.name + index}">${radio.label}</label></div>`
            isReq = false;
        }

        return radiosStr + '</div>';
    },

    /**
     * @param {Object} field The select
     * @param {string} field.label Label of select
     * @param {string} field.name  Name of group (input-name)
     * @param {boolean} field.is_required Is select required
     * @param {boolean} field.is_multiple Is select multiple
     * @param {Object[]} field.options Array of options
     * @param {string} field.options[].value Value of option
     * @param {string} field.options[].label Label of option
     * @returns {string} HTML select group
     */
    select: (field) => {
        let select = `${field.label}<select placeholder="${field.label}" name="${field.name}${field.is_multiple ? '[]' : ''}" ${field.is_required ? 'required' : ''} ${field.is_multiple ? 'multiple' : ''} >`;
        let selected = 'selected';

        for (const [_, option] of Object.entries(field.options)) {
            select += `<option value="${option.value}" ${selected}>${option.label}</option>`;
            selected = '';
        }

        return select;
    }
}

export const formReader = {
    /**
     * @returns null
     */
    header: (field, fd) => null,

    /**
     * @returns null
     */
    text: (field, fd) => null,

    /**
     * @param {Object} field The input
     * @param {string} field.label Input label
     * @param {string} field.name Input name
     * @param {string} field.value [optional] Input value
     * @param {boolean} field.is_required [optional] Is input required
     * @param {FormData} fd
     * @returns {boolean}
     */
    input: (field, fd) => {
        const value = $(`input[name="${field.name}"`).val();

        if (field.is_required && !value) {
            showNotification(
                false, makeNotificationText(`Не заполнено поле "${field.label}"`), 2500
            );

            highlightField(field);

            return false;
        }

        fd.append(field.name, value)
        return true;
    },

    /**
     * @param {Object} field The input
     * @param {string} field.label Input label
     * @param {string} field.name Input name
     * @param {string} field.value [optional] Input value
     * @param {boolean} field.is_required [optional] Is input required
     * @param {FormData} fd
     * @returns {boolean}
     */
    password: (field, fd) => {
        const value = $(`input[name="${field.name}"`).val();

        if (field.is_required && !value) {
            showNotification(
                false, makeNotificationText(`Не заполнено поле "${field.label}"`), 2500
            );

            highlightField(field);

            return false;
        }

        fd.append(field.name, value)
        return true;
    },

    /**
     * @param {Object} field The select
     * @param {string} field.label Label of select
     * @param {string} field.name  Name of group (input-name)
     * @param {boolean} field.is_required Is select required
     * @param {boolean} field.is_multiple Is select multiple
     * @param {FormData} fd
     * @returns {boolean}
     */
    file: (field, fd) => {
        const element = $(`input[name="${field.name}"]`)[0];

        if (field.is_required && element.files.length <= 0) {
            showNotification(
                false, makeNotificationText(`Не заполнено поле "${field.label}"`), 2500
            );

            highlightField(field);

            return false;
        }

        if (element.files.length <= 0) {
            return true;
        }

        if (field.is_multiple) {
            let noError = true;
            element.files.map(file => {
                noError = noError && checkFile(field, file);
                noError && fd.append(field.name + '[]', file);
            });

            return noError;
        }

        const noError = checkFile(field, element.files[0])
        noError && fd.append(field.name, element.files[0]);

        return noError;
    },

    /**
     * @param {Object} field The checkbox group
     * @param {string} field.label Label of group
     * @param {string} field.name Name of group (input-name). MUST ends "[]"
     * @param {Object[]} field.options Array of checkboxes
     * @param {string} field.options[].value Value of checkbox
     * @param {string} field.options[].label Label of checkbox
     * @param {boolean} field.options[].is_required  Is checkbox required (then user must check checkbox)
     * @param {FormData} fd
     * @returns {boolean}
     */
    checkbox: (field, fd) => {
        for (const [index, checkbox] of Object.entries(field.options)) {
            const checkboxInput = $(`#${field.name + index}`);

            if (checkbox.is_required && !checkboxInput.is(':checked')) {
                showNotification(
                    false, makeNotificationText(`Не поставлена галка напротив "${checkbox.label}"`), 2500
                );

                highlightField(field);

                return false;
            }

            fd.append(
                `${field.name}[]`,
                checkboxInput.is(':checked')
                    ? checkboxInput.val()
                    : ''
            )
        }

        return true;
    },

    /**
     * @param {Object} field The radio
     * @param {string} field.label Label of group
     * @param {string} field.name Name of group (input-name)
     * @param {boolean} field.is_required Is radio required
     * @param {Object[]} field.options Array of radio buttons
     * @param {string} field.options[].value Value of radio button
     * @param {string} field.options[].label Label of radio button
     * @param {FormData} fd
     * @returns {boolean}
     */
    radio: (field, fd) => {
        const val = $(`input[name=${field.name}]:checked`).val();

        fd.append(field.name, val);


        if (field.is_required && val === undefined) {
            showNotification(
                false, makeNotificationText(`Ничего не выбрано в "${field.label}"`), 2500
            );

            highlightField(field);

            return false;
        }

        return true;
    },

    /**
     * @param {Object} field The select
     * @param {string} field.label Label of select
     * @param {string} field.name  Name of group (input-name)
     * @param {boolean} field.is_required Is select required
     * @param {boolean} field.is_multiple Is select multiple
     * @param {Object[]} field.options Array of options
     * @param {string} field.options[].value Value of option
     * @param {string} field.options[].label Label of option
     * @param {FormData} fd
     * @returns {boolean}
     */
    select: (field, fd) => {
        let selectedOptions;

        selectedOptions = field.is_multiple
            ? $(`select[name="${field.name}[]"] option:selected`)
            : $(`select[name="${field.name}"] option:selected`);

        if (field.is_required && selectedOptions.length === 0) {
            showNotification(
                false, makeNotificationText(`Ничего не выбрано в "${field.label}"`), 2500
            );

            highlightField(field);

            return false;
        }

        if (field.is_multiple) {
            for (const option of selectedOptions) {
                fd.append(
                    `${field.name}[]`,
                    $(option).val()
                );
            }

            return true;
        }

        fd.append(
            `${field.name}`, $(selectedOptions).eq(0).val()
        );
        return true;
    }
}
