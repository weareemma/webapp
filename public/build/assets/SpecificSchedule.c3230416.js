import{g as s,o as h,e as b,w as d,W as p,Y as m,a as r,b as y,K as v,U as o}from"./app.c248d67c.js";import{_ as x}from"./AppLayout.4269b78a.js";import g from"./ScheduleSpecificCalendar.9f16134c.js";import"./Logo.d971df1b.js";import"./wizardStore.2226bc2a.js";import"./menu.6da08311.js";import"./ChevronDownIcon.f3847a45.js";import"./CheckIcon.5f685430.js";import"./XIcon.0ce515ba.js";import"./main.fb97573b.js";const k={class:"pb-16"},w={class:"text-2xl font-extrabold text-bb-primary"},Y=r("div",{class:"text-bb-primary"}," Imposta il numero di persone per ogni slot ",-1),V={__name:"SpecificSchedule",setup(D){const u=s(()=>p().props.value.schedules),i=s(()=>p().props.value.store),l=s(()=>{var t;const e=[];return(t=i.value.opening_times)==null||t.forEach(a=>{const f=m.datetime.weekdayIndex(a.day);a.slots.forEach(c=>{e.push({daysOfWeek:[f],startTime:c.start_time,endTime:c.end_time})})}),e}),_=s(()=>u.value.map(e=>({id:e.id,title:e.workers,start:n(e.date,e.start),end:n(e.date,e.end),editable:!0})));function n(e,t){return`${m.dayjs(e).format("YYYY-MM-DD")}T${t}`}return(e,t)=>(h(),b(x,{title:"Specific schedule","show-title":!1},{default:d(()=>[r("div",k,[y(g,{events:o(_),businessHours:o(l)},{title:d(()=>[r("h2",w," Programmazione specifica "+v(o(i).name),1),Y]),_:1},8,["events","businessHours"])])]),_:1}))}};export{V as default};
