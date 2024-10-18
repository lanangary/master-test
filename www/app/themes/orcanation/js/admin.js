/**
 * ACF module screenshots
 */
jQuery($ => {
    function snakeToCamel(s) {
        return capitalizeFirstLetter(
            s.replace(/(\_\w)/g, function(m) {
                return m[1].toUpperCase();
            })
        );
    }

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    $(document.body).on(
        {
            mouseenter: function() {
                var $item = $(this),
                    nameSpace = snakeToCamel($item.find('a').data('layout'));

                if (!$item.find('img').length) {
                    var image =
                        acfJpgData.themeUri +
                        '/src/JuiceBox/Modules/' +
                        nameSpace +
                        '/screenshot.png';

                    image = $('<img src="' + image + '" class="acf-fc-jpg" />');

                    image.on('error', function() {
                        $(this).attr(
                            'src',
                            acfJpgData.themeUri +
                                '/src/JuiceBox/Modules/__template/screenshot.png'
                        );
                    });

                    image.hide(0).css({
                        position: 'absolute',
                        right: 'calc(100% + 20px)',
                        width: '800px',
                        top: '50%',
                        transform: 'translateY(-50%)',
                        boxShadow: '0px 2px 10px 3px rgba(0,0,0,0.2)'
                    });

                    $item.find('a').append(image);
                } else {
                    $item.find('img').fadeIn(300);
                }
            },
            mouseleave: function() {
                $('.acf-fc-popup li img').hide(0);
            }
        },
        '.acf-fc-popup li'
    );
});
