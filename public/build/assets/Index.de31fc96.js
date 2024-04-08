import{_ as y}from"./AppLayout.4269b78a.js";import{B as b}from"./Table.94a0e2ae.js";import{B as f}from"./SearchInput.2608ef07.js";import{u as g}from"./search.181f2eee.js";import{o as _,e as k,w as r,a as o,b as a,U as s,ag as x,Q as v,K as M,S as c,I as p}from"./app.c248d67c.js";import"./Logo.d971df1b.js";import"./wizardStore.2226bc2a.js";import"./menu.6da08311.js";import"./ChevronDownIcon.f3847a45.js";import"./CheckIcon.5f685430.js";import"./XIcon.0ce515ba.js";import"./PencilIcon.50f1eb3f.js";import"./RefreshIcon.6547917e.js";const V={class:"flex justify-between items-center mb-4"},T={class:"bb-card w-full flex flex-col gap-y-2"},h={class:"flex justify-start items-center gap-4 flex-wrap"},w=c("Da"),S=c("A"),B={class:"bb-card"},O={__name:"Index",props:{logs:Object},setup(i){const{searchQuery:n,isSearching:u,filters:l}=g("logs.index",{from:route().params.from,to:route().params.to});return(A,t)=>{const m=p("bb-label"),d=p("datepicker");return _(),k(y,{title:"Logs"},{default:r(()=>[o("div",V,[o("div",T,[a(f,{class:"w-1/2",searching:s(u),modelValue:s(n),"onUpdate:modelValue":t[0]||(t[0]=e=>x(n)?n.value=e:null)},null,8,["searching","modelValue"]),o("div",h,[o("div",null,[a(m,{class:"text-sm mb-1"},{default:r(()=>[w]),_:1}),a(d,{modelValue:s(l).from,"onUpdate:modelValue":t[1]||(t[1]=e=>s(l).from=e),format:"dd/MM/yyyy",previewFormat:"dd/MM/yyyy",locale:"it-IT",modelType:"dd/MM/yyyy",enableTimePicker:!1,monthNameFormat:"long",autoApply:""},null,8,["modelValue"])]),o("div",null,[a(m,{class:"text-sm mb-1"},{default:r(()=>[S]),_:1}),a(d,{modelValue:s(l).to,"onUpdate:modelValue":t[2]||(t[2]=e=>s(l).to=e),format:"dd/MM/yyyy",previewFormat:"dd/MM/yyyy",locale:"it-IT",modelType:"dd/MM/yyyy",enableTimePicker:!1,monthNameFormat:"long",autoApply:""},null,8,["modelValue"])])])])]),o("div",B,[a(b,{collection:i.logs.data,columns:[{key:"type",label:"Tipo",classes:"font-bold"},{key:"app",label:"App"},{slot:"status",label:"Stato"},{key:"session",label:"Sessione"},{key:"event",label:"Evento"},{key:"subject",label:"Soggetto"},{key:"created_at",label:"Logged",format:"datetime"}],links:i.logs.links,actions:[],onAction:t[3]||(t[3]=()=>{})},{status:r(({item:e})=>[o("span",{class:v({"bb-badge-success":e.status==="200","bb-badge-danger":e.status!=="200"})},M(e.status.toUpperCase()),3)]),_:1},8,["collection","links","actions"])])]),_:1})}}};export{O as default};
