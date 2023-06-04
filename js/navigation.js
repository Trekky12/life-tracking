'use strict';

/**
 * mobile navigation
 */
const body = document.getElementsByTagName("BODY")[0];
const header = document.getElementById('masthead');
const menuButton = document.getElementById('menu-toggle');
const navigation = document.getElementById('site-navigation');
const navigationOverlay = document.getElementById('navigation-overlay');
const FBAs = document.querySelectorAll('.button-add-item');

const boardSidebar = document.getElementById('board-sidebar');

const wasMobile = isMobile();

document.addEventListener("DOMContentLoaded", (event) => {
    scrollToTab(document.querySelector('a.tabbar-tab.active'), true);
});

if (/^(iPhone|iPad|iPod)/.test(navigator.platform)) {
    body.classList.add("ios");
}
if (navigation && header && navigationOverlay) {
    let menuList = navigation.getElementsByTagName('ul')[0];

    let max_opacity = 0.8;
    let navi_width = 256;

    menuButton.addEventListener('click', function (evt) {

        if (navigation.classList.contains('toggled')) {
            //            console.log("close now");
            closeMenu();
        } else {
            //            console.log("open now");
            openMenu();
        }

        // set cookie
        if (!isMobile()) {
            if (navigation.classList.contains('toggled')) {
                setCookie('navigationdrawer_desktophidden', 1);
            } else {
                setCookie('navigationdrawer_desktophidden', 0);
            }
        } else {
            setCookie('navigationdrawer_desktophidden', 0);
        }
    });

    navigationOverlay.addEventListener('click', function (evt) {
        menuButton.click();
    });

    function openMenu() {

        menuButton.setAttribute('aria-expanded', 'true');
        menuList.setAttribute('aria-expanded', 'true');
        menuButton.classList.add("open");
        body.classList.add("navigation-drawer-toggled");

        if (isMobile()) {
            navigationOverlay.classList.add("visible");
            navigationOverlay.style.removeProperty('transition-duration');
            navigationOverlay.style.opacity = max_opacity;
        }

        navigation.classList.add("animate");
        //navigation.style.removeProperty('transition-duration');
        navigation.style.removeProperty('transform');
        //navigation.style.transform = 'translateX(0px)';
        navigation.classList.add("toggled");

        currentPos = navi_width;
    }


    function closeMenu() {

        menuButton.setAttribute('aria-expanded', 'false');
        menuList.setAttribute('aria-expanded', 'false');

        menuButton.classList.remove("open");
        body.classList.remove("navigation-drawer-toggled");

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

    // https://stackoverflow.com/a/23230280
    // https://github.com/freetitelu/touch-sidewipe
    if (isMobile()) {
        document.addEventListener('touchstart', handleTouchStart, false);
        document.addEventListener('touchmove', handleTouchMove, false);
        document.addEventListener('touchend', handleTouchEnd, false);
    }

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

    window.addEventListener('resize', handleResize);

    function handleResize() {
        if (!wasMobile && isMobile()) {
            navigation.classList.remove('toggled');
            body.classList.remove("navigation-drawer-toggled");
            setCookie('navigationdrawer_desktophidden', 0);
        }
    }

    /**
     * Hide header on scroll down
     * @see https://www.w3schools.com/howto/howto_js_navbar_hide_scroll.asp
     */
    let prevScrollpos = window.pageYOffset;
    document.addEventListener('scroll', function () {
        var currentScrollPos = window.pageYOffset;

        if (Math.abs(prevScrollpos - currentScrollPos) < 10) {
            return;
        }

        let headerHeight = header.offsetHeight;
        //let hideHeaderValue = (headerHeight * -1) + 'px';

        if (prevScrollpos <= currentScrollPos) {

            header.style.top = -headerHeight + 'px';
        } else {
            header.style.removeProperty("top");
        }
        header.style.top = (prevScrollpos <= currentScrollPos) ? -headerHeight + 'px' : "0";

        FBAs.forEach(function (fba) {
            if (prevScrollpos <= currentScrollPos) {
                fba.style.bottom = "-100px";
            } else {
                fba.style.removeProperty("bottom");
            }
        });

        if (boardSidebar) {
            boardSidebar.style.top = (prevScrollpos <= currentScrollPos) ? "0" : headerHeight + 'px';
        }
        prevScrollpos = currentScrollPos;
    });
}

const rippleIcons = document.querySelectorAll('.icon-ripple-wrapper');

rippleIcons.forEach(function (rippleIcon) {

    if (!isTouchEnabled()) {
        rippleIcon.addEventListener('mousedown', function (evt) {
            rippleIcon.classList.add("ripple-effect-start");
        });

        rippleIcon.addEventListener('mouseup', function (evt) {
            //setTimeout(function () {
            rippleIcon.classList.remove("ripple-effect-start");
            //}, 300);
            rippleIcon.classList.add("ripple-effect-end");
            setTimeout(function () {
                rippleIcon.classList.remove("ripple-effect-end");
            }, 200);
        });
    } else {
        rippleIcon.addEventListener('touchstart', function (evt) {
            rippleIcon.classList.add("ripple-effect-start");

            // trigger click, so that the timeout causes no delay on ios
            rippleIcon.click();

            // simulate hover (before element is triggered)
            rippleIcon.focus();

            setTimeout(function () {
                rippleIcon.classList.remove("ripple-effect-start");
                rippleIcon.classList.add("ripple-effect-end");
                setTimeout(function () {
                    rippleIcon.classList.remove("ripple-effect-end");
                }, 200);
            }, 300);
        });
    }
});

/*let scrollBarHeight = computeHorizontalScrollbarHeight();

let tabbarScroller = document.querySelectorAll('.tabbar-scrollarea');
tabbarScroller.forEach(function (tabbarScroller) {
    tabbarScroller.style.marginBottom = -scrollBarHeight + 'px';
});
*/

const tabbarScrollArea = document.querySelector('.tabbar-scrollarea');
const tabbarTabs = document.querySelectorAll('a.tabbar-tab');


tabbarTabs.forEach(function (tabbarTab) {
    tabbarTab.addEventListener('click', function (evt) {
        tabbarTabs.forEach(function (btn) {
            btn.classList.remove("active");
        });
        tabbarTab.classList.add("active");
        scrollToTab(tabbarTab, false);

        // wait for smooth scroll to tab
        setTimeout(function () {
            tabbarScrollArea.parentNode.style.height = 0;
            body.classList.remove("has-tabbar");
        }, 200);
    });
});

function scrollToTab(tab, initial = false) {
    if (!tab) {
        return;
    }
    let behaviour = 'smooth';
    if (initial) {
        tabbarScrollArea.parentNode.style.height = "50px";
        body.classList.add("has-tabbar");
        behaviour = 'instant';
    }

    //let scrollMax = tabbarScrollArea.scrollWidth - tabbarScrollArea.offsetWidth;

    let tabWidth = tab.offsetWidth;
    let tabPosition = tab.offsetLeft;
    let offsetLeft = 0; //tabbarScrollArea.parentNode.offsetLeft;

    let margin = (tabbarScrollArea.offsetWidth - tabWidth) / 2;


    //let newPosition = tabPosition - offsetLeft - tabWidth / 2;

    let offset = offsetLeft / 2;
    if (!isMobile()) {
        offset = 0;
    }

    // center to middle of screen
    let newPosition = tabPosition - margin + offset;

    /*let xMin = tabbarScrollArea.offsetLeft;
    let xMax = tabbarScrollArea.offsetWidth;
    console.log(newPosition);
    console.log(xMin);
    console.log(xMax);*/

    tabbarScrollArea.scrollTo({ 'left': newPosition, 'behavior': behaviour });
}


const tabbarScrollButtons = document.querySelectorAll('.tabbar-tab-scroll-btn');
tabbarScrollButtons.forEach(function (tabbarScrollButton) {
    tabbarScrollButton.addEventListener('click', function (evt) {
        tabbarScrollArea.style.scrollBehavior = 'smooth';
        if (tabbarScrollButton.dataset.type == "left") {
            tabbarScrollArea.scrollLeft -= 50;
        } else {
            tabbarScrollArea.scrollLeft += 50;
        }
    });
});