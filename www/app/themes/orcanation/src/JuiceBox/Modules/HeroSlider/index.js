import { Swiper, Navigation, Scrollbar } from 'swiper';

import { gsap } from 'gsap';

import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ScrollToPlugin } from 'gsap/ScrollToPlugin';
import { EaselPlugin } from 'gsap/EaselPlugin';
import { staggerSelf } from 'JBSrc/stagger-single';
gsap.registerPlugin(ScrollTrigger, ScrollToPlugin, EaselPlugin);

class heroslider {
    constructor() {
        this.module = document.querySelectorAll('.module.hero-slider');
    }

    init() {
        if (!document.body.contains(this.module[0])) {
            return;
        }
        staggerSelf('.stag-text', 'down', 'words', 100);

        staggerSelf('.stag-text-down', 'up', 'words', 100);
        this.banner();

        this.svgmorph();
    }

    svgmorph() {
        // Morph the SVG on hover
        document.querySelector('svg').addEventListener('mouseenter', function() {
            console.log('hover');

            gsap.to('#startPath', {
                duration: 1,
                morphSVG: '#endPath', // Morph the starting path to the end path
                ease: 'power2.inOut'
            });
        });

        // Reverse morph when the mouse leaves
        document.querySelector('svg').addEventListener('mouseleave', function() {
            gsap.to('#startPath', {
                duration: 1,
                morphSVG: '#startPath', // Morph back to the original shape
                ease: 'power2.inOut'
            });
        });
    }

    banner() {
        const swiper = new Swiper('.swiper', {
            // Optional parameters
            direction: 'horizontal',
            loop: true,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },

            // If we need pagination
            pagination: {
                el: '.swiper-pagination'
            }
        });

        // get current slide index

        let currentclip = this.module[0].querySelectorAll(
            '.hero-slider-clip-pagination-item'
        );

        swiper.on('slideChange', function() {
            // remove active class from currentclip
            currentclip.forEach(function(el) {
                el.classList.remove('active');
            });

            let currentSlide = swiper.realIndex;

            // add active class to currentclip
            currentclip[currentSlide].classList.add('active');
        });

        // click on clip to change slide
        currentclip.forEach(function(el, index) {
            el.addEventListener('click', function() {
                swiper.slideTo(index);
            });
        });
    }
}

let herosliderinit = new heroslider();
herosliderinit.init();
