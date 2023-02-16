/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
import $ from"jquery";import AjaxRequest from"@typo3/core/ajax/ajax-request.js";import ContextMenuActions from"@typo3/backend/context-menu-actions.js";import DebounceEvent from"@typo3/core/event/debounce-event.js";import RegularEvent from"@typo3/core/event/regular-event.js";import ThrottleEvent from"@typo3/core/event/throttle-event.js";class ContextMenu{constructor(){this.mousePos={X:null,Y:null},this.record={uid:null,table:null},this.eventSources=[],this.storeMousePositionEvent=e=>{this.mousePos={X:e.pageX,Y:e.pageY}},document.addEventListener("click",(e=>{this.handleTriggerEvent(e)})),document.addEventListener("contextmenu",(e=>{this.handleTriggerEvent(e)})),new ThrottleEvent("mousemove",this.storeMousePositionEvent.bind(this),50).bindTo(document)}static drawActionItem(e){const t=e.additionalAttributes||{};let n="";for(const e of Object.entries(t)){const[t,o]=e;n+=" "+t+'="'+o+'"'}return'<li role="menuitem" class="context-menu-item" tabindex="-1" data-callback-action="'+e.callbackAction+'"'+n+'><span class="context-menu-item-icon">'+e.icon+'</span> <span class="context-menu-item-label">'+e.label+"</span></li>"}static within(e,t,n){const o=e.getBoundingClientRect(),s=window.pageXOffset||document.documentElement.scrollLeft,i=window.pageYOffset||document.documentElement.scrollTop,c=t>=o.left+s&&t<=o.left+s+o.width,a=n>=o.top+i&&n<=o.top+i+o.height;return c&&a}show(e,t,n,o,s,i=null){this.hideAll(),this.record={table:e,uid:t};const c=i.matches("a, button, [tabindex]")?i:i.closest("a, button, [tabindex]");this.eventSources.push(c);let a="";void 0!==e&&(a+="table="+encodeURIComponent(e)),void 0!==t&&(a+=(a.length>0?"&":"")+"uid="+("number"==typeof t?t:encodeURIComponent(t))),void 0!==n&&(a+=(a.length>0?"&":"")+"context="+encodeURIComponent(n)),this.fetch(a)}initializeContextMenuContainer(){if(0===$("#contentMenu0").length){const e='<div id="contentMenu0" class="context-menu" style="display: none;"></div><div id="contentMenu1" class="context-menu" data-parent="#contentMenu0" style="display: none;"></div>';$("body").append(e),document.querySelectorAll(".context-menu").forEach((e=>{new RegularEvent("mouseenter",(e=>{e.target;this.storeMousePositionEvent(e)})).bindTo(e),new DebounceEvent("mouseleave",(e=>{const t=e.target,n=document.querySelector('[data-parent="#'+t.id+'"]');if(!ContextMenu.within(t,this.mousePos.X,this.mousePos.Y)&&(null===n||null===n.offsetParent)){let e;this.hide("#"+t.id),void 0!==t.dataset.parent&&null!==(e=document.querySelector(t.dataset.parent))&&(ContextMenu.within(e,this.mousePos.X,this.mousePos.Y)||this.hide(t.dataset.parent))}}),500).bindTo(e)}))}}handleTriggerEvent(e){if(!(e.target instanceof Element))return;const t=e.target.closest("[data-contextmenu-trigger]");if(t instanceof HTMLElement)return void this.handleContextMenuEvent(e,t);const n=e.target.closest(".t3js-contextmenutrigger");if(n instanceof HTMLElement)return console.warn('Using the contextmenu trigger .t3js-contextmenutrigger is deprecated. Please use [data-contextmenu-trigger="click"] instead and prefix your config with "data-contextmenu-".'),void this.handleLegacyContextMenuEvent(e,n);e.target.closest(".context-menu")||this.hideAll()}handleContextMenuEvent(e,t){const n=t.dataset.contextmenuTrigger;"click"!==n&&n!==e.type||(e.preventDefault(),this.show(t.dataset.contextmenuTable??"",t.dataset.contextmenuUid??"",t.dataset.contextmenuContext??"","","",t))}handleLegacyContextMenuEvent(e,t){t.getAttribute("onclick")&&"click"===e.type||(e.preventDefault(),this.show(t.dataset.table??"",t.dataset.uid??"",t.dataset.context??"","","",t))}fetch(e){const t=TYPO3.settings.ajaxUrls.contextmenu;new AjaxRequest(t).withQueryArguments(e).get().then((async e=>{const t=await e.resolve();void 0!==e&&Object.keys(e).length>0&&this.populateData(t,0)}))}populateData(e,t){this.initializeContextMenuContainer();const n=$("#contentMenu"+t);if(n.length&&(0===t||$("#contentMenu"+(t-1)).is(":visible"))){const o=this.drawMenu(e,t);n.html('<ul class="context-menu-group" role="menu">'+o+"</ul>"),$("li.context-menu-item",n).on("click",(e=>{e.preventDefault();const n=e.currentTarget;if(n.classList.contains("context-menu-item-submenu"))return void this.openSubmenu(t,$(n),!1);const{callbackAction:o,callbackModule:s,...i}=n.dataset,c=new Proxy($(n),{get(e,t,n){console.warn(`\`this\` being bound to the selected context menu item is marked as deprecated. To access data attributes, use the 3rd argument passed to callback \`${o}\` in \`${s}\`.`);const i=e[t];return i instanceof Function?function(...t){return i.apply(this===n?e:this,t)}:i}});n.dataset.callbackModule?import(s+".js").then((({default:e})=>{e[o].bind(c)(this.record.table,this.record.uid,i)})):ContextMenuActions&&"function"==typeof ContextMenuActions[o]?ContextMenuActions[o].bind(c)(this.record.table,this.record.uid,i):console.log("action: "+o+" not found"),this.hideAll()})),$("li.context-menu-item",n).on("keydown",(e=>{const n=$(e.currentTarget);switch(e.key){case"Down":case"ArrowDown":this.setFocusToNextItem(n.get(0));break;case"Up":case"ArrowUp":this.setFocusToPreviousItem(n.get(0));break;case"Right":case"ArrowRight":if(!n.hasClass("context-menu-item-submenu"))return;this.openSubmenu(t,n,!0);break;case"Home":this.setFocusToFirstItem(n.get(0));break;case"End":this.setFocusToLastItem(n.get(0));break;case"Enter":case"Space":n.click();break;case"Esc":case"Escape":case"Left":case"ArrowLeft":this.hide("#"+n.parents(".context-menu").first().attr("id"));break;case"Tab":this.hideAll();break;default:return}e.preventDefault()})),n.css(this.getPosition(n,!1)).show(),$("li.context-menu-item[tabindex=-1]",n).first().focus()}}setFocusToPreviousItem(e){let t=this.getItemBackward(e.previousElementSibling);t||(t=this.getLastItem(e)),t.focus()}setFocusToNextItem(e){let t=this.getItemForward(e.nextElementSibling);t||(t=this.getFirstItem(e)),t.focus()}setFocusToFirstItem(e){let t=this.getFirstItem(e);t&&t.focus()}setFocusToLastItem(e){let t=this.getLastItem(e);t&&t.focus()}getItemBackward(e){for(;e&&(!e.classList.contains("context-menu-item")||"-1"!==e.getAttribute("tabindex"));)e=e.previousElementSibling;return e}getItemForward(e){for(;e&&(!e.classList.contains("context-menu-item")||"-1"!==e.getAttribute("tabindex"));)e=e.nextElementSibling;return e}getFirstItem(e){return this.getItemForward(e.parentElement.firstElementChild)}getLastItem(e){return this.getItemBackward(e.parentElement.lastElementChild)}openSubmenu(e,t,n){this.eventSources.push(t[0]);const o=$("#contentMenu"+(e+1)).html("");t.next().find(".context-menu-group").clone(!0).appendTo(o),o.css(this.getPosition(o,n)).show(),$(".context-menu-item[tabindex=-1]",o).first().focus()}getPosition(e,t){let n=0,o=0;if(this.eventSources.length&&(null===this.mousePos.X||t)){const e=this.eventSources[this.eventSources.length-1].getBoundingClientRect();n=this.eventSources.length>1?e.right:e.x,o=e.y}else n=this.mousePos.X-1,o=this.mousePos.Y-1;const s=$(window).width()-20,i=$(window).height(),c=e.width(),a=e.height(),r=n-$(document).scrollLeft(),u=o-$(document).scrollTop();return i-a<u&&(u>a?o-=a-10:o+=i-a-u),s-c<r&&(r>c?n-=c-10:s-c-r<$(document).scrollLeft()?n=$(document).scrollLeft():n+=s-c-r),{left:n+"px",top:o+"px"}}drawMenu(e,t){let n="";for(const o of Object.values(e))if("item"===o.type)n+=ContextMenu.drawActionItem(o);else if("divider"===o.type)n+='<li role="separator" class="context-menu-item context-menu-item-divider"></li>';else if("submenu"===o.type||o.childItems){n+='<li role="menuitem" aria-haspopup="true" class="context-menu-item context-menu-item-submenu" tabindex="-1"><span class="context-menu-item-icon">'+o.icon+'</span><span class="context-menu-item-label">'+o.label+'</span><span class="context-menu-item-indicator"><typo3-backend-icon identifier="actions-chevron-right" size="small"></typo3-backend-icon></span></li>';n+='<div class="context-menu contentMenu'+(t+1)+'" style="display:none;"><ul role="menu" class="context-menu-group">'+this.drawMenu(o.childItems,1)+"</ul></div>"}return n}hide(e){$(e).hide();const t=this.eventSources.pop();t&&$(t).focus()}hideAll(){this.hide("#contentMenu0"),this.hide("#contentMenu1")}}export default new ContextMenu;