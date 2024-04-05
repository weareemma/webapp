import{a9 as C,o as u,e as N,w as n,a,b as o,U as t,c as _,J as w,S as i,K as y,q as A,Y as g,I as m}from"./app.c248d67c.js";import{_ as B}from"./AppLayout.4269b78a.js";import"./Logo.d971df1b.js";import"./wizardStore.2226bc2a.js";import"./menu.6da08311.js";import"./ChevronDownIcon.f3847a45.js";import"./CheckIcon.5f685430.js";import"./XIcon.0ce515ba.js";const F={class:"flex justify-start items-center mt-4 mb-6"},P={class:"bb-card p-8"},T={class:"text-bb-blue-500 mb-4 big-header-title"},D={key:0},O={key:1},q={class:"grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4"},E=i("Nome"),$={class:"col-span-2"},J=i("SERVIZI"),K={class:"flex justify-start items-baseline"},L=i(" Cancella "),R=i("+ Aggiungi servizio"),Y=i("Scadenza"),Z=i("Prezzo IVA inclusa"),G=i("Durata di validit\xE0 dalla data di acquisto (giorni)*"),H=i("Store"),Q={class:"col-span-2"},W=i("Descrizione"),X={class:"sm:col-span-2 flex justify-start items-center"},ee=i("Pacchetto attivo"),oe={class:"flex justify-end items-center mt-4"},te={key:0},le={key:1},pe={__name:"Form",props:{model:Object,stores:Object,services:Object},setup(v){const e=C(v.model);function k(){e.id?e.put(route("package.update",e.id),{preserveScroll:!0,onSuccess:c=>{g.helpers.flash(c.props.flash)}}):e.post(route("package.store"),{preserveScroll:!0,onSuccess:c=>{g.helpers.flash(c.props.flash)}})}function x(c){let l={ids:null,units:null};e.services?e.services.push(l):e.services=[l]}function z(c,l){e.services.splice(c,1)}return(c,l)=>{const S=m("bb-back-link"),r=m("bb-label"),b=m("bb-input"),d=m("bb-input-validation"),h=m("Bblink"),V=m("bb-select"),U=m("datepicker"),I=m("bb-textarea"),M=m("bb-switch"),j=m("bb-button");return u(),N(B,{title:"Pacchetto","show-title":!1},{default:n(()=>[a("div",F,[o(S)]),a("div",P,[a("h1",T,[t(e).id?(u(),_("span",D,"Modifica pacchetto")):(u(),_("span",O,"Aggiungi nuovo pacchetto"))]),a("div",q,[a("div",null,[o(r,{class:"mb-1"},{default:n(()=>[E]),_:1}),o(b,{type:"text",placeholder:"Nome",modelValue:t(e).name,"onUpdate:modelValue":l[0]||(l[0]=s=>t(e).name=s)},null,8,["modelValue"]),o(d,{form:t(e),name:"name"},null,8,["form"])]),a("div",$,[o(r,{class:"mb-1 text-bb-gray-700"},{default:n(()=>[J]),_:1}),(u(!0),_(A,null,w(t(e).services,(s,p)=>(u(),_("div",{class:"my-5 grid grid-cols-1 sm:grid-cols-2 gap-x-5",key:p},[a("div",null,[a("div",K,[o(r,{class:"mb-1"},{default:n(()=>[i("Servizio "+y(p+1),1)]),_:2},1024),o(h,{link:"",onClick:f=>z(p),class:"cursor-pointer text-bb-danger text-sm mb-1 ml-2"},{default:n(()=>[L]),_:2},1032,["onClick"])]),o(V,{mode:"tags",placeholder:"Seleziona i servizi","close-on-select":!0,options:v.services,modelValue:s.ids,"onUpdate:modelValue":f=>s.ids=f},null,8,["options","modelValue","onUpdate:modelValue"]),o(d,{form:t(e),name:"services."+p+".ids"},null,8,["form","name"])]),a("div",null,[o(r,{class:"mb-1"},{default:n(()=>[i("Numero servizi compresi "+y(p+1),1)]),_:2},1024),o(b,{type:"number",placeholder:"Inserisci il numero di servizi",modelValue:s.units,"onUpdate:modelValue":f=>s.units=f},null,8,["modelValue","onUpdate:modelValue"]),o(d,{form:t(e),name:"services."+p+".units"},null,8,["form","name"])])]))),128)),o(h,{link:"",onClick:x,class:"cursor-pointer text-bb-blue-500"},{default:n(()=>[R]),_:1}),o(d,{form:t(e),name:"services"},null,8,["form"])]),a("div",null,[o(r,{class:"mb-1"},{default:n(()=>[Y]),_:1}),o(U,{modelValue:t(e).expired_at,"onUpdate:modelValue":l[1]||(l[1]=s=>t(e).expired_at=s),format:"dd/MM/yyyy",previewFormat:"dd/MM/yyyy",locale:"it-IT",modelType:"dd/MM/yyyy",enableTimePicker:!1,monthNameFormat:"long",autoApply:""},null,8,["modelValue"]),o(d,{form:t(e),name:"expired_at"},null,8,["form"])]),a("div",null,[o(r,{class:"mb-1"},{default:n(()=>[Z]),_:1}),o(b,{type:"number",placeholder:"Prezzo IVA inclusa",modelValue:t(e).price,"onUpdate:modelValue":l[2]||(l[2]=s=>t(e).price=s)},null,8,["modelValue"]),o(d,{form:t(e),name:"price"},null,8,["form"])]),a("div",null,[o(r,{class:"mb-1"},{default:n(()=>[G]),_:1}),o(b,{type:"number",placeholder:"Inserisci il numero di giorni",modelValue:t(e).valid_from,"onUpdate:modelValue":l[3]||(l[3]=s=>t(e).valid_from=s)},null,8,["modelValue"]),o(d,{form:t(e),name:"valid_from"},null,8,["form"])]),a("div",null,[o(r,{class:"mb-1"},{default:n(()=>[H]),_:1}),o(V,{mode:"tags",placeholder:"Seleziona gli store","close-on-select":!0,options:v.stores,modelValue:t(e).stores,"onUpdate:modelValue":l[4]||(l[4]=s=>t(e).stores=s)},null,8,["options","modelValue"]),o(d,{form:t(e),name:"stores"},null,8,["form"])]),a("div",Q,[o(r,{class:"mb-1"},{default:n(()=>[W]),_:1}),o(I,{class:"min-h-[180px]",type:"text",modelValue:t(e).description,"onUpdate:modelValue":l[5]||(l[5]=s=>t(e).description=s)},null,8,["modelValue"]),o(d,{form:t(e),name:"description"},null,8,["form"])]),a("div",X,[o(M,{modelValue:t(e).active,"onUpdate:modelValue":l[6]||(l[6]=s=>t(e).active=s)},null,8,["modelValue"]),o(r,{class:"mb-0 ml-2"},{default:n(()=>[ee]),_:1})]),o(d,{form:t(e),name:"active"},null,8,["form"])]),a("div",oe,[o(j,{type:"button",onClick:k,disabled:t(e).processing},{default:n(()=>[t(e).id?(u(),_("span",te,"Salva")):(u(),_("span",le,"Aggiungi"))]),_:1},8,["disabled"])])])]),_:1})}}};export{pe as default};
