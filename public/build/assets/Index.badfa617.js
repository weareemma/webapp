import{r as m,o as u,e as f,w as r,a as s,b as i,U as p,a4 as d,K as b,X as k,Y as _}from"./app.45a2e0de.js";import{_ as h}from"./AppLayout.f8630d45.js";import{B as v}from"./Table.e80badd5.js";import"./SearchInput.29274ae3.js";import"./Logo.1cde1930.js";import"./wizardStore.ef397de9.js";import"./menu.365b37e7.js";import"./ChevronDownIcon.ec8df155.js";import"./CheckIcon.b71d4ea3.js";import"./XIcon.52e671f8.js";import"./PencilIcon.9b9a523e.js";import"./RefreshIcon.76168562.js";const y=s("div",{class:"flex justify-between items-center mb-4"},null,-1),g={class:"bb-card"},V={__name:"Index",props:{errors:Object},setup(a){const l=m(!1);function n(o,e){switch(o){case"resolve":c(e.id);break}}function c(o){l.value=!0,k.Inertia.post(route("checkoutError.resolve",o),{onSuccess:e=>{l.value=!1,_.flash(e.props.flash)}})}return(o,e)=>(u(),f(h,{title:"Errori checkout"},{default:r(()=>[y,s("div",g,[i(v,{collection:a.errors.data,columns:[{slot:"user",label:"Utente"},{key:"store.name",label:"Store"},{key:"booking_for",label:"Prenotato per",format:"datetime"},{key:"paid_at",label:"Pagato il",format:"datetime"},{key:"total",label:"Totale",format:"currency"}],links:a.errors.links,actions:[{name:"resolve",condition:t=>!0}],onAction:e[0]||(e[0]=t=>n(t.action,t.item))},{user:r(({item:t})=>[i(p(d),{href:o.route("customer.show",t.user.id),class:"underline"},{default:r(()=>[s("strong",null,b(t.user.full_name),1)]),_:2},1032,["href"])]),_:1},8,["collection","columns","links","actions"])])]),_:1}))}};export{V as default};
