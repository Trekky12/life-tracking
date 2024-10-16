"use strict";const body=document.getElementsByTagName("BODY")[0],header=document.getElementById("masthead"),menuButton=document.getElementById("menu-toggle"),navigation=document.getElementById("site-navigation"),navigationOverlay=document.getElementById("navigation-overlay"),FBAs=document.querySelectorAll(".button-add-item"),boardSidebar=document.getElementById("board-sidebar");let wasMobile=isMobile();const max_opacity=.8;if(document.addEventListener("DOMContentLoaded",(t=>{scrollToTab(document.querySelector("a.tabbar-tab.active"),!0)})),(/^(iPhone|iPad|iPod)/.test(navigator.platform)||"MacIntel"===navigator.platform&&navigator.maxTouchPoints>1)&&body.classList.add("ios"),navigation&&header&&navigationOverlay){let t=navigation.getElementsByTagName("ul")[0],e=256;function a(){menuButton.setAttribute("aria-expanded","true"),t.setAttribute("aria-expanded","true"),menuButton.classList.add("open"),body.classList.add("navigation-drawer-toggled"),isMobile()&&(navigationOverlay.classList.add("visible"),navigationOverlay.style.removeProperty("transition-duration"),navigationOverlay.style.opacity=.8),navigation.classList.add("animate"),navigation.style.removeProperty("transform"),navigation.classList.add("toggled"),document.activeElement.blur(),b=e}function n(){menuButton.setAttribute("aria-expanded","false"),t.setAttribute("aria-expanded","false"),menuButton.classList.remove("open"),body.classList.remove("navigation-drawer-toggled"),navigationOverlay.style.removeProperty("transition-duration"),navigationOverlay.style.opacity=0,setTimeout((function(t){navigationOverlay.classList.remove("visible")}),200),navigation.classList.add("animate"),navigation.style.removeProperty("transform"),navigation.classList.remove("toggled"),b=0,L()}menuButton.addEventListener("click",(function(t){navigation.classList.contains("toggled")?n():a(),isMobile()?setCookie("navigationdrawer_desktophidden",0):navigation.classList.contains("toggled")?setCookie("navigationdrawer_desktophidden",1):setCookie("navigationdrawer_desktophidden",0)})),navigationOverlay.addEventListener("click",(function(t){menuButton.click()})),isMobile()&&(document.addEventListener("touchstart",m,!1),document.addEventListener("touchmove",f,!1),document.addEventListener("touchend",y,!1));let i=null,o=null,s=null,l=null,r=null,d=null,c=100,u=50,v=50,b=null,g=!1;function m(t){const e=t.touches[0];i=e.clientX,s=e.clientY,r=i<c&&!navigation.classList.contains("toggled"),d=navigation.classList.contains("toggled"),g=!1}function f(t){if(isAppResumed)return void(isAppResumed=!1);if(!i)return;let e=o;o=t.touches[0].clientX,l=t.touches[0].clientY;let a=navigation.classList.contains("toggled"),n=Math.abs(i-o);Math.abs(s-l);if(g=!1,a&&n<u)return void(g=!0);if(!a&&n<v)return void(g=!0);let c=o,b=i<o;b&&r&&(p(c),e&&e>o&&(i=e,d=!0)),!b&&d&&(p(c),e&&e<o&&(i=e,r=!0))}function p(t){t>e&&(t=e),t<0&&(t=0),navigation.classList.remove("animate"),navigation.style.transform="translateX("+(-e+t)+"px)",b=t;let a=t/e;a<=0&&(a=0),a>=1&&(a=1),navigationOverlay.classList.add("visible"),navigationOverlay.style.transitionDuration="0s",navigationOverlay.style.opacity=.8*a}function y(t){if(isAppResumed)return void(isAppResumed=!1);if(!i||!o)return;let e=navigation.classList.contains("toggled");g&&b>0&&(e?a():n()),g||(e?o<i&&d?n():a():o>i&&r?a():n()),L()}function L(){i=null,r=null,d=null,o=null}function h(){(!wasMobile&&isMobile()||wasMobile&&!isMobile())&&(navigation.classList.remove("toggled"),body.classList.remove("navigation-drawer-toggled"),setCookie("navigationdrawer_desktophidden",0),navigationOverlay.style.opacity=0,navigationOverlay.classList.remove("visible")),wasMobile=isMobile()}window.addEventListener("resize",h);let E=window.pageYOffset;document.addEventListener("scroll",(function(){var t=window.pageYOffset;if(Math.abs(E-t)<10)return;let e=header.offsetHeight;E<=t?header.style.top=-e+"px":header.style.removeProperty("top"),header.style.top=E<=t?-e+"px":"0",FBAs.forEach((function(e){E<=t?e.style.bottom="-100px":e.style.removeProperty("bottom")})),boardSidebar&&(boardSidebar.style.top=E<=t?"0":e+"px"),E=t}))}const rippleIcons=document.querySelectorAll(".icon-ripple-wrapper");rippleIcons.forEach((function(t){isTouchEnabled()?(t.addEventListener("touchstart",(function(e){t.classList.add("ripple-effect-start"),t.focus()})),t.addEventListener("touchend",(function(e){setTimeout((function(){t.classList.remove("ripple-effect-start"),t.classList.add("ripple-effect-end"),setTimeout((function(){t.classList.remove("ripple-effect-end")}),200)}),300)}))):(t.addEventListener("mousedown",(function(e){t.classList.add("ripple-effect-start")})),t.addEventListener("mouseup",(function(e){t.classList.remove("ripple-effect-start"),t.classList.add("ripple-effect-end"),setTimeout((function(){t.classList.remove("ripple-effect-end")}),200)})))}));const tabbarScrollArea=document.querySelector(".tabbar-scrollarea"),tabbarTabs=document.querySelectorAll("a.tabbar-tab");function scrollToTab(t,e=!1){if(!t)return;let a="smooth";e&&(tabbarScrollArea.parentNode.style.height="50px",body.classList.add("has-tabbar"),a="instant");let n=t.offsetWidth,i=t.offsetLeft,o=(tabbarScrollArea.offsetWidth-n)/2,s=0;isMobile()||(s=0);let l=i-o+s;tabbarScrollArea.scrollTo({left:l,behavior:a})}tabbarTabs.forEach((function(t){t.addEventListener("click",(function(e){tabbarTabs.forEach((function(t){t.classList.remove("active")})),t.classList.add("active"),scrollToTab(t,!1),setTimeout((function(){tabbarScrollArea.parentNode.style.height=0,body.classList.remove("has-tabbar")}),200)}))}));const tabbarScrollButtons=document.querySelectorAll(".tabbar-tab-scroll-btn");tabbarScrollButtons.forEach((function(t){t.addEventListener("click",(function(e){tabbarScrollArea.style.scrollBehavior="smooth","left"==t.dataset.type?tabbarScrollArea.scrollLeft-=50:tabbarScrollArea.scrollLeft+=50}))}));