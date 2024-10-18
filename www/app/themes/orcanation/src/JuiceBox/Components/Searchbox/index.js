import throttle from 'lodash/throttle';

jQuery($ => {
    const $trigger = $('.header__search');
    const $close = $('.searchbox__close');
    const $input = $('.searchbox__form__input');
    const $suggestions = $('.searchbox__fuzzy');

    function search(event) {
        const $this = $(event.currentTarget);
        const value = $this.val();

        // Clear suggestions when there is no search string
        if (!value.length) {
            $suggestions.empty();
        }

        // Wait till we have 3 or more character before we search
        if (value.length < 3) {
            return;
        }
        $.get(
            '/',
            {
                s: value
            },
            data => {
                $suggestions.empty();

                if (!data.length) {
                    $suggestions.append(
                        '<p class="bold searchbox__fuzzy__no-results">Sorry, there are no results for that search query.<p>'
                    );
                } else {
                    let links = [];

                    links = data.map(
                        link =>
                            `<a href="${link.url}" class="bold block searchbox__fuzzy__link">${link.title}</a>`
                    );

                    $suggestions.append(links.join(''));
                }
            },
            'json'
        );
    }

    $trigger.on('click', () => {
        $(document.body).addClass('search-active');
        $input.focus();
    });

    $close.on('click', () => {
        $(document.body).removeClass('search-active');
    });

    $input.on('keyup', throttle(search, 500));

    $(document).on('keydown', event => {
        if (!$(document.body).hasClass('search-active')) {
            return;
        }

        if (event.keyCode === 27) {
            $(document.body).removeClass('search-active');
        }
    });
});
