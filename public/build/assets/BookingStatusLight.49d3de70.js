import{g as e,o as n,c as r,Q as u,U as b,Y as t}from"./app.c248d67c.js";const l={__name:"BookingStatusLight",props:{status:String},setup(o){const s=o,a=e(()=>({"inline-block w-3 h-3 rounded-full":!0,"bg-bb-gray-100":s.status==t.booking_status_todo,"bg-[#A2E8EF]":s.status==t.booking_status_progress,"bg-bb-green-500":s.status==t.booking_status_ended,"bg-bb-danger":s.status==t.booking_status_canceled,"bg-bb-primary-100":s.status==t.booking_status_not_shown}));return(g,c)=>(n(),r("div",{class:u(b(a))},null,2))}};export{l as default};