"use strict";const body=document.getElementsByTagName("BODY")[0],header=document.getElementById("masthead"),menuButton=document.getElementById("menu-toggle"),navigation=document.getElementById("site-navigation"),navigationOverlay=document.getElementById("navigation-overlay");if(/^(iPhone|iPad|iPod)/.test(navigator.platform)&&body.classList.add("ios"),navigation&&header&&navigationOverlay){let t=navigation.getElementsByTagName("ul")[0],e=.8,n=200,o=document.querySelector("#menu-toggle .bar:nth-child(1)"),i=document.querySelector("#menu-toggle .bar:nth-child(2)"),a=document.querySelector("#menu-toggle .bar:nth-child(3)"),s=document.querySelector("#menu-toggle .bar:nth-child(4)");function openMenu(){resetCross(),menuButton.setAttribute("aria-expanded","true"),t.setAttribute("aria-expanded","true"),menuButton.classList.add("open"),body.classList.add("mobile-navigation-open"),navigationOverlay.classList.add("visible"),navigationOverlay.style.removeProperty("transition-duration"),navigationOverlay.style.opacity=e,navigation.style.removeProperty("transition-duration"),navigation.style.transform="translateX(0px)",navigation.classList.add("toggled"),m=n}function closeMenu(){resetCross(),menuButton.setAttribute("aria-expanded","false"),t.setAttribute("aria-expanded","false"),menuButton.classList.remove("open"),body.classList.remove("mobile-navigation-open"),navigationOverlay.style.removeProperty("transition-duration"),navigationOverlay.style.opacity=0,setTimeout((function(t){navigationOverlay.classList.remove("visible")}),200),navigation.style.removeProperty("transition-duration"),navigation.style.removeProperty("transform"),navigation.classList.remove("toggled"),m=0}function resetCross(){o.style.removeProperty("transition-duration"),o.style.removeProperty("top"),o.style.removeProperty("width"),o.style.removeProperty("left"),i.style.removeProperty("transition-duration"),i.style.removeProperty("transform"),a.style.removeProperty("transition-duration"),a.style.removeProperty("transform"),s.style.removeProperty("transition-duration"),s.style.removeProperty("top"),s.style.removeProperty("width"),s.style.removeProperty("left")}menuButton.addEventListener("click",(function(t){navigation.classList.contains("toggled")?closeMenu():openMenu()})),document.addEventListener("touchstart",handleTouchStart,!1),document.addEventListener("touchmove",handleTouchMove,!1),document.addEventListener("touchend",handleTouchEnd,!1);let r=null,l=null,u=null,d=null,v=null,y=null,c=50,g=50,m=null,p=!1;function handleTouchStart(t){const e=t.touches[0];r=e.clientX,u=e.clientY,v=window.innerWidth-r<c&&!navigation.classList.contains("toggled"),y=navigation.classList.contains("toggled"),p=!1}function handleTouchMove(t){if(!r)return;let e=l;l=t.touches[0].clientX,d=t.touches[0].clientY;let n=navigation.classList.contains("toggled"),o=Math.abs(r-l);Math.abs(u-d);if(p=!1,n&&o<g)return void(p=!0);let i=window.innerWidth-l,a=r>l;a&&v&&(moveNavigationToPosition(i),e&&e<l&&(r=e,y=!0)),!a&&y&&(moveNavigationToPosition(i),e&&e>l&&(r=e,v=!0))}function moveNavigationToPosition(t){t>n&&(t=n),t<0&&(t=0),navigation.style.transitionDuration="0s",navigation.style.transform="translateX("+(n-t)+"px)",m=t;let r=t/n;r<=0&&(r=0),r>=1&&(r=1),navigationOverlay.classList.add("visible"),navigationOverlay.style.transitionDuration="0s",navigationOverlay.style.opacity=r*e,o.style.transitionDuration="0s",o.style.top=4+4*r+"px",o.style.width=100*(1-r)+"%",o.style.left=50*r+"%",i.style.transitionDuration="0s",i.style.transform="rotate("+45*r+"deg)",a.style.transitionDuration="0s",a.style.transform="rotate(-"+45*r+"deg)",s.style.transitionDuration="0s",s.style.top=20-4*r+"px",s.style.width=100*(1-r)+"%",s.style.left=50*r+"%"}function handleTouchEnd(t){if(!r||!l)return;let e=navigation.classList.contains("toggled");p?m>0&&(e?openMenu():closeMenu()):e?r<l&&y?closeMenu():openMenu():r>l&&v?openMenu():closeMenu(),r=null,v=null,y=null,l=null}}