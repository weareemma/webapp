import{V as b,y as f,ar as h,o as c,c as w,a as d,b as x,U as t,e as k,w as v,ae as z,L as V,I as l,S,E as i}from"./app.45a2e0de.js";import{u as y}from"./wizardStore.ef397de9.js";const C=d("div",{class:"text-center mb-16"}," Seleziona il cliente per il quale desideri effettuare la prenotazione ",-1),g={class:"space-y-4 max-w-sm mx-auto"},B=S(" Avanti "),T={__name:"StepCustomer",setup(N){const o=y(),{wizardSelection:a,wizardGeneral:u,isValid:D,activeStep:F,people:U,wizardFetchData:q}=b(o);i("prev");const m=i("next");f(()=>{o.wizardFetchData("step_customer")}),h(()=>{o.wizardFetchData("step_customer")});const p=async(r="a")=>{let e=[];return await axios.post(route("booking.customer.select"),{q:r},{preserveScroll:!0}).then(s=>{e=s.data}),e};return(r,e)=>{const s=l("bb-select"),_=l("bb-button");return c(),w("div",null,[C,d("div",g,[x(s,{mode:"single","track-by":"name",searchable:!0,placeholder:"Seleziona un cliente","close-on-select":!0,"filter-results":!1,"min-chars":1,"resolve-on-load":!0,delay:0,options:async function(n){return await p(n)},disabled:!!t(u).original_booking,modelValue:t(a).customer_id,"onUpdate:modelValue":e[0]||(e[0]=n=>t(a).customer_id=n)},null,8,["options","disabled","modelValue"]),t(a).customer_id?(c(),k(_,{key:0,class:"w-full",outline:"",onClick:z(t(m),["stop"])},{default:v(()=>[B]),_:1},8,["onClick"])):V("",!0)])])}}};export{T as default};
