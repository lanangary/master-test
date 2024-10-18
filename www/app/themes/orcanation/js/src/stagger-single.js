import { gsap } from 'gsap';

import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ScrollToPlugin } from 'gsap/ScrollToPlugin';

import 'splitting/dist/splitting.css';
import 'splitting/dist/splitting-cells.css';
import Splitting from 'splitting';

Splitting();

export function staggerSelf(
    elements,
    direction = 'up',
    style = 'words',
    speed,
    settruncate = false
) {
    Splitting();
    var textElement = document.querySelectorAll(elements);
    textElement.forEach(element => {
        // element.style.opacity = 0;
    });

    gsap.registerPlugin(ScrollTrigger);

    textElement.forEach(elementself => {
        ScrollTrigger.create({
            trigger: elementself,
            start: 'top 90%', // Adjust the trigger point as needed
            end: 'bottom 50%',
            onEnter: () => {
                playStaggeredAnimation(elementself, direction, style, speed, settruncate);
            },
            once: true // Only trigger once
        });
    });
}

function playStaggeredAnimation(elementself, direction, style, speed, settruncate) {
    speed = parseInt(speed);
    speed = speed / 1000;

    console.log(speed);
    if (style == 'words') {
        var tl = gsap.timeline();

        var splitText = Splitting({ target: elementself, by: 'chars' });
        var items = splitText[0].chars;
        var offsite = '100%';
        if (direction == 'down') {
            offsite = '-100%';
        }

        gsap.set(elementself, { overflow: 'hidden' });

        // Wrap each word in a container and position them absolutely
        items.forEach(word => {
            var wordWrapper = document.createElement('div');
            wordWrapper.classList.add('word-wrapper');
            wordWrapper.style.overflow = 'hidden';
            word.parentNode.insertBefore(wordWrapper, word);
            wordWrapper.appendChild(word);

            // gsap.set(wordWrapper, { y: offsite });
        });

        gsap.set(items, { y: offsite });

        tl.to(items, {
            duration: 1,
            y: 0,
            ease: 'power4.inOut',
            stagger: 0.035
        });

        tl.play();
        elementself.style.opacity = 1;
    } else {
        var tl = gsap.timeline();

        var splitText = Splitting({ target: elementself, by: 'lines' });

        var items = splitText[0].words;

        let thelines = splitText[0].lines;
        let ind = 0;

        let linelength = thelines.length;
        //  to int
        linelength = parseInt(linelength);

        var linewrap = document.createElement('div');
        linewrap.classList.add('line-wrapper');

        for (let i = 0; i < linelength; i++) {
            let line = thelines[i];

            let innerlinelength = line.length;
            innerlinelength = parseInt(innerlinelength);

            let innerline = document.createElement('div');
            innerline.classList.add('inner-line-wrapper');

            let innerlineanimation = document.createElement('div');
            innerlineanimation.classList.add('inner-line-animation');

            for (let index = 0; index < innerlinelength; index++) {
                let node = line[index];
                let clone = node.cloneNode(true);

                if (index !== innerlinelength - 1) {
                    // Append a non-breaking space after the word
                    let space = document.createTextNode('\u00A0'); // '\u00A0' represents non-breaking space
                    clone.appendChild(space);
                }

                innerlineanimation.appendChild(clone);
                innerline.appendChild(innerlineanimation);
            }

            linewrap.appendChild(innerline);

            elementself.replaceChildren(linewrap);
        }

        var offsite = '100%';
        if (direction == 'down') {
            offsite = -30;
        }

        let lineanimating = document.querySelectorAll('.inner-line-animation');

        if (settruncate == true) {
            var truncate = elementself.querySelectorAll('.inner-line-wrapper');

            if (truncate.length > 3) {
                truncate.forEach((line, i) => {
                    if (i > 2) {
                        line.style.display = 'none';
                    }
                    if (i == 2) {
                        let more = line.querySelectorAll('.word');
                        more.forEach(word => {
                            // change the last word to more
                            if (word == more[more.length - 1]) {
                                word.innerHTML = '...';
                                // word margin left -10px
                                word.style.marginLeft = '-8px';
                            }
                        });
                    }
                });
            }

            console.log('true line');
        }
        gsap.set(lineanimating, { y: offsite });

        tl.to(lineanimating, {
            duration: 1,
            y: 0,
            delay: speed,
            ease: 'power2.inOut',
            stagger: 0.12
        });

        tl.play();
        elementself.style.opacity = 1;

        // after play remove overflow hidden
        tl.then(() => {
            // console.log(elementself);
            // search all word wrapper and remove the overflow hidden

            var wordWrapper = elementself.querySelectorAll('.inner-line-wrapper');

            // console.log(wordWrapper);
            wordWrapper.forEach(word => {
                word.style.overflow = 'visible';
            });

            console.log('Stagger done');
        });
    }
}
