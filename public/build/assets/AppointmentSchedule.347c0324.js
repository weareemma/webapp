import{r as g,o as a,e as d,w as u,a as l,K as B,U as e,c as r,b as i,ag as $,q as D,L as m,S as N,I as w}from"./app.c248d67c.js";import{_ as T}from"./StylistLayout.8d0f81c6.js";import j from"./ScheduleAppointmentCalendar.8fe9957c.js";import A from"./ScheduleAppointmentList.aeeb2bfe.js";import{u as I}from"./search.181f2eee.js";import{B as U}from"./SearchInput.2608ef07.js";import{r as z,a as E}from"./ViewListIcon.297f7ad2.js";import"./Logo.d971df1b.js";import"./wizardStore.2226bc2a.js";import"./XIcon.0ce515ba.js";import"./menu.6da08311.js";import"./disclosure.e9de6139.js";import"./ScheduleAppointmentEventModal.27448be1.js";import"./ScheduleAppointmentStatusBadge.3b648ebd.js";import"./BookingPaymentDialog.53cd0b2c.js";import"./main.fb97573b.js";import"./Table.94a0e2ae.js";import"./PencilIcon.50f1eb3f.js";import"./RefreshIcon.6547917e.js";import"./CheckIcon.5f685430.js";import"./Popover.a6db3f26.js";const F={class:"pb-16"},O={class:"text-2xl font-extrabold text-bb-primary mb-3"},q={class:"flex items-center justify-between mb-4"},K={key:0,class:"text-bb-primary"},L={key:1},P={class:"flex items-center space-x-4"},Q=N(" \xA0 Elenco "),R=N(" \xA0 Calendario "),G={key:0},H={key:1},be={__name:"AppointmentSchedule",props:{viewName:String,store:Object,bookings:Object},setup(n){var b;const x=n,{searchQuery:c,isSearching:V,filters:o}=I("stylist.appointment.index",{viewName:(b=x.viewName)!=null?b:"calendar"}),p=g(null),f=g(null);function S(_){p.value.goToDay(_)}return(_,t)=>{const C=w("datepicker"),v=w("bb-button");return a(),d(T,{title:"Appointments schedule","show-title":!1},{default:u(()=>{var k,h,y;return[l("div",F,[l("h2",O," Calendario "+B(n.store.name),1),l("div",q,[l("div",null,[e(o).viewName=="calendar"?(a(),r("div",K," Visualizza gli appuntamenti ")):(a(),r("div",L,[i(U,{isSearching:e(V),modelValue:e(c),"onUpdate:modelValue":t[0]||(t[0]=s=>$(c)?c.value=s:null)},null,8,["isSearching","modelValue"])]))]),l("div",P,[e(o).viewName=="calendar"?(a(),r(D,{key:0},[i(C,{class:"bb-datepicker-button",modelValue:f.value,"onUpdate:modelValue":[t[1]||(t[1]=s=>f.value=s),S],locale:"it-IT",enableTimePicker:!1,monthNameFormat:"long",autoApply:""},null,8,["modelValue"]),i(v,{secondary:"",light:"",onClick:t[2]||(t[2]=s=>e(o).viewName="list")},{default:u(()=>[i(e(z),{class:"w-3 h-3"}),Q]),_:1})],64)):m("",!0),e(o).viewName=="list"?(a(),d(v,{key:1,secondary:"",light:"",onClick:t[3]||(t[3]=s=>e(o).viewName="calendar")},{default:u(()=>[i(e(E),{class:"w-3 h-3"}),R]),_:1})):m("",!0)])]),e(o).viewName=="calendar"?(a(),r("div",G,[i(j,{ref_key:"calendar",ref:p},null,512)])):m("",!0),e(o).viewName=="list"?(a(),r("div",H,[(k=n.bookings)!=null&&k.data?(a(),d(A,{key:0,collection:(h=n.bookings)==null?void 0:h.data,links:(y=n.bookings)==null?void 0:y.links},null,8,["collection","links"])):m("",!0)])):m("",!0)])]}),_:1})}}};export{be as default};
