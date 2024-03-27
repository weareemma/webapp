import{y as H,g as Z,r as m,o,e as G,w as r,aq as J,a as t,b,K as n,c as a,L as _,J as w,Q as K,q as h,aa as x,ab as y,U as c,a8 as u,S as V,X as z,Y as W,I as p}from"./app.45a2e0de.js";import{_ as X}from"./AppLayout.f8630d45.js";import{B as E}from"./Table.e80badd5.js";import tt from"./ScheduleAppointmentStatusBadge.d0c04c36.js";import"./Logo.1cde1930.js";import"./wizardStore.ef397de9.js";import"./menu.365b37e7.js";import"./ChevronDownIcon.ec8df155.js";import"./CheckIcon.b71d4ea3.js";import"./XIcon.52e671f8.js";import"./PencilIcon.9b9a523e.js";import"./RefreshIcon.76168562.js";const et={class:"flex justify-start items-center mt-4 mb-6"},st={class:"bb-card p-8"},ot={class:"flex justify-between items-center"},nt={class:"flex justify-start items-center"},at=t("img",{src:""},null,-1),it={class:"text-xl font-extrabold text-bb-blue-500"},lt={class:"flex justify-start items-center"},ct=t("p",{class:"text-xs text-bb-blue-800 mr-2"},"TELEFONO:",-1),rt={class:"text-sm text-bb-gray-800 mr-2"},dt=t("p",{class:"text-xs text-bb-blue-800 mr-2"},"EMAIL:",-1),bt={class:"text-sm text-bb-gray-800 mr-2"},ut={key:0},mt=t("span",{class:"inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"},"Abbonamento attivo",-1),_t=[mt],ht=t("div",{class:"sm:hidden"},[t("label",{for:"tabs",class:"sr-only"})],-1),xt={class:"hidden sm:block"},yt={class:"border-b border-gray-200"},pt={class:"-mb-px flex space-x-8","aria-label":"Tabs"},ft=["onClick","aria-current"],gt={class:"py-6"},vt={class:"lg:w-1/2 w-full"},kt=t("h1",{class:"text-md text-bb-blue-500 font-bold mb-3"},"Informazioni",-1),wt={class:"flex justify-between items-center"},Yt=t("p",{class:"text-bb-gray-700 text-sm"},"INDIRIZZO",-1),Dt={class:"text-bb-gray-800 text-sm"},jt=t("h1",{class:"text-md text-bb-blue-500 font-bold mt-5 mb-3"},"Abbonamento",-1),At={key:0},It={class:"flex justify-between items-center my-1"},Mt=t("p",{class:"text-bb-gray-700 text-sm"},"NOME",-1),Nt={class:"text-bb-gray-800 text-sm"},Ot={class:"flex justify-between items-center my-1"},St=t("p",{class:"text-bb-gray-700 text-sm"},"ACQUISTATO IL",-1),Tt={class:"text-bb-gray-800 text-sm"},Ct={key:0,class:"flex justify-between items-center my-1"},Bt=t("p",{class:"text-bb-gray-700 text-sm"},"ATTIVO FINO AL",-1),zt={class:"text-bb-gray-800 text-sm"},Et={class:"flex justify-between items-center my-1"},Vt=t("p",{class:"text-bb-gray-700 text-sm"},"PROSSIMO RINNOVO",-1),Lt={class:"text-bb-gray-800 text-sm"},Pt={key:1},qt=t("p",{class:"text-bb-gray-800 italic"},"Nessun abbonamento acquistato",-1),Rt=[qt],Ut=t("h1",{class:"text-md text-bb-blue-500 font-bold mt-5 mb-3"},"Note cliente",-1),Ft={key:2},Qt={class:"text-sm"},$t={class:"text-xs text-gray-600"},Ht={key:3},Zt=t("p",{class:"text-bb-gray-800 italic"},"Nessuna nota",-1),Gt=[Zt],Jt=t("h1",{class:"text-md text-bb-blue-500 font-bold mt-5 mb-3"},"Note appuntamenti",-1),Kt={key:4},Wt={class:"text-xs text-bb-gray-600 mt-3"},Xt={key:0,class:"text-bb-gray-800 inline-block"},te={key:1,class:"text-bb-gray-800 inline-block italic text-xs"},ee={key:5},se=t("p",{class:"text-bb-gray-800 italic"},"Nessuna nota",-1),oe=[se],ne=t("h1",{class:"text-md text-bb-blue-500 font-bold mt-5 mb-3"},"Pacchetti",-1),ae={key:6},ie={class:"flex justify-between items-center my-1"},le=t("p",{class:"text-bb-gray-700 text-sm"},"NOME",-1),ce={class:"text-bb-gray-800 text-sm"},re={class:"flex justify-between items-center my-1"},de=t("p",{class:"text-bb-gray-700 text-sm"},"ACQUISTATO IL",-1),be={class:"text-bb-gray-800 text-sm"},ue={class:"flex justify-between items-center my-1"},me=t("p",{class:"text-bb-gray-700 text-sm"},"SCADE IL",-1),_e={class:"text-bb-gray-800 text-sm"},he={key:7},xe=t("p",{class:"text-bb-gray-800 italic"},"Nessun pacchetto acquistato",-1),ye=[xe],pe={class:"py-6"},fe={class:"py-6"},ge=V(" Elimina appuntamento "),ve=t("p",null,"Una volta eliminato, non potrai pi\xF9 recuperare le informazioni.",-1),ke=t("br",null,null,-1),we=t("p",null,"Scegli una modalit\xE0 di cancellazione",-1),Ye=V(" Elimina "),De={class:"py-6"},je={key:0,class:"bb-badge-warning inline-block"},Le={__name:"Show",props:{customer:Object,subscription:Object,bookings:Object,payments:Object,packages:Object,plan:Object},setup(s){const Y=s;H(()=>{console.log(Y.plan)});const D=Z(()=>{var l,i;return(i=J.filter((l=Y.customer)==null?void 0:l.bookings,k=>k.stylist_notes))!=null?i:[]}),f=m(null),j=m(null),g=m(!1),v=m("refund");function L(l,i){switch(l){case"view":z.Inertia.visit(route("booking.show",i.id));break;case"delete":j.value=i,f.value.open();break}}function P(l,i){switch(l){case"download":window.open(route("payment.invoice",[i.user_id,i.id]),"_blank");break}}function q(){g.value=!0,z.Inertia.delete(route("booking.destroy",j.value.id),{data:{method:v.value},onFinish:()=>{W.flash({type:"success",message:"Appuntamento cancellato"}),f.value.close(),emit("deleted"),g.value=!1}})}const R=[{name:"Info"},{name:"Appuntamenti"},{name:"Transazioni"}],d=m("Info");function U(l){d.value=l}return(l,i)=>{const k=p("bb-back-link"),F=p("bb-radio-group"),Q=p("BbButton"),$=p("BbDialog");return o(),G(X,{title:"Cliente","show-title":!1},{default:r(()=>{var A,I,M,N,O,S,T,C;return[t("div",et,[b(k)]),t("div",st,[t("div",ot,[t("div",nt,[at,t("div",null,[t("h1",it,n((A=s.customer.full_name)!=null?A:"-"),1),t("div",lt,[ct,t("p",rt,n((I=s.customer.phone)!=null?I:"-"),1),dt,t("p",bt,n((M=s.customer.email)!=null?M:"-"),1)])])]),s.subscription?(o(),a("p",ut,_t)):_("",!0)]),t("div",null,[ht,t("div",xt,[t("div",yt,[t("nav",pt,[(o(),a(h,null,w(R,e=>t("p",{onClick:B=>U(e.name),key:e.name,class:K([d.value===e.name?"border-bb-blue-500 text-bb-blue-500":"border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300","whitespace-nowrap pt-4 pb-2 px-1 border-b-2 font-medium text-sm font-bold cursor-pointer"]),"aria-current":d.value===e.name?"page":void 0},n(e.name),11,ft)),64))])])])]),x(t("div",gt,[t("div",vt,[kt,t("div",wt,[Yt,t("p",Dt,n((N=s.customer.address)!=null?N:"-"),1)]),jt,s.subscription?(o(),a("div",At,[t("div",It,[Mt,t("p",Nt,n((S=(O=s.plan)==null?void 0:O.name)!=null?S:"--"),1)]),t("div",Ot,[St,t("p",Tt,n(c(u).unix(s.subscription.created).format("DD/MM/YYYY")),1)]),s.subscription.ended_at?(o(),a("div",Ct,[Bt,t("p",zt,n(c(u).unix(s.subscription.ended_at).format("DD/MM/YYYY")),1)])):_("",!0),t("div",Et,[Vt,t("p",Lt,n(c(u).unix(s.subscription.current_period_end).format("DD/MM/YYYY")),1)])])):(o(),a("div",Pt,Rt)),Ut,s.customer.last_notes?(o(),a("div",Ft,[t("div",null,[t("p",Qt,n(s.customer.last_notes),1),t("p",$t," Scritte da "+n((T=s.customer.last_notes_by)==null?void 0:T.name)+" "+n((C=s.customer.last_notes_by)==null?void 0:C.surname)+" il "+n(c(u)(s.customer.last_notes_updated_at).format("DD/MM/YYYY")),1)])])):(o(),a("div",Ht,Gt)),Jt,c(D).length>0?(o(),a("div",Kt,[(o(!0),a(h,null,w(c(D),e=>(o(),a("div",null,[e.stylist_notes?(o(),a(h,{key:0},[t("p",Wt,"Appuntamento del "+n(c(u)(e.start_date).format("DD/MM/YYYY HH:mm")),1),e.stylist_notes?(o(),a("p",Xt,n(e.stylist_notes),1)):(o(),a("p",te,"Nessuna nota"))],64)):_("",!0)]))),256))])):(o(),a("div",ee,oe)),ne,s.packages.length>0?(o(),a("div",ae,[(o(!0),a(h,null,w(s.packages,e=>(o(),a("div",{class:"mb-5",key:e.id},[t("div",ie,[le,t("p",ce,n(e.name),1)]),t("div",re,[de,t("p",be,n(c(u)(e.created_at).format("DD/MM/YYYY")),1)]),t("div",ue,[me,t("p",_e,n(e.expired_at),1)])]))),128))])):(o(),a("div",he,ye))])],512),[[y,d.value==="Info"]]),x(t("div",pe,null,512),[[y,d.value==="Scheda"]]),x(t("div",fe,[b(E,{collection:s.bookings.data,columns:[{key:"date_formatted",label:"Data"},{key:"hour_formatted",label:"Ora"},{key:"store.name",label:"Store"},{key:"stylist.full_name",label:"Stylist"},{label:"Servizi",computed:e=>e.slots?e.slots.map(B=>B.service.title).join(" - "):"-",classes:"max-w-[240px]"},{slot:"created",label:"Prenotato il"},{slot:"status",label:"Stato"}],links:s.bookings.links,actions:[{name:"view",condition:e=>!0},{name:"delete",condition:e=>!e.is_past&&e.status!=="cancelled"}],onAction:i[0]||(i[0]=e=>L(e.action,e.item))},{created:r(({item:e})=>[t("p",null,n(c(u)(e.order?e.order.created_at:e.created_at).format("D/MM/YYYY")),1)]),status:r(({item:e})=>[b(tt,{status:e.status},null,8,["status"])]),_:1},8,["collection","columns","links","actions"]),b($,{ref_key:"deleteDialog",ref:f,type:"plain",size:"sm"},{title:r(()=>[ge]),buttons:r(()=>[b(Q,{danger:"",disabled:g.value,onClick:q},{default:r(()=>[Ye]),_:1},8,["disabled"])]),default:r(()=>[ve,ke,we,b(F,{class:"py-2",modelValue:v.value,"onUpdate:modelValue":i[1]||(i[1]=e=>v.value=e),vertical:!0,options:[{value:"refund",label:"Rimborso"},{value:"discount",label:"Genera sconto"},{value:"none",label:"Nessuna azione"}]},null,8,["modelValue"])]),_:1},512)],512),[[y,d.value==="Appuntamenti"]]),x(t("div",De,[b(E,{collection:s.payments.data,columns:[{key:"customer_name",label:"Cliente",classes:"font-bold"},{computed:e=>{switch(e.subject){case"booking-create":return"Nuovo appuntamento";case"booking-edit":return"Modifica appuntamento";case"package":return"Acquisto pacchetto";case"subscription-create":return"Acquisto abbonamento";case"subscription-cycle":return"Rinnovo Abbonamento"}},label:"Oggetto"},{key:"date",label:"Data"},{key:"total",label:"Totale",format:"currency"},{computed:e=>{switch(e.method){case"stripe":return"Stripe";case"cash":return"Pagamento in contanti";case"satispay":return"Pagamento con Satispay";case"card":return"Pagamento in carta"}},label:"Metodo di pagamento"},{slot:"refunded",label:""}],links:s.payments.links,actions:[{name:"download",condition:e=>e.stripe_payment_id!==null}],onAction:i[2]||(i[2]=e=>P(e.action,e.item))},{refunded:r(({item:e})=>[e.refunded?(o(),a("div",je,"Rimborsato")):_("",!0)]),_:1},8,["collection","columns","links","actions"])],512),[[y,d.value==="Transazioni"]])])]}),_:1})}}};export{Le as default};
