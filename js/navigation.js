'use strict';

/**
 * mobile navigation
 */
const body = document.getElementsByTagName("BODY")[0];
const header = document.getElementById('masthead');
const menuButton = document.getElementById('menu-toggle');
const navigation = document.getElementById('site-navigation');
const navigationOverlay = document.getElementById('navigation-overlay');

if (/^(iPhone|iPad|iPod)/.test(navigator.platform)) {
    body.classList.add("ios");
}
if (navigation && header && navigationOverlay) {
    let menuList = navigation.getElementsByTagName('ul')[0];

    let max_opacity = 0.8;
    let navi_width = 256;

    let bar1 = document.querySelector('#menu-toggle .bar:nth-child(1)');
    let bar2 = document.querySelector('#menu-toggle .bar:nth-child(2)');
    let bar3 = document.querySelector('#menu-toggle .bar:nth-child(3)');
    let bar4 = document.querySelector('#menu-toggle .bar:nth-child(4)');

    menuButton.addEventListener('click', function (evt) {
//        console.log("click!");
        if (navigation.classList.contains('toggled')) {
//            console.log("close now");
            closeMenu();
        } else {
//            console.log("open now");
            openMenu();
        }
    });

    navigationOverlay.addEventListener('click', function (evt) {
        menuButton.click();
    });

    function openMenu() {
        resetCross();

        menuButton.setAttribute('aria-expanded', 'true');
        menuList.setAttribute('aria-expanded', 'true');
        menuButton.classList.add("open");
        body.classList.add("mobile-navigation-open");

        navigationOverlay.classList.add("visible");
        navigationOverlay.style.removeProperty('transition-duration');
        navigationOverlay.style.opacity = max_opacity;

        navigation.classList.add("animate");
        //navigation.style.removeProperty('transition-duration');
        navigation.style.transform = 'translateX(0px)';
        navigation.classList.add("toggled");

        currentPos = navi_width;
    }


    function closeMenu() {
        resetCross();

        menuButton.setAttribute('aria-expanded', 'false');
        menuList.setAttribute('aria-expanded', 'false');

        menuButton.classList.remove("open");
        body.classList.remove("mobile-navigation-open");

        navigationOverlay.style.removeProperty("transition-duration");
        navigationOverlay.style.opacity = 0;

        // set hidden after opacity animation
        setTimeout(function (e) {
            navigationOverlay.classList.remove("visible");
        }, 200);

        navigation.classList.add("animate");
        //navigation.style.removeProperty("transition-duration");
        navigation.style.removeProperty("transform");
        navigation.classList.remove("toggled");

        currentPos = 0;
    }

    // reset manually triggered X animation
    function resetCross() {
        /*bar1.style.removeProperty("transition-duration");
         bar1.style.removeProperty("top");
         bar1.style.removeProperty("width");
         bar1.style.removeProperty("left");
         
         bar2.style.removeProperty("transition-duration");
         bar2.style.removeProperty("transform");
         
         bar3.style.removeProperty("transition-duration");
         bar3.style.removeProperty("transform");
         
         bar4.style.removeProperty("transition-duration");
         bar4.style.removeProperty("top");
         bar4.style.removeProperty("width");
         bar4.style.removeProperty("left");
         */
    }

    // https://stackoverflow.com/a/23230280
    // https://github.com/freetitelu/touch-sidewipe
    document.addEventListener('touchstart', handleTouchStart, false);
    document.addEventListener('touchmove', handleTouchMove, false);
    document.addEventListener('touchend', handleTouchEnd, false);

    let xStartPosition = null;
    let xMovePosition = null;
    let yStartPosition = null;
    let yMovePosition = null;
    let isOpenAllowed = null;
    let isCloseAllowed = null;
    let threshold_edge = 20;
    let xMinDistanceClose = 50;
    let xMinDistanceOpen = 20;
    let yMinDistance = 50;
    let currentPos = null;
    let skip = false;

    function handleTouchStart(evt) {
        const firstTouch = evt.touches[0];
        xStartPosition = firstTouch.clientX;
        yStartPosition = firstTouch.clientY;

        isOpenAllowed = xStartPosition < threshold_edge && !navigation.classList.contains('toggled');
        isCloseAllowed = navigation.classList.contains('toggled'); // && (window.innerWidth - xStartPosition) > navi_width

        skip = false;
//        console.log("start");
    }

    function handleTouchMove(evt) {
        if (!xStartPosition) {
            return;
        }

        // Save previous xMovePosition
        let xMovePositionPrevious = xMovePosition;

        xMovePosition = evt.touches[0].clientX;
        yMovePosition = evt.touches[0].clientY;

        let isOpen = navigation.classList.contains('toggled');

        // is open and min distance not reached
        let xDistance = Math.abs(xStartPosition - xMovePosition);
        let yDistance = Math.abs(yStartPosition - yMovePosition);

        skip = false;
        if (isOpen && xDistance < xMinDistanceClose) {
//            console.log("skip");
            skip = true;
            return;
        }

        if (!isOpen && xDistance < xMinDistanceOpen) {
            skip = true;
            return;
        }

        /*if(isOpen && yDistance > yMinDistance){
         xMovePosition = null;
         return;
         }*/

        let posUp = xMovePosition;

        let swipe = xStartPosition < xMovePosition;

        // swipe open
        if (swipe && isOpenAllowed) {
            moveNavigationToPosition(posUp);

            // swipe from right to left (open) and then go back to right
            if (xMovePositionPrevious && xMovePositionPrevious > xMovePosition) {
//                console.log("zu lassen?");
//                console.log(xStartPosition);
//                console.log(xMovePositionPrevious);
//                console.log(xMovePosition);
                xStartPosition = xMovePositionPrevious;
                // force close
                isCloseAllowed = true;
            }
        }

        // swipe close
        if (!swipe && isCloseAllowed) {
            moveNavigationToPosition(posUp);

            // swipe from left to right (close) and then go back to left
            if (xMovePositionPrevious && xMovePositionPrevious < xMovePosition) {
//                console.log("offen lassen?");
//                console.log(xStartPosition);
//                console.log(xMovePositionPrevious);
//                console.log(xMovePosition);
                xStartPosition = xMovePositionPrevious;
                // force open
                isOpenAllowed = true;
            }
        }

        //}
    }

    function moveNavigationToPosition(pos) {

        if (pos > navi_width) {
            pos = navi_width;
        }
        if (pos < 0) {
            pos = 0;
        }

        navigation.classList.remove("animate");
        //navigation.style.transitionDuration = 0 + 's';
        navigation.style.transform = 'translateX(' + (-navi_width + pos) + 'px)';

        currentPos = pos;

        let percent_open = pos / navi_width;
        if (percent_open <= 0) {
            percent_open = 0;
        }
        if (percent_open >= 1) {
            percent_open = 1;
        }

        navigationOverlay.classList.add("visible");
        navigationOverlay.style.transitionDuration = 0 + 's';
        navigationOverlay.style.opacity = percent_open * max_opacity;


        // Manually trigger X animation
        /*
         bar1.style.transitionDuration = 0 + 's';
         bar1.style.top = 4 + (percent_open * 4) + 'px';
         bar1.style.width = (1 - percent_open) * 100 + '%';
         bar1.style.left = (percent_open * 50) + '%';
         
         bar2.style.transitionDuration = 0 + 's';
         bar2.style.transform = 'rotate(' + (percent_open * 45) + 'deg)';
         
         bar3.style.transitionDuration = 0 + 's';
         bar3.style.transform = 'rotate(-' + (percent_open * 45) + 'deg)';
         
         bar4.style.transitionDuration = 0 + 's';
         bar4.style.top = 20 - (percent_open * 4) + 'px';
         bar4.style.width = (1 - percent_open) * 100 + '%';
         bar4.style.left = (percent_open * 50) + '%';
         */
    }

    function handleTouchEnd(evt) {

        if (!xStartPosition || !xMovePosition) {
            return;
        }

        let isOpen = navigation.classList.contains('toggled');

        // close/open menu when distance travelled is too small (min distance not reached)
        // and menu is already faded out/in
        if (skip && currentPos > 0) {
//                console.log("close because distance to small");
            if (isOpen) {
                openMenu();
            } else {
                closeMenu();
            }
        }
        if (!skip) {
            if (!isOpen) {
                if (xMovePosition > xStartPosition && isOpenAllowed) {
                    openMenu();
                } else {
//                    console.log("close1");
                    closeMenu();
                }
            } else {
                if (xMovePosition < xStartPosition && isCloseAllowed) {
//                    console.log("close2");
                    closeMenu();
                } else {
                    openMenu();
                }
            }
        }
        xStartPosition = null;
        isOpenAllowed = null;
        isCloseAllowed = null;
        xMovePosition = null;
    }
}