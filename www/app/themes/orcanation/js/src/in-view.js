import InViewFactory from '../plugins/in-view';
import { Breakpoints } from 'JBSrc/constants';

class JBInView {
    static init() {
        const inViewDefault = InViewFactory();
        const inViewInit = InViewFactory();

        inViewDefault.threshold(0.2);
        // Dont add init class if already in viewport.
        jQuery('.jb-scroll').each((i, el) => {
            if (!inViewInit.is(el)) {
                jQuery(el).addClass('jb-scroll-init');
            }
        });

        inViewDefault('.jb-scroll')
            .on('enter', el => {
                if (!el.inViewDone) {
                    jQuery(el).addClass('in-view');
                }
            })
            .on('exit', el => (el.inViewDone = true));
    }
}

jQuery($ => {
    if ($(window).width() > Breakpoints.sm) {
        // Delay allows time for page to load itself in properly.
        setTimeout(JBInView.init, 500);
    }
});
