jQuery($ => {
    window.dataLayer = window.dataLayer || [];

    //On AJAX submit do we have a Gravity Form confirmation? If so trigger dataLayer Event
    $(document).bind('gform_confirmation_loaded', function(event, formID) {
        var formTitle = $('#gform_' + formID).find('.gform_heading .gform_title').length
            ? $('#gform_' + formID)
                  .find('.gform_heading .gform_title')
                  .text()
            : '';

        window.dataLayer.push({
            event: 'formSubmission',
            eventCategory:
                formTitle != '' ? formTitle + ' Form Submission' : 'Form Submission',
            eventAction: 'Submit',
            eventLabel: window.location.href,
            formTitle: formTitle,
            formID: formID
        });
    });

    //On load do we have a Gravity Form confirmation? If so trigger dataLayer Event
    $('.gform_confirmation_wrapper .gform_confirmation_message').each(function() {
        var formID = $(this)
            .attr('id')
            .replace('gform_confirmation_message_', '');

        window.dataLayer.push({
            event: 'formSubmission',
            eventCategory: 'Form Submission',
            eventAction: 'Submit',
            eventLabel: window.location.href,
            formID: formID
        });
    });
});
