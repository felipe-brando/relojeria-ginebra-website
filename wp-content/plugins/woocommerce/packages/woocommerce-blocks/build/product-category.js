this.wc=this.wc||{},this.wc.blocks=this.wc.blocks||{},this.wc.blocks["product-category"]=function(e){function t(t){for(var n,i,u=t[0],a=t[1],l=t[2],b=0,d=[];b<u.length;b++)i=u[b],Object.prototype.hasOwnProperty.call(c,i)&&c[i]&&d.push(c[i][0]),c[i]=0;for(n in a)Object.prototype.hasOwnProperty.call(a,n)&&(e[n]=a[n]);for(s&&s(t);d.length;)d.shift()();return o.push.apply(o,l||[]),r()}function r(){for(var e,t=0;t<o.length;t++){for(var r=o[t],n=!0,u=1;u<r.length;u++){var a=r[u];0!==c[a]&&(n=!1)}n&&(o.splice(t--,1),e=i(i.s=r[0]))}return e}var n={},c={30:0},o=[];function i(t){if(n[t])return n[t].exports;var r=n[t]={i:t,l:!1,exports:{}};return e[t].call(r.exports,r,r.exports,i),r.l=!0,r.exports}i.m=e,i.c=n,i.d=function(e,t,r){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(i.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)i.d(r,n,function(t){return e[t]}.bind(null,n));return r},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p="";var u=window.webpackWcBlocksJsonp=window.webpackWcBlocksJsonp||[],a=u.push.bind(u);u.push=t,u=u.slice();for(var l=0;l<u.length;l++)t(u[l]);var s=a;return o.push([739,0]),r()}({0:function(e,t){e.exports=window.wp.element},1:function(e,t){e.exports=window.wp.i18n},107:function(e,t,r){"use strict";r.d(t,"b",(function(){return c}));var n=r(3),c=["woocommerce/product-best-sellers","woocommerce/product-category","woocommerce/product-new","woocommerce/product-on-sale","woocommerce/product-top-rated"];t.a={columns:{type:"number",default:Object(n.getSetting)("default_columns",3)},rows:{type:"number",default:Object(n.getSetting)("default_rows",3)},alignButtons:{type:"boolean",default:!1},categories:{type:"array",default:[]},catOperator:{type:"string",default:"any"},contentVisibility:{type:"object",default:{title:!0,price:!0,rating:!0,button:!0}},isPreview:{type:"boolean",default:!1}}},11:function(e,t,r){"use strict";r.d(t,"q",(function(){return o})),r.d(t,"p",(function(){return i})),r.d(t,"o",(function(){return u})),r.d(t,"l",(function(){return l})),r.d(t,"e",(function(){return s})),r.d(t,"f",(function(){return b})),r.d(t,"i",(function(){return d})),r.d(t,"h",(function(){return p})),r.d(t,"n",(function(){return g})),r.d(t,"m",(function(){return f})),r.d(t,"c",(function(){return h})),r.d(t,"d",(function(){return w})),r.d(t,"g",(function(){return O})),r.d(t,"j",(function(){return E})),r.d(t,"a",(function(){return m})),r.d(t,"k",(function(){return j})),r.d(t,"b",(function(){return y})),r.d(t,"t",(function(){return _})),r.d(t,"u",(function(){return x})),r.d(t,"r",(function(){return k})),r.d(t,"s",(function(){return P}));var n,c=r(3),o=Object(c.getSetting)("wcBlocksConfig",{buildPhase:1,pluginUrl:"",productCount:0,defaultAvatar:"",restApiRoutes:{},wordCountType:"words"}),i=o.pluginUrl+"images/",u=o.pluginUrl+"build/",a=o.buildPhase,l=null===(n=c.STORE_PAGES.shop)||void 0===n?void 0:n.permalink,s=c.STORE_PAGES.checkout.id,b=c.STORE_PAGES.checkout.permalink,d=c.STORE_PAGES.privacy.permalink,p=c.STORE_PAGES.privacy.title,g=c.STORE_PAGES.terms.permalink,f=c.STORE_PAGES.terms.title,h=c.STORE_PAGES.cart.id,w=c.STORE_PAGES.cart.permalink,O=c.STORE_PAGES.myaccount.permalink?c.STORE_PAGES.myaccount.permalink:Object(c.getSetting)("wpLoginUrl","/wp-login.php"),E=Object(c.getSetting)("shippingCountries",{}),m=Object(c.getSetting)("allowedCountries",{}),j=Object(c.getSetting)("shippingStates",{}),y=Object(c.getSetting)("allowedStates",{}),v=r(25),_=function(e,t){if(a>2)return Object(v.registerBlockType)(e,t)},x=function(e,t){if(a>1)return Object(v.registerBlockType)(e,t)},k=function(){return a>2},P=function(){return a>1}},12:function(e,t){e.exports=window.React},122:function(e,t){},123:function(e,t){e.exports=window.wp.coreData},133:function(e,t,r){"use strict";var n=r(0),c=r(1),o=r(4);r(2),t.a=function(e){var t=e.value,r=e.setAttributes;return Object(n.createElement)(o.SelectControl,{label:Object(c.__)("Order products by",'woocommerce'),value:t,options:[{label:Object(c.__)("Newness - newest first",'woocommerce'),value:"date"},{label:Object(c.__)("Price - low to high",'woocommerce'),value:"price_asc"},{label:Object(c.__)("Price - high to low",'woocommerce'),value:"price_desc"},{label:Object(c.__)("Rating - highest first",'woocommerce'),value:"rating"},{label:Object(c.__)("Sales - most first",'woocommerce'),value:"popularity"},{label:Object(c.__)("Title - alphabetical",'woocommerce'),value:"title"},{label:Object(c.__)("Menu Order",'woocommerce'),value:"menu_order"}],onChange:function(e){return r({orderby:e})}})}},141:function(e,t){},15:function(e,t){e.exports=window.wp.apiFetch},16:function(e,t){e.exports=window.wp.data},186:function(e,t,r){"use strict";r.d(t,"a",(function(){return c}));var n=r(0),c=Object(n.createElement)("svg",{xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 230 250",style:{width:"100%"}},Object(n.createElement)("title",null,"Grid Block Preview"),Object(n.createElement)("rect",{width:"65.374",height:"65.374",x:".162",y:".779",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"47.266",height:"5.148",x:"9.216",y:"76.153",fill:"#E1E3E6",rx:"2.574"}),Object(n.createElement)("rect",{width:"62.8",height:"15",x:"1.565",y:"101.448",fill:"#E1E3E6",rx:"5"}),Object(n.createElement)("rect",{width:"65.374",height:"65.374",x:".162",y:"136.277",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"47.266",height:"5.148",x:"9.216",y:"211.651",fill:"#E1E3E6",rx:"2.574"}),Object(n.createElement)("rect",{width:"62.8",height:"15",x:"1.565",y:"236.946",fill:"#E1E3E6",rx:"5"}),Object(n.createElement)("rect",{width:"65.374",height:"65.374",x:"82.478",y:".779",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"47.266",height:"5.148",x:"91.532",y:"76.153",fill:"#E1E3E6",rx:"2.574"}),Object(n.createElement)("rect",{width:"62.8",height:"15",x:"83.882",y:"101.448",fill:"#E1E3E6",rx:"5"}),Object(n.createElement)("rect",{width:"65.374",height:"65.374",x:"82.478",y:"136.277",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"47.266",height:"5.148",x:"91.532",y:"211.651",fill:"#E1E3E6",rx:"2.574"}),Object(n.createElement)("rect",{width:"62.8",height:"15",x:"83.882",y:"236.946",fill:"#E1E3E6",rx:"5"}),Object(n.createElement)("rect",{width:"65.374",height:"65.374",x:"164.788",y:".779",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"47.266",height:"5.148",x:"173.843",y:"76.153",fill:"#E1E3E6",rx:"2.574"}),Object(n.createElement)("rect",{width:"62.8",height:"15",x:"166.192",y:"101.448",fill:"#E1E3E6",rx:"5"}),Object(n.createElement)("rect",{width:"65.374",height:"65.374",x:"164.788",y:"136.277",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"47.266",height:"5.148",x:"173.843",y:"211.651",fill:"#E1E3E6",rx:"2.574"}),Object(n.createElement)("rect",{width:"62.8",height:"15",x:"166.192",y:"236.946",fill:"#E1E3E6",rx:"5"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"13.283",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"21.498",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"29.713",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"37.927",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"46.238",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"95.599",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"103.814",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"112.029",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"120.243",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"128.554",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"177.909",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"186.124",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"194.339",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"202.553",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"210.864",y:"86.301",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"13.283",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"21.498",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"29.713",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"37.927",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"46.238",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"95.599",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"103.814",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"112.029",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"120.243",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"128.554",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"177.909",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"186.124",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"194.339",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"202.553",y:"221.798",fill:"#E1E3E6",rx:"3"}),Object(n.createElement)("rect",{width:"6.177",height:"6.177",x:"210.864",y:"221.798",fill:"#E1E3E6",rx:"3"}))},21:function(e,t){e.exports=window.wp.blockEditor},22:function(e,t){e.exports=window.wp.compose},23:function(e,t){e.exports=window.regeneratorRuntime},24:function(e,t){e.exports=window.wp.url},25:function(e,t){e.exports=window.wp.blocks},26:function(e,t){e.exports=window.wp.htmlEntities},3:function(e,t){e.exports=window.wc.wcSettings},30:function(e,t){e.exports=window.wp.primitives},32:function(e,t){e.exports=window.moment},36:function(e,t){e.exports=window.wp.dataControls},39:function(e,t,r){"use strict";r.d(t,"h",(function(){return p})),r.d(t,"e",(function(){return g})),r.d(t,"b",(function(){return f})),r.d(t,"i",(function(){return h})),r.d(t,"f",(function(){return w})),r.d(t,"c",(function(){return O})),r.d(t,"d",(function(){return E})),r.d(t,"g",(function(){return m})),r.d(t,"a",(function(){return j}));var n=r(5),c=r.n(n),o=r(24),i=r(15),u=r.n(i),a=r(7),l=r(3),s=r(11);function b(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function d(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?b(Object(r),!0).forEach((function(t){c()(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):b(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}var p=function(e){var t=e.selected,r=void 0===t?[]:t,n=e.search,c=void 0===n?"":n,i=e.queryArgs,l=function(e){var t=e.selected,r=void 0===t?[]:t,n=e.search,c=void 0===n?"":n,i=e.queryArgs,u=void 0===i?{}:i,a=s.q.productCount>100,l={per_page:a?100:0,catalog_visibility:"any",search:c,orderby:"title",order:"asc"},b=[Object(o.addQueryArgs)("/wc/store/products",d(d({},l),u))];return a&&r.length&&b.push(Object(o.addQueryArgs)("/wc/store/products",{catalog_visibility:"any",include:r,per_page:0})),b}({selected:r,search:c,queryArgs:void 0===i?{}:i});return Promise.all(l.map((function(e){return u()({path:e})}))).then((function(e){return Object(a.uniqBy)(Object(a.flatten)(e),"id").map((function(e){return d(d({},e),{},{parent:0})}))})).catch((function(e){throw e}))},g=function(e){return u()({path:"/wc/store/products/".concat(e)})},f=function(){return u()({path:"wc/store/products/attributes"})},h=function(e){return u()({path:"wc/store/products/attributes/".concat(e,"/terms")})},w=function(e){var t=e.selected,r=function(e){var t=e.selected,r=void 0===t?[]:t,n=e.search,c=Object(l.getSetting)("limitTags",!1),i=[Object(o.addQueryArgs)("wc/store/products/tags",{per_page:c?100:0,orderby:c?"count":"name",order:c?"desc":"asc",search:n})];return c&&r.length&&i.push(Object(o.addQueryArgs)("wc/store/products/tags",{include:r})),i}({selected:void 0===t?[]:t,search:e.search});return Promise.all(r.map((function(e){return u()({path:e})}))).then((function(e){return Object(a.uniqBy)(Object(a.flatten)(e),"id")}))},O=function(e){return u()({path:Object(o.addQueryArgs)("wc/store/products/categories",d({per_page:0},e))})},E=function(e){return u()({path:"wc/store/products/categories/".concat(e)})},m=function(e){return u()({path:Object(o.addQueryArgs)("wc/store/products",{per_page:0,type:"variation",parent:e})})},j=function(e,t){if(!e.title.raw)return e.slug;var r=1===t.filter((function(t){return t.title.raw===e.title.raw})).length;return e.title.raw+(r?"":" - ".concat(e.slug))}},4:function(e,t){e.exports=window.wp.components},42:function(e,t,r){"use strict";r.d(t,"a",(function(){return a})),r.d(t,"b",(function(){return l}));var n=r(31),c=r.n(n),o=r(23),i=r.n(o),u=r(1),a=function(){var e=c()(i.a.mark((function e(t){var r;return i.a.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if("function"!=typeof t.json){e.next=11;break}return e.prev=1,e.next=4,t.json();case 4:return r=e.sent,e.abrupt("return",{message:r.message,type:r.type||"api"});case 8:return e.prev=8,e.t0=e.catch(1),e.abrupt("return",{message:e.t0.message,type:"general"});case 11:return e.abrupt("return",{message:t.message,type:t.type||"general"});case 12:case"end":return e.stop()}}),e,null,[[1,8]])})));return function(_x){return e.apply(this,arguments)}}(),l=function(e){if(e.data&&"rest_invalid_param"===e.code){var t=Object.values(e.data.params);if(t[0])return t[0]}return(null==e?void 0:e.message)||Object(u.__)("Something went wrong. Please contact us to get assistance.",'woocommerce')}},48:function(e,t){e.exports=window.wp.escapeHtml},49:function(e,t){e.exports=window.wp.keycodes},52:function(e,t,r){"use strict";var n=r(0),c=r(1),o=(r(2),r(48));t.a=function(e){var t,r,i,u=e.error;return Object(n.createElement)("div",{className:"wc-block-error-message"},(r=(t=u).message,i=t.type,r?"general"===i?Object(n.createElement)("span",null,Object(c.__)("The following error was returned",'woocommerce'),Object(n.createElement)("br",null),Object(n.createElement)("code",null,Object(o.escapeHTML)(r))):"api"===i?Object(n.createElement)("span",null,Object(c.__)("The following error was returned from the API",'woocommerce'),Object(n.createElement)("br",null),Object(n.createElement)("code",null,Object(o.escapeHTML)(r))):r:Object(c.__)("An unknown error occurred which prevented the block from being updated.",'woocommerce')))}},54:function(e,t){e.exports=window.wp.deprecated},55:function(e,t){e.exports=window.wp.hooks},63:function(e,t,r){"use strict";var n=r(5),c=r.n(n),o=r(14),i=r.n(o),u=r(0),a=["srcElement","size"];function l(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}t.a=function(e){var t=e.srcElement,r=e.size,n=void 0===r?24:r,o=i()(e,a);return Object(u.isValidElement)(t)?Object(u.cloneElement)(t,function(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?l(Object(r),!0).forEach((function(t){c()(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):l(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}({width:n,height:n},o)):null}},7:function(e,t){e.exports=window.lodash},739:function(e,t,r){e.exports=r(850)},74:function(e,t){e.exports=window.wp.dom},740:function(e,t){},741:function(e,t,r){"use strict";var n=r(0),c=r(30),o=Object(n.createElement)(c.SVG,{xmlns:"http://www.w3.org/2000/SVG",viewBox:"0 0 24 24"},Object(n.createElement)("path",{fill:"none",d:"M0 0h24v24H0V0z"}),Object(n.createElement)("path",{d:"M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z"}));t.a=o},82:function(e,t){e.exports=window.wp.serverSideRender},85:function(e,t){e.exports=window.ReactDOM},850:function(e,t,r){"use strict";r.r(t);var n=r(5),c=r.n(n),o=r(0),i=r(1),u=r(25),a=r(7),l=r(63),s=r(741),b=(r(740),r(17)),d=r.n(b),p=r(18),g=r.n(p),f=r(13),h=r.n(f),w=r(19),O=r.n(w),E=r(20),m=r.n(E),j=r(10),y=r.n(j),v=r(21),_=r(82),x=r.n(_),k=r(4),P=(r(2),r(90)),C=r(91),S=r(96),A=r(133),R=r(186),T=r(3);function B(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function D(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?B(Object(r),!0).forEach((function(t){c()(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):B(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}var M=function(e){O()(u,e);var t,r,n=(t=u,r=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}(),function(){var e,n=y()(t);if(r){var c=y()(this).constructor;e=Reflect.construct(n,arguments,c)}else e=n.apply(this,arguments);return m()(this,e)});function u(){var e;d()(this,u);for(var t=arguments.length,r=new Array(t),o=0;o<t;o++)r[o]=arguments[o];return e=n.call.apply(n,[this].concat(r)),c()(h()(e),"state",{changedAttributes:{},isEditing:!1}),c()(h()(e),"startEditing",(function(){e.setState({isEditing:!0,changedAttributes:{}})})),c()(h()(e),"stopEditing",(function(){e.setState({isEditing:!1,changedAttributes:{}})})),c()(h()(e),"setChangedAttributes",(function(t){e.setState((function(e){return{changedAttributes:D(D({},e.changedAttributes),t)}}))})),c()(h()(e),"save",(function(){var t=e.state.changedAttributes;(0,e.props.setAttributes)(t),e.stopEditing()})),e}return g()(u,[{key:"componentDidMount",value:function(){this.props.attributes.categories.length||this.setState({isEditing:!0})}},{key:"getInspectorControls",value:function(){var e=this,t=this.props,r=t.attributes,n=t.setAttributes,c=this.state.isEditing,u=r.columns,a=r.catOperator,l=r.contentVisibility,s=r.orderby,b=r.rows,d=r.alignButtons;return Object(o.createElement)(v.InspectorControls,{key:"inspector"},Object(o.createElement)(k.PanelBody,{title:Object(i.__)("Product Category",'woocommerce'),initialOpen:!r.categories.length&&!c},Object(o.createElement)(S.a,{selected:r.categories,onChange:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],r=t.map((function(e){return e.id})),c={categories:r};n(c),e.setChangedAttributes(c)},operator:a,onOperatorChange:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"any",r={catOperator:t};n(r),e.setChangedAttributes(r)},isCompact:!0})),Object(o.createElement)(k.PanelBody,{title:Object(i.__)("Layout",'woocommerce'),initialOpen:!0},Object(o.createElement)(C.a,{columns:u,rows:b,alignButtons:d,setAttributes:n,minColumns:Object(T.getSetting)("min_columns",1),maxColumns:Object(T.getSetting)("max_columns",6),minRows:Object(T.getSetting)("min_rows",1),maxRows:Object(T.getSetting)("max_rows",6)})),Object(o.createElement)(k.PanelBody,{title:Object(i.__)("Content",'woocommerce'),initialOpen:!0},Object(o.createElement)(P.a,{settings:l,onChange:function(e){return n({contentVisibility:e})}})),Object(o.createElement)(k.PanelBody,{title:Object(i.__)("Order By",'woocommerce'),initialOpen:!1},Object(o.createElement)(A.a,{setAttributes:n,value:s})))}},{key:"renderEditMode",value:function(){var e=this,t=this.props,r=t.attributes,n=t.debouncedSpeak,c=this.state.changedAttributes,u=D(D({},r),c);return Object(o.createElement)(k.Placeholder,{icon:Object(o.createElement)(l.a,{srcElement:s.a}),label:Object(i.__)("Products by Category",'woocommerce'),className:"wc-block-products-grid wc-block-products-category"},Object(i.__)("Display a grid of products from your selected categories.",'woocommerce'),Object(o.createElement)("div",{className:"wc-block-products-category__selection"},Object(o.createElement)(S.a,{selected:u.categories,onChange:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],r=t.map((function(e){return e.id}));e.setChangedAttributes({categories:r})},operator:u.catOperator,onOperatorChange:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"any";return e.setChangedAttributes({catOperator:t})}}),Object(o.createElement)(k.Button,{isPrimary:!0,onClick:function(){e.save(),n(Object(i.__)("Showing Products by Category block preview.",'woocommerce'))}},Object(i.__)("Done",'woocommerce')),Object(o.createElement)(k.Button,{className:"wc-block-products-category__cancel-button",isTertiary:!0,onClick:function(){e.stopEditing(),n(Object(i.__)("Showing Products by Category block preview.",'woocommerce'))}},Object(i.__)("Cancel",'woocommerce'))))}},{key:"renderViewMode",value:function(){var e=this.props,t=e.attributes,r=e.name,n=t.categories.length;return Object(o.createElement)(k.Disabled,null,n?Object(o.createElement)(x.a,{block:r,attributes:t,EmptyResponsePlaceholder:function(){return Object(o.createElement)(k.Placeholder,{icon:Object(o.createElement)(l.a,{srcElement:s.a}),label:Object(i.__)("Products by Category",'woocommerce'),className:"wc-block-products-grid wc-block-products-category"},Object(i.__)("No products were found that matched your selection.",'woocommerce'))}}):Object(i.__)("Select at least one category to display its products.",'woocommerce'))}},{key:"render",value:function(){var e=this,t=this.state.isEditing;return this.props.attributes.isPreview?R.a:Object(o.createElement)(o.Fragment,null,Object(o.createElement)(v.BlockControls,null,Object(o.createElement)(k.ToolbarGroup,{controls:[{icon:"edit",title:Object(i.__)("Edit"),onClick:function(){return t?e.stopEditing():e.startEditing()},isActive:t}]})),this.getInspectorControls(),t?this.renderEditMode():this.renderViewMode())}}]),u}(o.Component),N=Object(k.withSpokenMessages)(M),G=r(107);function V(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function L(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?V(Object(r),!0).forEach((function(t){c()(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):V(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}Object(u.registerBlockType)("woocommerce/product-category",{title:Object(i.__)("Products by Category",'woocommerce'),icon:{src:Object(o.createElement)(l.a,{srcElement:s.a}),foreground:"#96588a"},category:"woocommerce",keywords:[Object(i.__)("WooCommerce",'woocommerce')],description:Object(i.__)("Display a grid of products from your selected categories.",'woocommerce'),supports:{align:["wide","full"],html:!1},example:{attributes:{isPreview:!0}},attributes:L(L({},G.a),{},{editMode:{type:"boolean",default:!0},orderby:{type:"string",default:"date"}}),transforms:{from:[{type:"block",blocks:Object(a.without)(G.b,"woocommerce/product-category"),transform:function(e){return Object(u.createBlock)("woocommerce/product-category",L(L({},e),{},{editMode:!1}))}}]},edit:function(e){return Object(o.createElement)(N,e)},save:function(){return null}})},87:function(e,t){e.exports=window.wp.viewport},90:function(e,t,r){"use strict";var n=r(5),c=r.n(n),o=r(0),i=r(1),u=(r(2),r(4));function a(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function l(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?a(Object(r),!0).forEach((function(t){c()(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):a(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}t.a=function(e){var t=e.onChange,r=e.settings,n=r.button,c=r.price,a=r.rating,s=r.title;return Object(o.createElement)(o.Fragment,null,Object(o.createElement)(u.ToggleControl,{label:Object(i.__)("Product title",'woocommerce'),help:s?Object(i.__)("Product title is visible.",'woocommerce'):Object(i.__)("Product title is hidden.",'woocommerce'),checked:s,onChange:function(){return t(l(l({},r),{},{title:!s}))}}),Object(o.createElement)(u.ToggleControl,{label:Object(i.__)("Product price",'woocommerce'),help:c?Object(i.__)("Product price is visible.",'woocommerce'):Object(i.__)("Product price is hidden.",'woocommerce'),checked:c,onChange:function(){return t(l(l({},r),{},{price:!c}))}}),Object(o.createElement)(u.ToggleControl,{label:Object(i.__)("Product rating",'woocommerce'),help:a?Object(i.__)("Product rating is visible.",'woocommerce'):Object(i.__)("Product rating is hidden.",'woocommerce'),checked:a,onChange:function(){return t(l(l({},r),{},{rating:!a}))}}),Object(o.createElement)(u.ToggleControl,{label:Object(i.__)("Add to Cart button",'woocommerce'),help:n?Object(i.__)("Add to Cart button is visible.",'woocommerce'):Object(i.__)("Add to Cart button is hidden.",'woocommerce'),checked:n,onChange:function(){return t(l(l({},r),{},{button:!n}))}}))}},91:function(e,t,r){"use strict";var n=r(0),c=r(1),o=r(7),i=(r(2),r(4));t.a=function(e){var t=e.columns,r=e.rows,u=e.setAttributes,a=e.alignButtons,l=e.minColumns,s=void 0===l?1:l,b=e.maxColumns,d=void 0===b?6:b,p=e.minRows,g=void 0===p?1:p,f=e.maxRows,h=void 0===f?6:f;return Object(n.createElement)(n.Fragment,null,Object(n.createElement)(i.RangeControl,{label:Object(c.__)("Columns",'woocommerce'),value:t,onChange:function(e){var t=Object(o.clamp)(e,s,d);u({columns:Number.isNaN(t)?"":t})},min:s,max:d}),Object(n.createElement)(i.RangeControl,{label:Object(c.__)("Rows",'woocommerce'),value:r,onChange:function(e){var t=Object(o.clamp)(e,g,h);u({rows:Number.isNaN(t)?"":t})},min:g,max:h}),Object(n.createElement)(i.ToggleControl,{label:Object(c.__)("Align Last Block",'woocommerce'),help:a?Object(c.__)("The last inner block will be aligned vertically.",'woocommerce'):Object(c.__)("The last inner block will follow other content.",'woocommerce'),checked:a,onChange:function(){return u({alignButtons:!a})}}))}},95:function(e,t){e.exports=window.wp.date},96:function(e,t,r){"use strict";var n=r(9),c=r.n(n),o=r(0),i=r(1),u=(r(2),r(47)),a=r(4),l=r(31),s=r.n(l),b=r(17),d=r.n(b),p=r(18),g=r.n(p),f=r(13),h=r.n(f),w=r(19),O=r.n(w),E=r(20),m=r.n(E),j=r(10),y=r.n(j),v=r(23),_=r.n(v),x=r(22),k=r(39),P=r(42);var C=Object(x.createHigherOrderComponent)((function(e){return function(t){O()(u,t);var r,n,i=(r=u,n=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}(),function(){var e,t=y()(r);if(n){var c=y()(this).constructor;e=Reflect.construct(t,arguments,c)}else e=t.apply(this,arguments);return m()(this,e)});function u(){var e;return d()(this,u),(e=i.apply(this,arguments)).state={error:null,loading:!1,categories:[]},e.loadCategories=e.loadCategories.bind(h()(e)),e}return g()(u,[{key:"componentDidMount",value:function(){this.loadCategories()}},{key:"loadCategories",value:function(){var e=this;this.setState({loading:!0}),Object(k.c)().then((function(t){e.setState({categories:t,loading:!1,error:null})})).catch(function(){var t=s()(_.a.mark((function t(r){var n;return _.a.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,Object(P.a)(r);case 2:n=t.sent,e.setState({categories:[],loading:!1,error:n});case 4:case"end":return t.stop()}}),t)})));return function(_x){return t.apply(this,arguments)}}())}},{key:"render",value:function(){var t=this.state,r=t.error,n=t.loading,i=t.categories;return Object(o.createElement)(e,c()({},this.props,{error:r,isLoading:n,categories:i}))}}]),u}(o.Component)}),"withCategories"),S=r(52),A=r(6),R=r.n(A),T=(r(141),function(e){var t=e.categories,r=e.error,n=e.isLoading,l=e.onChange,s=e.onOperatorChange,b=e.operator,d=e.selected,p=e.isCompact,g=e.isSingle,f=e.showReviewCount,h={clear:Object(i.__)("Clear all product categories",'woocommerce'),list:Object(i.__)("Product Categories",'woocommerce'),noItems:Object(i.__)("Your store doesn't have any product categories.",'woocommerce'),search:Object(i.__)("Search for product categories",'woocommerce'),selected:function(e){return Object(i.sprintf)(
/* translators: %d is the count of selected categories. */
Object(i._n)("%d category selected","%d categories selected",e,'woocommerce'),e)},updated:Object(i.__)("Category search results updated.",'woocommerce')};return r?Object(o.createElement)(S.a,{error:r}):Object(o.createElement)(o.Fragment,null,Object(o.createElement)(u.b,{className:"woocommerce-product-categories",list:t,isLoading:n,selected:d.map((function(e){return t.find((function(t){return t.id===e}))})).filter(Boolean),onChange:l,renderItem:function(e){var t=e.item,r=e.search,n=e.depth,a=void 0===n?0:n,l=t.breadcrumbs.length?"".concat(t.breadcrumbs.join(", "),", ").concat(t.name):t.name,s=f?Object(i.sprintf)(
/* translators: %1$s is the item name, %2$d is the count of reviews for the item. */
Object(i._n)("%1$s, has %2$d review","%1$s, has %2$d reviews",t.review_count,'woocommerce'),l,t.review_count):Object(i.sprintf)(
/* translators: %1$s is the item name, %2$d is the count of products for the item. */
Object(i._n)("%1$s, has %2$d product","%1$s, has %2$d products",t.count,'woocommerce'),l,t.count),b=f?Object(i.sprintf)(
/* translators: %d is the count of reviews. */
Object(i._n)("%d review","%d reviews",t.review_count,'woocommerce'),t.review_count):Object(i.sprintf)(
/* translators: %d is the count of products. */
Object(i._n)("%d product","%d products",t.count,'woocommerce'),t.count);return Object(o.createElement)(u.c,c()({className:R()("woocommerce-product-categories__item","has-count",{"is-searching":r.length>0,"is-skip-level":0===a&&0!==t.parent})},e,{countLabel:b,"aria-label":s}))},messages:h,isCompact:p,isHierarchical:!0,isSingle:g}),!!s&&Object(o.createElement)("div",{hidden:d.length<2},Object(o.createElement)(a.SelectControl,{className:"woocommerce-product-categories__operator",label:Object(i.__)("Display products matching",'woocommerce'),help:Object(i.__)("Pick at least two categories to use this setting.",'woocommerce'),value:b,onChange:s,options:[{label:Object(i.__)("Any selected categories",'woocommerce'),value:"any"},{label:Object(i.__)("All selected categories",'woocommerce'),value:"all"}]})))});T.defaultProps={operator:"any",isCompact:!1,isSingle:!1},t.a=C(T)}});