jQuery(function($) {
    $.fn.newsletterSignup = function() {
        function handleClasses($el, type) {
            // Reset classes, remove all classes matching 'has-*'
            $el.removeClass(function(index, css) {
                return (css.match(/(^|\s)has-\S+/g) || []).join(' ');
            });

            if (type !== null) {
                $el.addClass('has-' + type);
            }
        }

        function validEmail(emailAddress) {
            var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
            return pattern.test(emailAddress);
        }

        function validate($active) {
            var $input = $active.find('input'),
                validate = $input.data('validate');

            // If there is no validation required
            if (typeof validate === 'undefined') {
                return true;
            }

            var value = $input.val();

            // split rules, currently supports required and email
            validate = validate.split('|');

            // Loop through rules and validate
            for (var i in validate) {
                // Special case for length
                if (/length/.test(validate[i])) {
                    var size = parseInt(validate[i].replace('length:', ''));
                    validate[i] = 'length';
                }

                switch (validate[i]) {
                    case 'length':
                        if (value.length !== size) {
                            $input.focus();
                            return (
                                'This entry must be only ' +
                                size +
                                ' character' +
                                (size !== 1 ? 's' : '') +
                                ' long.'
                            );
                        }
                        break;
                    case 'numeric':
                        if (!$.isNumeric(value)) {
                            $input.focus();
                            return 'Please enter a number.';
                        }
                        break;
                    case 'email':
                        if (!validEmail(value)) {
                            $input.focus();
                            return 'Please enter a valid email address.';
                        }

                        break;
                    case 'required':
                    default:
                        if ($.trim(value) === '') {
                            $input.focus();
                            return 'This field is required, please fill it out.';
                        }
                        break;
                }
            }

            return true;
        }

        return this.map((i, el) => {
            let _form = el,
                $form = $(_form),
                $next = $form.find('[type=button]'),
                $submit = $form.find('[type=submit]'),
                $help = $form.find('.newsletter-signup__form-help'),
                $response = $form.next(),
                submitting = false,
                $active = $form.find('.newsletter-signup__list-item--field').first();

            /**
             * Init all the things
             */
            // Show first input
            $active
                .addClass('active')
                .find('input')
                .attr('tabindex', '0'); // make the active input tabbable to

            $active.closest('form').addClass('active');

            function setFeedback($message, type = 'error') {
                // Set help message
                $help.text($message);
                // Add classes
                handleClasses($form, type);
            }

            // Handle when next btn is click/enter is pressed
            var handleNext = function(event) {
                // If the currently form is not active then don't validate
                // This is needed if there are multiple newsletter signups on 1 page, i.e. footer and somewhere else on the site.
                if (
                    !$(document.activeElement)
                        .closest('form')
                        .is($form)
                ) {
                    return;
                }

                event.preventDefault();

                var $valid = validate($active);

                // Validate input
                if ($valid !== true) {
                    setFeedback($valid, 'error');
                    return false;
                }

                // Clear feedback
                setFeedback('', null);

                // If we are about to move to the last input show the submit button
                if ($active.index() === $active.siblings().length - 1) {
                    $next.parent().removeClass('active');
                    $submit.parent().addClass('active');
                }

                // If we are still here display the next input
                $active = $active.next();

                $active
                    .addClass('active')
                    .find('input')
                    .attr('tabindex', 0)
                    .focus();

                $active
                    .siblings()
                    .removeClass('active')
                    .find('input')
                    .attr('tabindex', -1); // set complete inputs to not be tabbable
            };

            /**
             * Bind all the events
             */
            // Handle the form submit
            $form.on('submit', function(event) {
                event.preventDefault();

                var $valid = validate($active);

                // Validate input
                if ($valid !== true) {
                    setFeedback($valid, 'error');
                    return false;
                }

                // Clear feedback
                setFeedback('', null);

                // Fade form out
                $form.addClass('submitting');
                // Add loading
                $submit.append($('<div />')).addClass('loading');

                var formData = $form.serialize();

                formData += '&action=newsletter_signup&nonce=' + newsletterData.nonce;

                $.ajax({
                    url: newsletterData.ajaxUrl,
                    data: formData,
                    type: 'POST'
                }).always(function(response) {
                    $form.fadeOut(300, function() {
                        $form.remove();

                        $response
                            .html(
                                typeof response.message !== 'undefined'
                                    ? response.message
                                    : response.responseJSON.message
                            )
                            .fadeIn(300);
                    });
                });
            });
            // On enter
            $(window).on('keydown', event => {
                var keyCode = event.keyCode || event.which;

                // enter
                if (keyCode === 13) {
                    // If enter is pressed on the last input then submit
                    if ($active.index() !== $active.siblings().length) {
                        handleNext(event);
                    }
                }
            });
            // Handle next button click
            $next.on('click', handleNext);
        });
    };

    $('.newsletter-signup').newsletterSignup();
});
