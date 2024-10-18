jQuery($ => {
    $(document.body).on('submit', '.gform_wrapper form', function() {
        const span = $('<span>').addClass('gform-spinner__inner'); // add a throwaway span element to the button and style that, rather than potentially overwriting any psuedo elements on the button
        $(this)
            .find('button')
            .last()
            .append(span)
            .addClass('gform-spinner');
    });

    // Google reCaptcha v2 (CAPTCHA field needs to be added to each form)
    $('.ginput_recaptcha').each(function() {
        $(this).before(
            '<p class="ginput_recaptcha_terms">This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="blank">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="blank">Terms of Service</a> apply.</p>'
        );
        $(this)
            .closest('.form-group')
            .find('.gfield_label')
            .removeAttr('for');
    });

    // Google reCaptcha v3 (we automatically add the text for the form) - needs Gravity Forms - reCaptcha addon
    $('.ginput_recaptchav3').each(function() {
        $(this)
            .closest('form')
            .find('.gform_footer')
            .before(
                '<p class="ginput_recaptcha_terms">This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="blank">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="blank">Terms of Service</a> apply.</p>'
            );
    });
});
