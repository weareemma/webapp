import{_ as c}from"./CustomerLayout.eb07e70f.js";import{B as r}from"./Table.94a0e2ae.js";import{o as a,e as m,w as d,a as i,c as l,b as _,X as f}from"./app.c248d67c.js";import"./Logo.d971df1b.js";import"./Footer.0f48e628.js";import"./XIcon.0ce515ba.js";import"./PencilIcon.50f1eb3f.js";import"./disclosure.e9de6139.js";import"./menu.6da08311.js";import"./RefreshIcon.6547917e.js";import"./CheckIcon.5f685430.js";const u={class:"mx-auto w-full"},b=i("h3",{class:"text-xl text-bb-blue-500 font-extrabold mb-5"},"Notifiche",-1),h={key:0},k={key:1},p=i("p",{class:"italic text-bb-blue-500"},"Non ci sono notifiche",-1),x=[p],j={__name:"Index",props:{models:Object},setup(o){function n(s,e){switch(s){case"markAsRead":f.Inertia.post(route("customer.notifications.markAsRead"),{notification_id:e.id},{onSuccess:t=>{helpers.flash(t)}});break}}return(s,e)=>(a(),m(c,{title:"Notifiche"},{default:d(()=>[i("div",u,[b,o.models.data.length>0?(a(),l("div",h,[_(r,{collection:o.models.data,columns:[{key:"created_at",label:"Data",format:"datetime"},{key:"data.title",label:"Messaggio"},{key:"read_at",label:"Letta il",format:"datetime"}],links:o.models.links,actions:[{name:"markAsRead",condition:t=>t.read_at===null}],onAction:e[0]||(e[0]=t=>n(t.action,t.item))},null,8,["collection","columns","links","actions"])])):(a(),l("div",k,x))])]),_:1}))}};export{j as default};