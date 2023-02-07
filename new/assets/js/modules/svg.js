/**
 * Some functions that may be needed
 */
import * as utils from './utils.js';

export const getSvg = async data => {
    return $.ajax({
        type: 'POST',
        url: '/events_registration/new/ajax/get_svg.php',
        data: data,
        dataType: 'html',
    });
}


$(document).ready(function () {
    $('.svg_form').each(function () {
        const form = this;

        if (!form) {
            return;
        }

        getSvg($(form).serialize())
            .then(response => $(form).replaceWith(response))
            .catch(response => {
                response = JSON.parse(response.responseText);
                showNotification(
                    false,
                    response.message ?? utils.makeNotificationText('Не удалось загрузить изображение мероприятия: обратитесь с этой ошибкой к <a href="https://new.vyatsu.ru/account/notifications/?ELEMENT_ID=789249"> администратору </a>'),
                    4000
                );
            })

        // $.ajax({
        //     type: 'POST',
        //     url: '/events_registration/new/ajax/get_svg.php',
        //     data: $(form).serialize(),
        //     dataType: 'html',
        //     success: response => $(form).replaceWith(response),
        //     error: response => {
        //         response = JSON.parse(response.responseText);
        //         showNotification(
        //             false,
        //             response.message ?? utils.makeNotificationText('Не удалось загрузить изображение мероприятия: обратитесь с этой ошибкой к <a href="https://new.vyatsu.ru/account/notifications/?ELEMENT_ID=789249"> администратору </a>'),
        //             4000
        //         );
        //     }
        // });
    })
});
