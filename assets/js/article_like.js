const LIKE_ICON = 'far fa-thumbs-up';
const UNLIKE_ICON = 'fas fa-thumbs-up';

$('#js-form-like').on('submit', handleOnSubmitLike);

function handleOnSubmitLike(evt) {
    evt.preventDefault();
    const form = $(this);
    const $icon = $('.js-like i');
    const likeCount = $('#js-likes');

    const data = form.serializeArray();
    $.ajax(
        {
            method: form.attr('method'),
            url: form.attr('action'),
            data: data,
            dataType: 'json'
        }
    ).done(function(response) {
        console.info(response);
        console.log($icon.attr('class'));
        $icon.hasClass(LIKE_ICON) ? $icon.removeClass(LIKE_ICON).addClass(UNLIKE_ICON) : $icon.removeClass(UNLIKE_ICON).addClass(LIKE_ICON);
        likeCount.text(response.likes);
    }).fail(function(response) {
        console.error(response.responseText);
    });
}
