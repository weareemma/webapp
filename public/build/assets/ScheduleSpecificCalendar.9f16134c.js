import{r as s,y as L,ap as U,o as k,c as E,a as r,a5 as K,b as a,w as n,U as o,q as O,a8 as u,X as P,Y as Q,I as y,S as d,K as H,L as M}from"./app.c248d67c.js";import{m as R,a as q,b as G,F as W}from"./main.fb97573b.js";const X={class:"flex items-center justify-between mb-4 flex-wrap gap-2"},Z={class:"grid grid-cols-2 sm:flex items-center space-x-4 w-full sm:w-auto"},J=d("Salva"),ee={class:"bg-white rounded-xl overflow-hidden"},te={key:0,class:"text-bb-danger text-sm"},le=d("OK"),ne={key:0,class:"text-bb-danger text-sm"},ae=d("Elimina"),se=d("Salva"),re={__name:"ScheduleSpecificCalendar",props:{events:Array,businessHours:Array},setup(w){const x=s(null),b=s(null),c=s(!1);L(()=>{b.value=x.value.getApi()});const t=U({id:null,title:null,start:null,end:null,editable:!0});function z(e){t.id="new",t.title="",t.start=e.startStr,t.end=e.endStr,D(),_.value.open()}function Y(e){i=e.event,t.title=e.event.title,t.start=e.event.startStr,t.end=e.event.endStr,D(),f.value.open()}function A(e){g(e.event)}function N(e){g(e.event)}const m=s(!1);function C(){return t.title?(m.value=!1,!0):(m.value=!0,!1)}function D(){m.value=!1}const _=s(null),h=s([]);function T(){if(!C())return;const e=b.value.addEvent(t);h.value.push(e),_.value.close(),p()}const f=s(null),V=s([]),F=s([]);let i=null;function j(){!C()||(i&&(i.setProp("title",t.title),g(i),p()),f.value.close())}function g(e){e.id!="new"&&V.value.push(e.id)}function B(){i&&(F.value.push(i.id),i.remove(),p()),f.value.close()}function p(){const e=b.value.getEvents().filter(l=>l.id=="new"||V.value.includes(l.id)).map(l=>({id:l.id,workers:l.title,date:u(l.startStr).format("YYYY-MM-DD"),start:u(l.startStr).format("HH:mm"),end:u(l.endStr).format("HH:mm")}));P.Inertia.post(route("schedule.specific.save"),{events:e,deleted:F.value},{preserveScroll:!0,preserveState:!0,onFinish:()=>{h.value.forEach(l=>l.remove()),h.value=[],Q.flash({type:"success",message:"Programmazione salvata"})}})}return(e,l)=>{const v=y("bb-button"),I=y("bb-input"),$=y("bb-dialog");return k(),E(O,null,[r("div",null,[r("div",X,[r("div",null,[K(e.$slots,"title")]),r("div",Z,[a(v,{onClick:p},{default:n(()=>[J]),_:1}),a(v,{onClick:l[0]||(l[0]=()=>{c.value=!c.value})},{default:n(()=>[d(H(c.value?"Slot 30":"Slot 5"),1)]),_:1})])]),r("div",ee,[a(o(W),{ref_key:"calendar",ref:x,options:{plugins:[o(R),o(q),o(G)],locale:"it",initialView:"timeGridWeek",headerToolbar:{start:"prev,next,title",center:"",end:""},titleFormat:{},slotLabelInterval:c.value?"00:05:00":"00:30:00",slotLabelFormat:{hour:"numeric",minute:"2-digit",omitZeroMinute:!1,meridiem:"short"},allDaySlot:!1,selectable:!0,firstDay:1,slotDuration:c.value?"00:05:00":"00:30:00",slotMinTime:"07:00:00",slotMaxTime:"23:00:00",eventClick:Y,eventResize:A,eventDrop:N,select:z,businessHours:w.businessHours,events:w.events}},null,8,["options"])])]),a($,{ref_key:"createDialog",ref:_,size:"md"},{title:n(()=>[d(" Inserisci numero di persone per lo slot "+H(`${o(u)(t.start).format("HH:mm")} - ${o(u)(t.end).format("HH:mm")}`),1)]),buttons:n(()=>[a(v,{onClick:T},{default:n(()=>[le]),_:1})]),default:n(()=>[r("div",null,[a(I,{type:"number",modelValue:t.title,"onUpdate:modelValue":l[1]||(l[1]=S=>t.title=S),placeholder:"Inserisci il numero di persone"},null,8,["modelValue"]),m.value?(k(),E("div",te," Questo campo \xE8 obbligatorio ")):M("",!0)])]),_:1},512),a($,{ref_key:"editDialog",ref:f,size:"md"},{title:n(()=>[d(" Inserisci numero di persone per lo slot "+H(`${o(u)(t.start).format("HH:mm")} - ${o(u)(t.end).format("HH:mm")}`),1)]),buttons:n(()=>[a(v,{danger:"",onClick:B},{default:n(()=>[ae]),_:1}),a(v,{onClick:j},{default:n(()=>[se]),_:1})]),default:n(()=>[r("div",null,[a(I,{type:"number",modelValue:t.title,"onUpdate:modelValue":l[2]||(l[2]=S=>t.title=S),placeholder:"Inserisci il numero di persone"},null,8,["modelValue"]),m.value?(k(),E("div",ne," Questo campo \xE8 obbligatorio ")):M("",!0)])]),_:1},512)],64)}}};export{re as default};
