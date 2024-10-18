import mq from 'enquire.js';
import { Breakpoints } from 'JBSrc/constants';

jQuery($ => {
    const $burger = $('.hamburger');
    const $navContainer = $('.header__nav');
    const $toggle = $('.nav__toggle');

    $burger.on('click', () => {
        document.body.classList.toggle('nav-active');
    });

    $toggle.on('click', event => {
        const $this = $(event.currentTarget);

        $this
            .parent()
            .toggleClass('nav__li--submenu-open')
            .end()
            .next('.nav__submenu')
            .slideToggle();
    });

    mq.register(`screen and (max-width: ${Breakpoints.sm}px)`, {
        match: () => {
            $navContainer.detach().insertAfter('.page-wrap');
        },
        unmatch: () => {
            $navContainer.detach().insertAfter('.header__logo-wrapper');
        }
    });

    /**
     * JuiceBox Keyboard accessibility smooth scroll
     * each anchor tag in template.twig should have a href attribute
     * to tell this code where to skip to
     */
    const $keyboardNavs = $('.keyboard-nav a');

    $keyboardNavs.on('click', function(e) {
        e.preventDefault();
        const hash = $(e.target).attr('href');
        const $speed = $(e.target).data('speed') ? $(e.target).data('speed') : 800;

        window.scrollAnchorTo(hash, $speed);
    });

    let mainHeader = document.querySelector('#main-header');

    // add scroll class to header when scrolling up and remove when scrolling down and at top add top class
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
        let currentScroll = window.scrollY;

        if (currentScroll > lastScroll) {
            mainHeader.classList.add('scroll');
        } else {
            mainHeader.classList.remove('scroll');
        }

        if (currentScroll === 0) {
            mainHeader.classList.add('top');
        }

        lastScroll = currentScroll;
    });

    // header slide js

    const headerSlidemain = document.querySelector('.header-slide-main');

    let headerSlidehasChild = headerSlidemain.querySelectorAll('.menu-item-has-children');

    headerSlidehasChild.forEach(item => {
        item.addEventListener('click', e => {
            e.preventDefault();

            // close all other submenu
            headerSlidehasChild.forEach(item => {
                if (item !== e.currentTarget) {
                    item.classList.remove('active');
                }
            });

            item.classList.toggle('active');
        });
    });

    let headerslideOpen = document.querySelector(
        '.header-wrap-item-menu-item-button-toogle'
    );
    let headerSlideClose = document.querySelector('.header-slide-top-close-toogle');
    let headerSlidewrap = document.querySelector('#header-slide-wrap');

    headerslideOpen.addEventListener('click', () => {
        headerSlidewrap.classList.add('active');
    });

    headerSlideClose.addEventListener('click', () => {
        headerSlidewrap.classList.remove('active');
    });
});
