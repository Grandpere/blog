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
        toast.find('.toast-header strong').html('<i class="fas fa-check-circle text-success"></i> <span>Success</span>');
        toast.find('.toast-body').text(response.message);
        toast.toast('show');
    }).fail(function(response) {
        const toast = $('#toast-container > #toasts .toast');
        toast.addClass('toast-error');
        toast.find('.toast-header strong').html('<i class="fas fa-exclamation-circle text-danger"></i> <span>An Error occured</span>');
        response.hasOwnProperty('responseJSON') ? toast.find('.toast-body').text(response.responseJSON.message) : toast.find('.toast-body').text('Oops, an error occured');
        toast.toast('show');
    });
}