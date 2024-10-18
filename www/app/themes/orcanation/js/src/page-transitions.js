import Barba from 'barba.js';
import TweenMax from 'gsap/TweenMax';
import imagesLoaded from 'imagesloaded';
import { Breakpoints } from './constants';

// Create the events.
const barbaInit = document.createEvent('Event');
barbaInit.initEvent('barbaInit', true, true);
const barbaUnmount = document.createEvent('Event');
barbaUnmount.initEvent('barbaUnmount', true, true);

// Animate Out Events
const animateOutStart = document.createEvent('Event');
animateOutStart.initEvent('animateOutStart', true, true);
const animateOutEnd = document.createEvent('Event');
animateOutEnd.initEvent('animateOutEnd', true, true);

// Animate In Events
const animateInStart = document.createEvent('Event');
animateInStart.initEvent('animateInStart', true, true);
const animateInEnd = document.createEvent('Event');
animateInEnd.initEvent('animateInEnd', true, true);

const transitioning = false;

const openLoader = function() {
    TweenLite.fromTo(jQuery('.loader'), 0.4, { opacity: 0 }, { opacity: 1 });
};

const closeLoader = function() {
    imagesLoaded('.page-wrap', () => {
        TweenLite.fromTo(jQuery('.loader'), 0.4, { opacity: 1 }, { opacity: 0 });
    });
};

document.addEventListener('DOMContentLoaded', closeLoader);

const initPage = () => {
    // scroll to the top so scroll position is not
    // retained on new page load.
    jQuery(window).scrollTop(0);

    // Eval inline scripts.
    jQuery('.page-wrap script').each(function() {
        eval(jQuery(this).text());
    });

    document.dispatchEvent(barbaInit);

    // Send pageview to ga.
    if (typeof ga === 'function') {
        ga('send', 'pageview', window.location.pathname);
    }
};

const BaseView = Barba.BaseView.extend({
    namespace: 'barba-view',
    onEnterCompleted() {
        initPage();
    }
});

const FadeTransition = Barba.BaseTransition.extend({
    start() {
        // As soon the loading is finished and the old page is faded out, let's fade the new page
        Promise.all([this.newContainerLoading, this.fadeOut()])
            .then(this.unmount())
            .then(this.fadeIn.bind(this));
    },

    unmount() {
        document.dispatchEvent(barbaUnmount);
    },

    fadeOut() {
        TweenMax.fromTo(
            this.oldContainer,
            0.6,
            {
                opacity: 1
            },
            {
                opacity: 0,
                onStart: () => {
                    document.dispatchEvent(animateOutStart);
                    jQuery('body')
                        .addClass('loading')
                        .removeClass('nav-active');
                },
                onComplete: () => document.dispatchEvent(animateOutEnd)
            }
        );
        return jQuery(this.oldContainer).promise();
    },

    fadeIn() {
        jQuery(this.oldContainer).hide();

        const start = {
            visibility: 'visible',
            opacity: 0,
            onStart: document.dispatchEvent(animateInStart)
        };

        const end = {
            opacity: 1,
            onStart: () => {
                const newBodyClasses = this.newContainer.getAttribute(
                    'data-body-classes'
                );
                jQuery('body')
                    .removeAttr('class')
                    .addClass(newBodyClasses);

                document.dispatchEvent(animateOutStart);
            },
            onComplete: () => {
                document.dispatchEvent(animateInEnd);
                TweenMax.set(this.newContainer, { clearProps: 'all' });
                this.done();
            }
        };

        // Close menus.
        jQuery('body')
            .removeClass('nav-active')
            .removeClass('mobile-nav-active')
            .removeClass('search-active');
        jQuery('.mobile-menu').removeClass('active');
        jQuery('.mega-menu').removeClass('active');
        jQuery('.searchbox').slideUp();

        let loaderDelay = 1000;

        if (jQuery(window).width() <= Breakpoints.sm) {
            loaderDelay = 500;
        }

        setTimeout(() => {
            if (transitioning) {
                openLoader();
            }
        }, loaderDelay);

        TweenMax.fromTo(this.newContainer, 0.8, start, end);
    }
});

/*
 * initPage runs on every new page
 */
Barba.Dispatcher.on('newPageReady', (newStatus, oldStatus, container, html) => {
    /*
    * get the classes rendered serverside for any element with an [data-barba-update-class] attribute,
    * then apply them to that element clientside (used for applying active classes to menu items)
    */
    const items = jQuery(html).find('[data-barba-update-class]');

    jQuery('[data-barba-update-class]').each(function(i) {
        const el = jQuery(items[i]).get(0);
        if (el !== undefined) jQuery(this).attr('class', el.classList.value);
    });
});

/*
 * Set FadeTransition as the page transition
 */

Barba.Pjax.getTransition = function() {
    return FadeTransition;
};

/*
* Initialise Barba
*/
const initBarba = () => {
    Barba.Utils.xhrTimeout = 6000;
    Barba.Pjax.cacheEnabled = true;
    BaseView.init();
    Barba.Pjax.start();
    Barba.Pjax.originalPreventCheck = Barba.Pjax.preventCheck;

    Barba.Pjax.preventCheck = function(evt, element) {
        if (!Barba.Pjax.originalPreventCheck(evt, element)) return false;
        if (/wp-admin/.test(element.href.toLowerCase())) return false;

        return true;
    };
};

document.addEventListener('DOMContentLoaded', () => {
    if (
        window.location.hostname === 'localhost' ||
        document.body.classList.contains('admin-bar')
    ) {
        initPage();
    } else {
        initBarba();
    }
});
