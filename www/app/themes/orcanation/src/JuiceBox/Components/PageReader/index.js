jQuery($ => {
    $(function() {
        const pageProgress = $('.page-reader-progress__inner');

        function pageReader() {
            if (pageProgress.length) {
                var winScroll =
                    document.body.scrollTop || document.documentElement.scrollTop;
                var height =
                    document.documentElement.scrollHeight -
                    document.documentElement.clientHeight;
                var scrolled = (winScroll / height) * 100;
                pageProgress.width(scrolled + '%');
            }
        }
        $(document).on('scroll resize', function() {
            pageReader();
        });
        if (pageProgress.length) {
            $('body').addClass('has-page-reader');
        }
    });
});
