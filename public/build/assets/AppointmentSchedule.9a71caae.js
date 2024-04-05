import{g as A,r as _,o as a,e as u,w as c,W as I,a as l,K as b,U as t,c as p,b as i,ag as T,S as f,L as n,X as U,I as S}from"./app.c248d67c.js";import{_ as z,r as E}from"./AppLayout.4269b78a.js";import O from"./ScheduleAppointmentCalendar.8fe9957c.js";import P from"./ScheduleAppointmentList.aeeb2bfe.js";import{u as F}from"./search.181f2eee.js";import{B as K}from"./SearchInput.2608ef07.js";import{r as L,a as Q}from"./ViewListIcon.297f7ad2.js";import"./Logo.d971df1b.js";import"./wizardStore.2226bc2a.js";import"./menu.6da08311.js";import"./ChevronDownIcon.f3847a45.js";import"./CheckIcon.5f685430.js";import"./XIcon.0ce515ba.js";import"./ScheduleAppointmentEventModal.27448be1.js";import"./ScheduleAppointmentStatusBadge.3b648ebd.js";import"./BookingPaymentDialog.53cd0b2c.js";import"./main.fb97573b.js";import"./Table.94a0e2ae.js";import"./PencilIcon.50f1eb3f.js";import"./RefreshIcon.6547917e.js";import"./Popover.a6db3f26.js";const R={class:"pb-16"},W={class:"text-2xl font-extrabold text-bb-primary mb-3"},X={class:"flex items-center justify-between mb-4 flex-wrap gap-x-4 gap-y-2"},q={class:"flex-grow flex justify-start gap-x-2"},G={key:0,class:"text-bb-primary"},H={key:1},J={class:"w-fit justify-self-end flex justify-end gap-2"},M={key:0,class:"inline-flex items-center rounded-full bg-yellow-100 px-4 py-2.5 text-sm font-medium text-yellow-800"},Y={class:"grid grid-cols-2 col-span-2 sm:flex items-center gap-x-4 gap-y-2 flex-wrap w-full sm:w-auto"},Z=f(" \xA0 Elenco "),ee=f(" \xA0 Calendario "),te=f("Aggiungi"),oe={key:0},ae={key:1},Ce={__name:"AppointmentSchedule",props:{viewName:String,store:Object,bookings:Object,noStylistBookingsCount:Number},setup(r){var x;const V=r;A(()=>I().props.value.is_admin);const{searchQuery:v,isSearching:j,filters:s}=F("schedule.appointment.index",{viewName:(x=V.viewName)!=null?x:"calendar",from:route().params.from,to:route().params.to}),y=_(0);function B(){U.Inertia.visit(route("booking.admin-dashboard"))}const d=_(null),k=_(null);function $(m){d.value.goToDay(m),h(m)}function h(m){axios.post(route("schedule.nostylist.count"),{day:dayjs(m).hour(5).toDate()}).then(e=>{y.value=e.data.count})}return(m,e)=>{const D=S("datepicker"),g=S("bb-button");return a(),u(z,{title:"Appointments schedule","show-title":!1},{default:c(()=>{var w,N,C;return[l("div",R,[l("h2",W," Calendario "+b(r.store.name),1),l("div",X,[l("div",q,[t(s).viewName=="calendar"?(a(),p("div",G," Visualizza e gestisci gli appuntamenti ")):(a(),p("div",H,[i(K,{isSearching:t(j),modelValue:t(v),"onUpdate:modelValue":e[0]||(e[0]=o=>T(v)?v.value=o:null)},null,8,["isSearching","modelValue"])]))]),l("div",J,[y.value>0?(a(),p("span",M,[i(t(E),{class:"w-6 h-6 mr-1"}),f(" "+b(y.value),1)])):n("",!0),i(D,{class:"bb-datepicker-button",modelValue:k.value,"onUpdate:modelValue":[e[1]||(e[1]=o=>k.value=o),$],locale:"it-IT",enableTimePicker:!1,monthNameFormat:"long",autoApply:""},null,8,["modelValue"])]),l("div",Y,[t(s).viewName==="calendar"?(a(),u(g,{key:0,secondary:"",light:"",onClick:e[2]||(e[2]=o=>t(s).viewName="list")},{default:c(()=>[i(t(L),{class:"w-3 h-3"}),Z]),_:1})):n("",!0),t(s).viewName==="list"?(a(),u(g,{key:1,secondary:"",light:"",onClick:e[3]||(e[3]=o=>t(s).viewName="calendar"),class:"px-1 sm:px-4"},{default:c(()=>[i(t(Q),{class:"w-3 h-3"}),ee]),_:1})):n("",!0),i(g,{class:"justify-self-end",onClick:B},{default:c(()=>[te]),_:1}),t(s).viewName==="calendar"?(a(),u(g,{key:2,onClick:e[4]||(e[4]=()=>{d.value.slot5=!d.value.slot5})},{default:c(()=>{var o;return[f(b((o=d.value)!=null&&o.slot5?"Slot 30":"Slot 5"),1)]}),_:1})):n("",!0)])]),t(s).viewName=="calendar"?(a(),p("div",oe,[i(O,{ref_key:"calendar",ref:d,onChanged:e[5]||(e[5]=o=>h(o))},null,512)])):n("",!0),t(s).viewName=="list"?(a(),p("div",ae,[(w=r.bookings)!=null&&w.data?(a(),u(P,{key:0,collection:(N=r.bookings)==null?void 0:N.data,links:(C=r.bookings)==null?void 0:C.links},null,8,["collection","links"])):n("",!0)])):n("",!0)])]}),_:1})}}};export{Ce as default};
