import{_ as c}from"./CustomerLayout.da8c9787.js";import{B as r}from"./Table.e80badd5.js";import{o as a,e as m,w as d,a as i,c as l,b as _,X as f}from"./app.45a2e0de.js";import"./Logo.1cde1930.js";import"./Footer.edc70d2c.js";import"./XIcon.52e671f8.js";import"./PencilIcon.9b9a523e.js";import"./disclosure.b237ce99.js";import"./menu.365b37e7.js";import"./RefreshIcon.76168562.js";import"./CheckIcon.b71d4ea3.js";const u={class:"mx-auto w-full"},b=i("h3",{class:"text-xl text-bb-blue-500 font-extrabold mb-5"},"Notifiche",-1),h={key:0},k={key:1},p=i("p",{class:"italic text-bb-blue-500"},"Non ci sono notifiche",-1),x=[p],j={__name:"Index",props:{models:Object},setup(o){function n(s,e){switch(s){case"markAsRead":f.Inertia.post(route("customer.notifications.markAsRead"),{notification_id:e.id},{onSuccess:t=>{helpers.flash(t)}});break}}return(s,e)=>(a(),m(c,{title:"Notifiche"},{default:d(()=>[i("div",u,[b,o.models.data.length>0?(a(),l("div",h,[_(r,{collection:o.models.data,columns:[{key:"created_at",label:"Data",format:"datetime"},{key:"data.title",label:"Messaggio"},{key:"read_at",label:"Letta il",format:"datetime"}],links:o.models.links,actions:[{name:"markAsRead",condition:t=>t.read_at===null}],onAction:e[0]||(e[0]=t=>n(t.action,t.item))},null,8,["collection","columns","links","actions"])])):(a(),l("div",k,x))])]),_:1}))}};export{j as default};
