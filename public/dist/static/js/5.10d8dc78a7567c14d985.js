webpackJsonp([5],{HEW3:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=n("cKc3"),i=n("jV6A"),o=n("6oH0"),r={data:function(){return{openid:""}},methods:{getUrlParame:function(e){var t=window.location.search;if(t.indexOf(e)>-1){var n="";return(n=t.substring(t.indexOf(e),t.length)).indexOf("&")>-1?n=(n=n.substring(0,n.indexOf("&"))).replace(e+"=",""):""}}},mounted:function(){},created:function(){var e=this;i.a.setOpenid("");var t=this.getUrlParame("code");if(t)a.a.ajaxGet("wechat_oauth",{code:t},function(t){"success"===t.status?(e.openid=t.data.openid,i.a.setOpenid(e.openid),console.log(window.location.href.split("#")[0]+i.a.getToUrl()),window.location.replace(window.location.href.split("#")[0]+i.a.getToUrl())):Object(o.a)(t.message)});else{var n=encodeURI(window.location.href.split("#")[0]);a.a.ajaxGet("wechat_oauth_url",{redirect_url:n},function(e){if("success"===e.status){var t=/http\S*t/g.exec(e.data.url);t=t[0].replace(/\\u0026/g,"&"),window.location.href=t}else Object(o.a)(e.message)})}}},c={render:function(){var e=this.$createElement;return(this._self._c||e)("div")},staticRenderFns:[]},s=n("C7Lr")(r,c,!1,null,null,null);t.default=s.exports}});
//# sourceMappingURL=5.10d8dc78a7567c14d985.js.map