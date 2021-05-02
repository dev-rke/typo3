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
var __importDefault=this&&this.__importDefault||function(t){return t&&t.__esModule?t:{default:t}};define(["require","exports","jquery","TYPO3/CMS/Core/Ajax/AjaxRequest","./Popover","bootstrap"],(function(t,e,a,o,l){"use strict";a=__importDefault(a);class s{constructor(){this.ajaxUrl=TYPO3.settings.ajaxUrls.context_help,this.trigger="click",this.placement="auto",this.selector=".help-link",this.initialize()}static resolveBackend(){return void 0!==window.opener&&null!==window.opener?window.opener.top:top}initialize(){const t=s.resolveBackend();void 0!==t.TYPO3.settings.ContextHelp&&(this.helpModuleUrl=t.TYPO3.settings.ContextHelp.moduleUrl),void 0===TYPO3.ShortcutMenu&&void 0===t.TYPO3.ShortcutMenu&&a.default(".icon-actions-system-shortcut-new").closest(".btn").hide();let e="&nbsp;";void 0!==t.TYPO3.lang&&(e=t.TYPO3.lang.csh_tooltip_loading);const o=a.default(this.selector);o.attr("data-loaded","false").attr("data-bs-html","true").attr("data-bs-original-title",e).attr("data-bs-placement",this.placement).attr("data-bs-trigger",this.trigger),l.popover(o),a.default(document).on("show.bs.popover",this.selector,t=>{const e=a.default(t.currentTarget),o=e.data("description");if(void 0!==o&&""!==o){const t={title:e.data("title")||"",content:o};l.setOptions(e,t)}else"false"===e.attr("data-loaded")&&e.data("table")&&this.loadHelp(e);e.closest(".t3js-module-docheader").length&&l.setOption(e,"placement","bottom")}).on("click",".help-has-link",t=>{a.default(".popover").each((e,o)=>{const l=a.default(o);l.has(t.target).length&&this.showHelpPopup(a.default('[aria-describedby="'+l.attr("id")+'"]'))})}).on("click","body",t=>{a.default(this.selector).each((e,o)=>{const s=a.default(o);s.is(t.target)||0!==s.has(t.target).length||0!==a.default(".popover").has(t.target).length||l.hide(s)})})}showHelpPopup(t){try{const e=window.open(this.helpModuleUrl+"&table="+t.data("table")+"&field="+t.data("field")+"&action=detail","ContextHelpWindow","height=400,width=600,status=0,menubar=0,scrollbars=1");return e.focus(),l.hide(t),e}catch(t){}}loadHelp(t){const e=t.data("table"),a=t.data("field");e&&new o(this.ajaxUrl).withQueryArguments({params:{action:"getContextHelp",table:e,field:a}}).get().then(async e=>{const a=await e.resolve(),o={title:a.title||"",content:a.content||"<p></p>"};l.setOptions(t,o),t.attr("data-loaded","true")})}}return new s}));