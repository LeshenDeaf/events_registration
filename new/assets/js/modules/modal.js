import { form } from './utils.js';
export const modal = {
    container: $('#modal'),
    img: $('#img01'),
    caption: $('#caption'),
    close: $('.close'),
}

export const popupImage = function () {
    $('body').css('overflow', 'hidden');
    modal.container.css('display', 'block');
    modal.img.attr('src', $(this).attr('src'));
    modal.caption.text($(this).parents('.image-event').first().find('.caption-event').text());
}

export const hideImage = () => {
    if (form.container.is(':hidden')) {
        $('body').css('overflow', 'auto');
    }

    modal.container.css('display', 'none');
}

modal.close.on('click', hideImage);
$(".image-event-picture").on('click', popupImage);

$(document).mousedown(function (e) {
    if (modal.container.has(e.target).length === 0
        && modal.container.is(':visible')
    ) {
        hideImage();
    }
});
