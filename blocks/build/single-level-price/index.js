(()=>{var e={942:(e,t)=>{var r;!function(){"use strict";var n={}.hasOwnProperty;function o(){for(var e="",t=0;t<arguments.length;t++){var r=arguments[t];r&&(e=a(e,l(r)))}return e}function l(e){if("string"==typeof e||"number"==typeof e)return e;if("object"!=typeof e)return"";if(Array.isArray(e))return o.apply(null,e);if(e.toString!==Object.prototype.toString&&!e.toString.toString().includes("[native code]"))return e.toString();var t="";for(var r in e)n.call(e,r)&&e[r]&&(t=a(t,r));return t}function a(e,t){return t?e?e+" "+t:e+t:e}e.exports?(o.default=o,e.exports=o):void 0===(r=function(){return o}.apply(t,[]))||(e.exports=r)}()}},t={};function r(n){var o=t[n];if(void 0!==o)return o.exports;var l=t[n]={exports:{}};return e[n](l,l.exports,r),l.exports}r.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return r.d(t,{a:t}),t},r.d=(e,t)=>{for(var n in t)r.o(t,n)&&!r.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},r.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{"use strict";const e=window.wp.blocks,t=window.wp.element;var n=r(942),o=r.n(n);const l=window.wp.i18n,a=window.wp.blockEditor,i=JSON.parse('{"UU":"pmpro/single-level-price"}');(0,e.registerBlockType)(i.UU,{icon:{background:"#FFFFFF",foreground:"#1A688B",src:"money-alt"},edit:function(e){const{attributes:{textAlign:r},setAttributes:n}=e,i=(0,a.useBlockProps)({className:o()({[`has-text-align-${r}`]:r})});let s;const c=(p=e.attributes.selected_membership_level,pmpro.all_levels_formatted_text[p]?pmpro.all_levels_formatted_text[p].formatted_price:null);var p;return s=c?(0,t.createElement)("div",i,(0,t.createElement)(t.RawHTML,null,c)):(0,t.createElement)("div",i,(0,l.__)("Level Price","paid-memberships-pro")),[(0,t.createElement)(t.Fragment,null,(0,t.createElement)(t.Fragment,null,(0,t.createElement)(a.BlockControls,null,(0,t.createElement)(a.AlignmentControl,{value:r,onChange:e=>{n({textAlign:e})}}))),s)]}})})()})();