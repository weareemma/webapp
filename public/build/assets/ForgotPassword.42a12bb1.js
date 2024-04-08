import{_ as v,a9 as w,o as l,c as n,b as o,U as e,w as a,q as x,$ as y,a as s,a4 as _,Q as u,K as S,L as k,ae as I,S as d,I as p,an as L,ao as V}from"./app.c248d67c.js";import{J as C}from"./AuthenticationCard.b07d9ed6.js";import{L as F}from"./LoginLayout.55859e79.js";import{L as z}from"./Logo.d971df1b.js";const r=i=>(L("data-v-97d95f20"),i=i(),V(),i),N={key:0},P=r(()=>s("h3",{class:"text-center mt-4 mb-5"},"Controlla la tua email!",-1)),q=r(()=>s("h6",{class:"text-center mb-8"},"Abbiamo inviato un\u2019email all\u2019indirizzo che ci hai indicato. Segui le istruzioni per reimpostare la password.",-1)),B={class:"text-center mb-2"},T=d("Non hai ricevuto l\u2019email? "),A=d("Rinvia"),E={key:1},J=r(()=>s("h3",{class:"text-center mt-4 mb-5"},"Password dimenticata?",-1)),U=r(()=>s("h6",{class:"text-center mb-8"},"Inserisci la tua email. Ti invieremo le istruzioni per reimpostare la tua password.",-1)),j=["onSubmit"],D={class:"sm:col-span-4 my-4"},H=r(()=>s("label",{for:"email",class:"block text-sm font-medium text-gray-700"}," Email ",-1)),K={class:"mt-1"},M={key:0,class:"mt-2 text-sm text-red-600",id:"email-error"},Q={class:"flex justify-between items-center"},R=d("Torna al login"),$=d(" Invia "),G={__name:"ForgotPassword",props:{status:String},setup(i){const t=w({email:""}),h=()=>{t.post(route("password.email"))};return(c,m)=>{const b=p("bb-input"),f=p("bb-button");return l(),n(x,null,[o(e(y),{title:"Forgot Password"}),o(F,null,{default:a(()=>[o(C,{class:""},{logo:a(()=>[o(z,{class:"mb-2"})]),default:a(()=>[i.status?(l(),n("div",N,[P,q,s("h6",B,[T,o(e(_),{href:c.route("password.request")},{default:a(()=>[A]),_:1},8,["href"])])])):(l(),n("div",E,[J,U,s("form",{onSubmit:I(h,["prevent"])},[s("div",D,[H,s("div",K,[o(b,{modelValue:e(t).email,"onUpdate:modelValue":m[0]||(m[0]=g=>e(t).email=g),id:"email",name:"email",type:"email",autocomplete:"email",class:u("shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-md "+(e(t).errors.email?"border-red-600":"border-gray-300")),required:"",autofocus:""},null,8,["modelValue","class"]),e(t).errors.email?(l(),n("p",M,S(e(t).errors.email),1)):k("",!0)])]),s("div",Q,[o(e(_),{href:c.route("login")},{default:a(()=>[R]),_:1},8,["href"]),o(f,{class:u(["ml-4",{"opacity-25":e(t).processing}]),disabled:e(t).processing},{default:a(()=>[$]),_:1},8,["class","disabled"])])],40,j)]))]),_:1})]),_:1})],64)}}};var Z=v(G,[["__scopeId","data-v-97d95f20"]]);export{Z as default};
