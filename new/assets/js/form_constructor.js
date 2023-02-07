import {fieldMaker, formReader} from './modules/fields.js';
import * as utils from './modules/utils.js';


$(document).on("focus", 'input[name*="date"]', function() {
    $(this).mask("99.99.9999", {reverse: true});
});

let json = [];

const form = $('.form');
const jsonEl = $('.json');
const creatingForm = $('.field_creator');
const inputsWithOptions = ['checkbox', 'radio', 'select'];

const inputs = {
    type: creatingForm.find('select[name="type"]'),
    text: creatingForm.find('input[name="text"]'),
    name: creatingForm.find('input[name="name"]'),
    value: creatingForm.find('input[name="value"]'),
    label: creatingForm.find('input[name="label"]'),
    is_required: creatingForm.find('input[name="is_required"]'),
    is_multiple: creatingForm.find('input[name="is_multiple"]'),
};

const availableInputs = {
    header: ['text'],
    text: ['text'],
    input: ['label', 'name', 'value', 'is_required'],
    password: ['label', 'name', 'value', 'is_required'],
    file: ['label', 'name', 'is_required', 'is_multiple'],
    checkbox: ['label', 'name',/* 'options'*/],
    radio: ['label', 'name', 'is_required',/* 'options'*/],
    select: ['label', 'name', 'is_required', 'is_multiple'],
}

const types = {
    header: () => ({text: inputs.text.val()?.trim()}),
    text: () => ({text: inputs.text.val()?.trim()}),
    input: () => ({
        label: inputs.label.val()?.trim(),
        name: inputs.name.val()?.trim(),
        value: inputs.value.val()?.trim(),
        is_required: inputs.is_required.is(':checked'),
    }),
    password: () => ({
        label: inputs.label.val()?.trim(),
        name: inputs.name.val()?.trim(),
        value: inputs.value.val()?.trim(),
        is_required: inputs.is_required.is(':checked'),
    }),
    file: () => ({
        label: inputs.label.val()?.trim(),
        name: inputs.name.val()?.trim(),
        is_required: inputs.is_required.is(':checked'),
        is_multiple: inputs.is_multiple.is(':checked'),
    }),
    checkbox: () => ({
        label: inputs.label.val()?.trim(),
        name: inputs.name.val()?.trim(),
        options: getOptions(),
    }),
    radio: () => ({
        label: inputs.label.val()?.trim(),
        name: inputs.name.val()?.trim(),
        is_required: inputs.is_required.is(':checked'),
        options: getOptions(),
    }),
    select: () => ({
        label: inputs.label.val()?.trim(),
        name: inputs.name.val()?.trim(),
        is_required: inputs.is_required.is(':checked'),
        is_multiple: inputs.is_multiple.is(':checked'),
        options: getOptions(),
    })
}

const makeInput = () => {
    const type = inputs.type.val();
    return {type, ...types[type]()};
}

const clearInputs = () => {
    inputs.name.val('');
    inputs.text.val('');
    inputs.value.val('');
    inputs.label.val('');
    inputs.is_required.prop('checked', false);
    inputs.is_multiple.prop('checked', false);
}

const updateJson = () => {
    jsonEl.html(JSON.stringify(json, null, 2));
}

function getRandomInt(max) {
    return Math.floor(Math.random() * max);
}

const addOption = function (type) {
    const index = getRandomInt(1000);

    const label = `<div class="omrs-input-group">
                    <label class="omrs-input-underlined">
                        <input name="option_label[${index}]" type="text">
                        <span class="omrs-input-label">Option label</span>
                    </label>
                </div>`;
    const value = `<div class="omrs-input-group">
                    <label class="omrs-input-underlined">
                        <input name="option_value[${index}]" type="text">
                        <span class="omrs-input-label">Option value</span>
                    </label>
                </div>`;
    const isReq = `<div>
                    <input type="checkbox"
                           class="custom-checkbox"
                           name="option_is_required[${index}]"
                           id="option_is_required[${index}]"
                           value="1"
                    >
                    <label for="option_is_required[${index}]">Option is required</label>
                </div>`;

    $('.options').append(`<div class="option draggable" draggable="true"><button class="remove_option"></button><div>${label}${value}${type === 'radio' || type === 'select' ? '' : isReq}</div></div>`);
}

const getOptions = function () {
    const options = [];

    $('.options .option').each(function () {
        const option = $(this);

        options.push({
            label: option.find('input[name^="option_label"]').val().trim(),
            value: option.find('input[name^="option_value"]').val().trim(),
            is_required: option.find('input[type="checkbox"]').is(':checked')
        })
    });

    return options;
}

const changeInputs = function () {
    const type = $(this).val();

    for (const [_, input] of Object.entries(inputs)) {
        input.parent().hide();
    }

    inputs.type.parent().show();
    availableInputs[type].map(inputType => {
        if (inputType === 'options') {
            return;
        }

        inputs[inputType].parent().show();
    });

    if (type === 'input') {
        $('.autofill_names').show()
    } else {
        $('.autofill_names').hide()
    }

    if (inputsWithOptions.includes(type)) {
        $('.options').show();
    } else {
        $('.options').hide();
    }
}

/**
 * @param input
 * @returns {boolean}
 */
const checkInput = input => {
    console.log(input);
    if (input.type === 'text' || input.type === 'header') {
        if (!input.text) {
            showNotification(
                false,
                utils.makeNotificationText('Не заполнено поле "text"'),
                2000
            );
            return false;
        }
        return true
    }

    if (!input.name) {
        showNotification(
            false,
            utils.makeNotificationText('Не заполнено поле "name"'),
            2000
        );
        return false;
    }

    if (!inputsWithOptions.includes(input.type)) {
        return true;
    }

    if (input.options.length <= 0) {
        showNotification(
            false,
            utils.makeNotificationText(`Не добавлены варианты ответа для поля с типом "${input.type}"`),
            2000
        );
        return false;
    }

    let errorField = '';

    input.options.map(option => {
        if (!option.value) {
            errorField = 'value';
        }
    })

    if (errorField) {
        showNotification(
            false,
            utils.makeNotificationText(`Не заполнено поле "${errorField}" у варианта ответа`),
            2000
        );
        return false;
    }

    return true
}

changeInputs.call(inputs.type);
inputs.type.on('change', changeInputs);

$('.add_field').on('click', function () {
    const input = makeInput();

    if (!checkInput(input)) { return; }

    form.append(`
<div style="position: relative" class="draggable" draggable="true">
    <button class="remove_input"></button>
    <div>${fieldMaker[input.type](input)}</div>
</div>`);

    clearInputs();
    $('.options').html(`<button class="add_option"
                    style="padding: .2em 1em; background: #4080F5; color: white; font-size: 24px; border-radius: 10px"
            >
                +
            </button>`);
    json.push(input);
    updateJson();
})

$('.copy').on('click', function () {
    navigator.clipboard.writeText(JSON.stringify(json)).then(function () {
        console.log('Async: Copying to clipboard was successful!');
    }, function (err) {
        console.error('Async: Could not copy text: ', err);
    });
});

const removeSth = function (e) {
    e.preventDefault();
    $(this).parent().fadeOut(100, () => $(this).parent().remove());
}

$('.options').on('click', '.remove_option', removeSth);

form.on('click', '.remove_input', function (e) {
    removeSth.call(this, e);
    json.splice($(this).parent().index(), 1);
    updateJson();
});

$(document).on('click', '.add_option', () => addOption(inputs.type.val()))

const getDragAfterElement = (container, y) => {
    const draggableElements = $(container).find('.draggable:not(.dragging)').toArray();

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2

        if (offset < 0 && offset > closest.offset) {
            return {offset, element: child};
        } else {
            return closest;
        }
    }, {offset: Number.NEGATIVE_INFINITY}).element;
}


let oldIndex = 0;
$(document).on('dragstart', '.draggable', function () {
    console.log('start');
    $(this).addClass('dragging');
    oldIndex = $(this).index();
})
$(document).on('dragend', '.draggable', function () {
    $(this).removeClass('dragging');

    if ($(this).parents('.form')) {
        json.splice(
            $(this).index(),
            0,
            json.splice(oldIndex, 1)[0]
        )
        updateJson();
    }
})
const containers = document.querySelectorAll('.options, .form');

const dragOver = (e, container) => {
    e.preventDefault();
    const draggable = $('.dragging')[0];
    const afterElement = getDragAfterElement(container, e.clientY);

    if (!afterElement) {
        container.appendChild(draggable);
        return;
    }

    container.insertBefore(draggable, afterElement);
};

containers.forEach(container => {
    container.addEventListener('dragover', (e) => dragOver(e, container))
});

$('.autofill_name').on('click', function () {
    inputs.name.val($(this).text());
})
