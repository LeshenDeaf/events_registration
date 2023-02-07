/**
 * Some functions that may be needed
 */
import * as utils from './utils.js';
import * as svg from './svg.js';
import * as calendar from './calendar.js';

const container = $('.body-events').eq(0);
let allLoaded = false;

const setIsLoaded = (isLoaded) => {
    allLoaded = isLoaded;
    allLoaded ? $('.load_more').hide() : $('.load_more').show();
}


let page = 1;
const searchInput = $('input[name="events-search_bar"]').eq(0);

/**
 * @param {Object} event
 * @param {number} event.id
 * @param {string} event.name
 * @param {string} event.head_unit
 * @param {string[]} event.contacts
 * @param {string} event.desc
 * @param {Object} event.button
 * @param {boolean} event.button.auth
 * @param {boolean} event.button.is_shown
 * @param {string} event.button.classes
 * @param {string} event.button.content
 * @returns {Promise<string>}
 */
const makeEventElement = async (event) => {
    let data = `text=${event.name}&head=${event.head_unit}`;

    event.contacts.map(contact => data +=`&contacts[]=${contact}`)

    return `<div class="container-event">

${await svg.getSvg(data)}
<div class="title-event">${event.name}</div>
<div>
    <div class="description-event">
        <span style="font-weight:bold;">Описание:</span>
        <span style="font-weight:200;">${event.desc}</span>
    </div>
</div>
<div class="buttons-event">
    ${event.button.is_shown 
        ? event.button.auth 
            ? `<div id="0" class="button-record-event get_auth_form" ><div>Авторизоваться</div><div class="button-flare"></div></div>` 
            : `<div class="${event.button.classes}" id="${event.id}"><div>${event.button.content}</div><div class="button-flare"></div></div>`
        : ''
    }
    <div class="button-description-event" href="details/?id=${event.id}"><div>Подробнее</div></div>
</div>
</div>`;
}

const showLoaded = eventList => {
    eventList.map(async event => {
        container.append(await makeEventElement(event));
    });
}



const loadMore = () => {
    if (allLoaded) {
        return;
    }

    $.ajax({
            type: 'POST',
            url: '/events_registration/new/ajax/get_more.php',
            data: `page=${page}&name=${searchInput.val() ?? ''}&date=${calendar.getSelectedDate()}`,
            // dataType: 'html',
            success: response => {
                // response = JSON.parse(response);
                showLoaded(response.payload);

                if (response.payload.length < 6) {
                    setIsLoaded(true)
                }
            },
            error: response => {
                response = JSON.parse(response.responseText);
                showNotification(
                    false,
                    response.message ?? utils.makeNotificationText('Не удалось загрузить изображение мероприятия: обратитесь с этой ошибкой к <a href="https://new.vyatsu.ru/account/notifications/?ELEMENT_ID=789249"> администратору </a>'),
                    4000
                );
            }
        }
    );
}

loadMore();

$('.load_more').on('click', () => {
    loadMore();
    page++;
});

$('.button_search').on('click', () => {
    page = 1;
    setIsLoaded(false);

    container.html('');
    loadMore();
    page++;
});
