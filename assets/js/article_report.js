import 'bootstrap';

$('#js-form-report').on('submit', handleOnSubmitReport);

function handleOnSubmitReport(evt) {
    evt.preventDefault();
    const form = $(this);
    const $toastContainer = $('#toast-container > #toasts');

    const data = form.serializeArray();
    $.ajax(
        {
            method: form.attr('method'),
            url: form.attr('action'),
            data: data,
            dataType: 'json'
        }
    ).done(function(response) {
        const toast = $('#toast-container > #toasts .toast');
        toast.addClass('toast-success');
        toast.find('.toast-body').text(response.message);
        toast.toast('show');
    }).fail(function(response) {
        console.error(response);
        const toast = $('#toast-container > #toasts .toast');
        toast.addClass('toast-error');
        toast.find('.toast-body').text("Oops, unexpected error !");
        toast.toast('show');
    });
}