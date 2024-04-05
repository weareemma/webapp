import{a9 as F,r as E,g as x,o as i,e as q,w as r,a as n,b as t,U as a,c as d,J as L,S as b,K as U,L as A,q as M,Y as C,I as c,aC as O}from"./app.c248d67c.js";import{_ as T}from"./AppLayout.4269b78a.js";import"./Logo.d971df1b.js";import"./wizardStore.2226bc2a.js";import"./menu.6da08311.js";import"./ChevronDownIcon.f3847a45.js";import"./CheckIcon.5f685430.js";import"./XIcon.0ce515ba.js";const $={class:"flex justify-start items-center mt-4 mb-6"},J={class:"bb-card p-8"},K={class:"text-bb-blue-500 mb-4 big-header-title"},R={key:0},Y={key:1},G={class:"grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4"},H=b("Nome"),Q={class:"col-span-2"},W=b("DURATE"),X={class:"flex justify-start items-baseline"},Z=["onClick"],ee={class:"sm:col-span-2 flex justify-start items-center mt-2"},te=b("Durata attiva"),oe={class:"col-span-2"},se=b("Descrizione"),ae={class:"sm:col-span-2 flex justify-start items-center"},le=b("Abbonamento attivo"),ne={class:"flex justify-end items-center mt-4"},ie={key:0},re={key:1},ve={__name:"Form",props:{model:Object,availableDurations:Array},setup(w){var g;const h=w,o=F(h.model);function S(){const l=o.transform(e=>({...e,pricings:m.value.filter(p=>!p.deleted||!p.new)}));o.id?l.put(route("plan.update",o.id),{preserveScroll:!0,onSuccess:e=>{C.flash(e.props.flash)}}):l.post(route("plan.store"),{preserveScroll:!0,onSuccess:e=>{C.flash(e.props.flash)}})}const m=E((g=o.pricings)!=null?g:[]),D=x(()=>m.value.filter(l=>!l.deleted));function j(){var e;let l={new:!0,id:O(),duration:"",price:null,active:!0};((e=m.value)==null?void 0:e.length)>=4||m.value.push(l)}function z(l){const e=m.value.findIndex(p=>p.id==l);e>-1&&(m.value[e].deleted=!0)}const N=x(()=>{const l={};return h.availableDurations.forEach(e=>{switch(e){case"1:month":l[e]="1 mese";break;case"3:month":l[e]="3 mesi";break;case"6:month":l[e]="6 mesi";break;case"1:year":l[e]="1 anno";break}}),l});return(l,e)=>{const p=c("bb-back-link"),u=c("bb-label"),V=c("bb-input"),_=c("bb-input-validation"),I=c("bb-select"),k=c("bb-switch"),P=c("bb-textarea"),B=c("bb-button");return i(),q(T,{title:"Abbonamento","show-title":!1},{default:r(()=>{var y;return[n("div",$,[t(p)]),n("div",J,[n("h1",K,[a(o).id?(i(),d("span",R,"Modifica abbonamento")):(i(),d("span",Y,"Aggiungi nuovo abbonamento"))]),n("div",G,[n("div",null,[t(u,{class:"mb-1"},{default:r(()=>[H]),_:1}),t(V,{type:"text",placeholder:"Nome",modelValue:a(o).name,"onUpdate:modelValue":e[0]||(e[0]=s=>a(o).name=s)},null,8,["modelValue"]),t(_,{form:a(o),name:"name"},null,8,["form"])]),n("div",Q,[t(u,{class:"mb-1 text-bb-gray-700"},{default:r(()=>[W]),_:1}),(i(!0),d(M,null,L(a(D),(s,v)=>(i(),d("div",{class:"my-5 grid grid-cols-1 sm:grid-cols-2 gap-x-5",key:s.id},[n("div",null,[n("div",X,[t(u,{class:"mb-1"},{default:r(()=>[b("Durata "+U(v+1),1)]),_:2},1024),s.stripe_price_id===void 0?(i(),d("button",{key:0,link:"",onClick:f=>z(s.id),class:"bb-link-primary cursor-pointer text-bb-danger text-sm mb-1 ml-2"}," Cancella ",8,Z)):A("",!0)]),t(I,{mode:"single",placeholder:"Seleziona la durata","close-on-select":!0,options:a(N),modelValue:s.duration,"onUpdate:modelValue":f=>s.duration=f,disabled:s.stripe_price_id!==void 0},null,8,["options","modelValue","onUpdate:modelValue","disabled"]),t(_,{form:a(o),name:"pricings."+v+".duration"},null,8,["form","name"])]),n("div",null,[t(u,{class:"mb-1"},{default:r(()=>[b("Prezzo "+U(v+1)+" (IVA inclusa)",1)]),_:2},1024),t(V,{type:"number",placeholder:"Prezzo IVA inclusa",modelValue:s.price,"onUpdate:modelValue":f=>s.price=f,disabled:s.stripe_price_id!==void 0},null,8,["modelValue","onUpdate:modelValue","disabled"]),t(_,{form:a(o),name:"pricings."+v+".price"},null,8,["form","name"])]),n("div",ee,[t(k,{modelValue:s.active,"onUpdate:modelValue":f=>s.active=f},null,8,["modelValue","onUpdate:modelValue"]),t(u,{class:"mb-0 ml-2"},{default:r(()=>[te]),_:1})])]))),128)),((y=m.value)==null?void 0:y.length)<4?(i(),d("button",{key:0,link:"",onClick:j,class:"bb-link-primary cursor-pointer text-bb-blue-500"}," + Aggiungi durata ")):A("",!0),t(_,{form:a(o),name:"pricings"},null,8,["form"])]),n("div",oe,[t(u,{class:"mb-1"},{default:r(()=>[se]),_:1}),t(P,{class:"min-h-[180px]",type:"text",modelValue:a(o).description,"onUpdate:modelValue":e[1]||(e[1]=s=>a(o).description=s)},null,8,["modelValue"]),t(_,{form:a(o),name:"description"},null,8,["form"])]),n("div",ae,[t(k,{modelValue:a(o).active,"onUpdate:modelValue":e[2]||(e[2]=s=>a(o).active=s)},null,8,["modelValue"]),t(u,{class:"mb-0 ml-2"},{default:r(()=>[le]),_:1})]),t(_,{form:a(o),name:"active"},null,8,["form"])]),n("div",ne,[t(B,{type:"button",onClick:S,disabled:a(o).processing},{default:r(()=>[a(o).id?(i(),d("span",ie,"Salva")):(i(),d("span",re,"Aggiungi"))]),_:1},8,["disabled"])])])]}),_:1})}}};export{ve as default};