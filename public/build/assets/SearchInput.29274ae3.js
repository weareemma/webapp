import{o,e as n,b as f,f as m,r as h,_ as v,I as t,c as b,a as c,L as u,aA as w,Q as V}from"./app.45a2e0de.js";import{r as g}from"./RefreshIcon.76168562.js";function _(e,r){return o(),n("svg",{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor","aria-hidden":"true"},[f("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])}const y=m({inheritAttrs:!1,components:{SearchIcon:_,RefreshIcon:g},props:["modelValue","readonly","searching","bordered"],emits:["update:modelValue"],setup(e,{emit:r}){const a=h(null);function s(){r("update:modelValue",a.value.value)}function l(){this.$refs.input.focus()}return{input:a,focus:l,emitValue:s}}}),I={class:"absolute left-0 h-full w-10 grid place-items-center text-bb-gray-500"},$=["value"];function B(e,r,a,s,l,k){const d=t("SearchIcon"),i=t("RefreshIcon");return o(),b("div",{class:V(["relative w-full",e.$attrs.class])},[c("div",I,[e.searching?u("",!0):(o(),n(d,{key:0,class:"w-5 h-5"})),e.searching?(o(),n(i,{key:1,class:"w-5 h-5 animate-spin"})):u("",!0)]),c("input",w({class:["bb-form-control w-full rounded-full pl-10 px-[30px] py-[10px]",{readonly:e.readonly!==void 0,"border-0":e.bordered!==void 0}],value:e.modelValue},{placeholder:e.$attrs.placeholder},{onKeyup:r[0]||(r[0]=(...p)=>e.emitValue&&e.emitValue(...p)),ref:"input"}),null,16,$)],2)}var z=v(y,[["render",B]]);export{z as B};
