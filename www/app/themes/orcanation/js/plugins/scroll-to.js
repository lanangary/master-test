jQuery($ => {
    let header_offset =
        $('.header').css('position') == 'sticky' ||
        $('.header').css('position') == 'fixed'
            ? parseInt($('.header').css('height'))
            : 0;
    let wpadmin = $('#wpadminbar').length ? parseInt($('#wpadminbar').css('height')) : 0;
    header_offset = (header_offset + wpadmin) * 1.05;
    window.scrollAnchorTo = function(item, speed) {
        let item_offset = 0;
        let clicked_item = $('[href="' + item + '"]');
        if (speed == undefined) {
            speed = 800;
        }
        if ($(item).length > 0) {
            item_offset = $(item).offset() !== null ? $(item).offset().top : 0;
            if (item_offset >= 0) {
                $('html,body').animate(
                    {
                        scrollTop: $(item).offset().top - header_offset
                    },
                    speed,
                    function() {
                        if (clicked_item.length) {
                            if (item.substring(0, 1) === '#') {
                                window.location.hash = item;
                                window.scrollTo(0, $(item).offset().top - header_offset);
                            } else {
                                window.history.pushState(
                                    null,
                                    clicked_item.attr('title'),
                                    clicked_item.attr('href')
                                );
                            }
                        }
                    }
                );
            }
        }
    };

    $(document).on('click', '.scrollTo', function(e) {
        e.preventDefault();
        const hash = $(this).attr('href');
        const $speed = $(this).data('speed') ? $(this).data('speed') : 800;

        window.scrollAnchorTo(hash, $speed);
    });

    $(document).on('click', 'a[href^="#"]', function(e) {
        e.preventDefault();
        const hash = $(this).attr('href');
        const $speed = $(this).data('speed') ? $(this).data('speed') : 800;

        window.scrollAnchorTo(hash, $speed);
    });

    if (window.location.hash) {
        let window_hash = window.location.hash;
        window.scrollAnchorTo(window_hash, 800);
    }
});
