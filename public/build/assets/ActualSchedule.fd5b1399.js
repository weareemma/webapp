import{_ as x}from"./AppLayout.f8630d45.js";import{g as n,y as g,r as i,o as v,e as b,w as c,W as p,a as e,b as m,K as d,U as t,Y as y,S as w,I as D}from"./app.45a2e0de.js";import k from"./ScheduleActualCalendar.265eeec8.js";import"./Logo.1cde1930.js";import"./wizardStore.ef397de9.js";import"./menu.365b37e7.js";import"./ChevronDownIcon.ec8df155.js";import"./CheckIcon.b71d4ea3.js";import"./XIcon.52e671f8.js";import"./main.f7cfe0fe.js";import"./ScheduleActualEventModal.98ae99b8.js";import"./ScheduleAppointmentStatusBadge.d0c04c36.js";const V={class:"pb-16"},T={class:"flex-grow col-span-2 w-full"},Y={class:"text-2xl font-extrabold text-bb-primary"},M=e("div",{class:"text-bb-primary"}," Dati importati da Tanda ",-1),N={class:"flex justify-between items-center gap-2 col-span-2 sm:col-span-1 flex-grow"},U={class:"text-sm text-white"},B=w("Ultimo aggiornamento: "),E={__name:"ActualSchedule",setup(P){const u=n(()=>p().props.value.current_store),a=n(()=>p().props.value.last_update.shift);g(()=>{});const o=i(null),s=i(null);function _(l){o.value.goToDay(l)}return(l,r)=>{const f=D("datepicker");return v(),b(x,{title:"Default schedule","show-title":!1},{default:c(()=>[e("div",V,[m(k,{ref_key:"calendar",ref:o},{title:c(()=>[e("div",T,[e("h2",Y," Programmazione effettiva "+d(t(u).name),1),M]),e("div",N,[e("div",null,[e("p",U,[B,e("strong",null,d(t(a)?t(y).dayjs(t(a)).format("H:m:s DD/MM/YYYY"):"Mai"),1)])]),e("div",null,[m(f,{class:"bb-datepicker-button",modelValue:s.value,"onUpdate:modelValue":[r[0]||(r[0]=h=>s.value=h),_],locale:"it-IT",enableTimePicker:!1,monthNameFormat:"long",autoApply:""},null,8,["modelValue"])])])]),_:1},512)])]),_:1})}}};export{E as default};
