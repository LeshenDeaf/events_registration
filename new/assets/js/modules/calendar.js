function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

export const getSelectedDate = () => selectedDate;

moment.locale('ru')
// month 0-11
let events = [];
// fetch('/events_registration/new/ajax/get_event_dates.php')
//     .then(res => {
//         events = res.json();
//         console.log(events);
//     })
//     .catch(data => console.log(data))

const getEvents = () => events;

const initCalendar = () => {
    setMonthYear(monthEl, yearEL, moment())
    fillCalendar(calendarEl)
}

$(function () {
    $.ajax({
            type: 'GET',
            url: '/events_registration/new/ajax/get_event_dates.php',
            data: '',
            success: response => {
                events = response.payload;
                initCalendar();
            },
            error: response => {
                response = JSON.parse(response.responseText);
                showNotification(
                    false,
                    response.message ?? utils.makeNotificationText('Не удалось загрузить изображение мероприятия: обратитесь с этой ошибкой к <a href="https://new.vyatsu.ru/account/notifications/?ELEMENT_ID=789249"> администратору </a>'),
                    4000
                );
                initCalendar();
            }
        }
    );
})

//     [
//     {
//         month: 11,
//         day: 1,
//         name: "длинный текст длинный текстдлинный текстдлинный текстдлинный текстдлинный текст",
//         description: "ОписаниеОписание ОписаниеОписаниеОписаниеОписаниеОписание ОписаниеОписаниеОписание Описание",
//     },
//     {
//         month: 10,
//         day: 9,
//         name: "День рождения"
//     },
//     {
//         month: 10,
//         day: 11,
//         year: 2022,
//         name: "Событие без повторений"
//     },
// ]

let selectedMoment = moment();
let currentMonth = moment().month()
let selectedDate = '';


const calendarEl = document.querySelector("#calendar");
const monthEl = document.querySelector("#month");
const yearEL = document.querySelector("#year");

const setMonthYear = (monthEL, yearEL, date) => {
    monthEL.innerHTML = capitalizeFirstLetter(date.format("MMMM"))
    yearEL.innerHTML = date.year()
}

const changeMonth = () => {
    currentMonth = selectedMoment.month()

    fillCalendar(calendarEl, moment(selectedMoment))
    setMonthYear(monthEl, yearEL, selectedMoment)
}

const setPrevMonth = () => {
    selectedMoment = selectedMoment.subtract(1, "month");
    changeMonth();
}

const setNextMonth = () => {
    selectedMoment = selectedMoment.add(1, "month");
    changeMonth();
}

const fillCalendar = (calendar, month) => {
    const getDate = m => m.startOf('month').startOf('week')

    let date = month ? getDate(month) : getDate(moment());

    calendar.innerHTML = '';
    for (let i = 0; i < 35; i++) {
        const event = [].concat(getEvents().filter(event => (
            date.date() === event.day
            && date.month() === event.month
            && (!event.year || date.year() === event.year)
        )))
        calendar.innerHTML +=
            `
            <div class="hover:outline calendar_body-day ${date.month() !== currentMonth && 'opacity-50' || ''}"
                date="${date.format('DD.MM.YYYY')}"
            >
                <div class="calendar_body-date ${date.format('DD.MM.YYYY') === selectedDate && 'active'}">${date.date()}</div>
                ${event.length !== 0 ? `
                <div class="calendar_body-has_event md:absolute h-full md:top-0  md:right-2 lg:right-3 flex items-center">
                    <div class="w-2 h-2 lg:w-3 lg:h-3 rounded-full bg-[#CBCAFF]" title="${event[0].name}">
                    
                    </div>
                </div>` : ''}
            </div>
            `;
        date.add(1, 'd');
    }
}


$('.set_prev_m').on('click', setPrevMonth);
$('.set_next_m').on('click', setNextMonth);

$('.calendar_opener').on('click', () => $('.calendar').toggleClass('active'));

$('#calendar').on('click', '.calendar_body-date', function () {
    const anotherActive = $(this).parent().parent().find('.calendar_body-date.active').eq(0);
    const thisDate = $(this).parent().attr('date');

    if (anotherActive.parent().attr('date') !== thisDate) {
        anotherActive.removeClass('active');
    }

    if ($(this).hasClass('active')) {
        selectedDate = '';
        $(this).removeClass('active')
    } else {
        selectedDate = thisDate;
        $(this).addClass('active')
    }
})

