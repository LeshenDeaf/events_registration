const searchInput = $('input[name="events-search_bar"]');
const events = $('.container-event');

String.prototype.clear = function () {
    return this.toLowerCase().trim()
        .replace(/[.,\/#!$%\^&\*;:{}=\-_`~()«»'\"]/g,"")
        .replace(/[\s\t\n]+/g," ")
}

const search = val => {
    val = val.clear();

    if (!val) {
        events.each(function () {
            $(this).show()
        });
        return;
    }

    events.each(function () {
        if ($(this).find('.title-event').text().clear().includes(val)) {
            $(this).show();
            return;
        }
        $(this).hide()
    })
}

let timer;

searchInput.on('keyup', function () {
    clearTimeout(timer);
    timer = setTimeout(() => search($(this).val()), 200);
})
