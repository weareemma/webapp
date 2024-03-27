import{r as p,a9 as v,o as a,e as k,w as o,c as n,q as x,J as b,a as s,K as c,S as r,L as B,b as l,U as i,as as S,Q as C}from"./app.45a2e0de.js";import{_ as L}from"./ActionMessage.a3d1100b.js";import{a as M,b as O}from"./DialogModal.eff80baa.js";import{_ as w}from"./Button.ef41f357.js";import{_ as V}from"./Input.0b9361ed.js";import{_ as $}from"./InputError.dc7c3f49.js";import{_ as F}from"./SecondaryButton.2c3abc10.js";const I=r(" Browser Sessions "),K=r(" Manage and log out your active sessions on other browsers and devices. "),N=s("div",{class:"max-w-xl text-sm text-gray-600"}," If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password. ",-1),U={key:0,class:"mt-5 space-y-6"},z={key:0,fill:"none","stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",viewBox:"0 0 24 24",stroke:"currentColor",class:"w-8 h-8 text-gray-500"},T=s("path",{d:"M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"},null,-1),j=[T],D={key:1,xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24","stroke-width":"2",stroke:"currentColor",fill:"none","stroke-linecap":"round","stroke-linejoin":"round",class:"w-8 h-8 text-gray-500"},E=s("path",{d:"M0 0h24v24H0z",stroke:"none"},null,-1),H=s("rect",{x:"7",y:"4",width:"10",height:"16",rx:"1"},null,-1),P=s("path",{d:"M11 5h2M12 17v.01"},null,-1),q=[E,H,P],A={class:"ml-3"},J={class:"text-sm text-gray-600"},Q={class:"text-xs text-gray-500"},G={key:0,class:"text-green-500 font-semibold"},R={key:1},W={class:"flex items-center mt-5"},X=r(" Log Out Other Browser Sessions "),Y=r(" Done. "),Z=r(" Log Out Other Browser Sessions "),ss=r(" Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices. "),es={class:"mt-4"},os=r(" Cancel "),ts=r(" Log Out Other Browser Sessions "),us={__name:"LogoutOtherBrowserSessionsForm",props:{sessions:Array},setup(h){const d=p(!1),_=p(null),t=v({password:""}),y=()=>{d.value=!0,setTimeout(()=>_.value.focus(),250)},m=()=>{t.delete(route("other-browser-sessions.destroy"),{preserveScroll:!0,onSuccess:()=>u(),onError:()=>_.value.focus(),onFinish:()=>t.reset()})},u=()=>{d.value=!1,t.reset()};return(rs,f)=>(a(),k(M,null,{title:o(()=>[I]),description:o(()=>[K]),content:o(()=>[N,h.sessions.length>0?(a(),n("div",U,[(a(!0),n(x,null,b(h.sessions,(e,g)=>(a(),n("div",{key:g,class:"flex items-center"},[s("div",null,[e.agent.is_desktop?(a(),n("svg",z,j)):(a(),n("svg",D,q))]),s("div",A,[s("div",J,c(e.agent.platform?e.agent.platform:"Unknown")+" - "+c(e.agent.browser?e.agent.browser:"Unknown"),1),s("div",null,[s("div",Q,[r(c(e.ip_address)+", ",1),e.is_current_device?(a(),n("span",G,"This device")):(a(),n("span",R,"Last active "+c(e.last_active),1))])])])]))),128))])):B("",!0),s("div",W,[l(w,{onClick:y},{default:o(()=>[X]),_:1}),l(L,{on:i(t).recentlySuccessful,class:"ml-3"},{default:o(()=>[Y]),_:1},8,["on"])]),l(O,{show:d.value,onClose:u},{title:o(()=>[Z]),content:o(()=>[ss,s("div",es,[l(V,{ref_key:"passwordInput",ref:_,modelValue:i(t).password,"onUpdate:modelValue":f[0]||(f[0]=e=>i(t).password=e),type:"password",class:"mt-1 block w-3/4",placeholder:"Password",onKeyup:S(m,["enter"])},null,8,["modelValue","onKeyup"]),l($,{message:i(t).errors.password,class:"mt-2"},null,8,["message"])])]),footer:o(()=>[l(F,{onClick:u},{default:o(()=>[os]),_:1}),l(w,{class:C(["ml-3",{"opacity-25":i(t).processing}]),disabled:i(t).processing,onClick:m},{default:o(()=>[ts]),_:1},8,["class","disabled"])]),_:1},8,["show"])]),_:1}))}};export{us as default};
