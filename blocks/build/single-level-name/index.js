(()=>{var e={942:(e,t)=>{var r;!function(){"use strict";var n={}.hasOwnProperty;function o(){for(var e="",t=0;t<arguments.length;t++){var r=arguments[t];r&&(e=a(e,l(r)))}return e}function l(e){if("string"==typeof e||"number"==typeof e)return e;if("object"!=typeof e)return"";if(Array.isArray(e))return o.apply(null,e);if(e.toString!==Object.prototype.toString&&!e.toString.toString().includes("[native code]"))return e.toString();var t="";for(var r in e)n.call(e,r)&&e[r]&&(t=a(t,r));return t}function a(e,t){return t?e?e+" "+t:e+t:e}e.exports?(o.default=o,e.exports=o):void 0===(r=function(){return o}.apply(t,[]))||(e.exports=r)}()}},t={};function r(n){var o=t[n];if(void 0!==o)return o.exports;var l=t[n]={exports:{}};return e[n](l,l.exports,r),l.exports}r.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return r.d(t,{a:t}),t},r.d=(e,t)=>{for(var n in t)r.o(t,n)&&!r.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},r.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{"use strict";const e=window.wp.blocks;function t(){return t=Object.assign?Object.assign.bind():function(e){for(var t=1;t<arguments.length;t++){var r=arguments[t];for(var n in r)Object.prototype.hasOwnProperty.call(r,n)&&(e[n]=r[n])}return e},t.apply(this,arguments)}const n=window.wp.element;var o=r(942),l=r.n(o);const a=window.wp.i18n,i=window.wp.blockEditor,s=JSON.parse('{"UU":"pmpro/single-level-name"}');(0,e.registerBlockType)(s.UU,{icon:{background:"#FFFFFF",foreground:"#1A688B",src:"heading"},edit:function(e){const{attributes:{textAlign:r,level:o},setAttributes:s}=e,p="h"+o,u=(0,i.useBlockProps)({className:l()({[`has-text-align-${r}`]:r})});let c;const m=(e=>pmpro.all_levels_formatted_text[e]?pmpro.all_levels_formatted_text[e].name:null)(e.attributes.selected_membership_level);return c=m?(0,n.createElement)(p,t({},u,{dangerouslySetInnerHTML:{__html:m}})):(0,n.createElement)(p,u,(0,a.__)("Level Name","paid-memberships-pro")),[(0,n.createElement)(n.Fragment,null,(0,n.createElement)(n.Fragment,null,(0,n.createElement)(i.BlockControls,null,(0,n.createElement)(i.HeadingLevelDropdown,{value:o,onChange:e=>s({level:e})}),(0,n.createElement)(i.AlignmentControl,{value:r,onChange:e=>{s({textAlign:e})}}))),c)]},save:function(e){return t=e.attributes.selected_membership_level,pmpro.all_levels_formatted_text[t]?pmpro.all_levels_formatted_text[t].name:"";var t}})})()})();