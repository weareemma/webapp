import{y as A,ar as F,a8 as i,r as h,W as P,o as n,e as D,w as p,a as t,K as o,U as s,b as l,S as U,Z as I,Q as x,c as d,J as w,q as k,L as y,aa as C,ab as M,I as L}from"./app.c248d67c.js";import{_ as Q}from"./StylistLayout.8d0f81c6.js";import{u as q}from"./search.181f2eee.js";import B from"./BookingStatusLight.49d3de70.js";import{r as E}from"./ChevronDownIcon.f3847a45.js";import{r as S}from"./ArrowCircleRightIcon.e9d6004c.js";import{m as J,p as K,v as T,f as W}from"./menu.6da08311.js";import"./Logo.d971df1b.js";import"./wizardStore.2226bc2a.js";import"./XIcon.0ce515ba.js";import"./disclosure.e9de6139.js";const Z={class:"text-xl font-extrabold text-bb-blue-500 hidden sm:block"},G=t("h4",{class:"text-bb-gray-800 hidden sm:block"},"Visualizza e gestisci gli appuntamenti che ti hanno assegnato",-1),H={class:"mx-auto max-w-7xl sm:px-6 lg:px-8 sm:mt-10"},R={class:"mx-auto max-w-3xl"},X={class:"bb-card bg-transparent px-0"},tt={class:"sm:flex justify-between items-center"},et={class:"flex justify-between items-center sm:gap-3 gap-1 mb-3 sm:mb-0"},st={class:"text-xl text-bb-gray-800 font-extrabold"},at={class:"flex justify-start items-center gap-1"},ot=t("span",{class:"inline-flex items-center rounded-full bg-white px-2.5 py-0.5 text-xs font-medium text-bb-gray-500"},"Oggi",-1),rt=[ot],nt={class:"flex justify-start items-center gap-2"},lt=t("p",{class:"text-sm text-bb-gray-600"},"Filtra per store:",-1),it={class:"py-1"},ct=["onClick"],dt=t("div",{class:""},[t("label",{for:"tabs",class:"sr-only"})],-1),mt={class:"block"},ut={class:"border-b border-gray-200"},pt={class:"-mb-px flex space-x-8 cursor-pointer","aria-label":"Tabs"},bt=["aria-current"],_t=["aria-current"],ft={class:"py-6"},ht={key:0,class:"text-sm text-bb-blue-500 text-center"},xt={class:"bb-card bg-bb-lilla mb-3"},yt={class:"flex justify-start items-center gap-4"},gt={class:"text-white font-bold"},vt={class:"text-white font-bold text-sm"},wt={class:"text-white text-sm"},kt={class:"flex justify-between items-center mt-6"},Yt={class:"text-sm text-[#2B3B77]"},jt=["href"],Dt={class:"py-6"},Ct={key:0,class:"text-sm text-bb-blue-500 text-center"},Mt={class:"bb-card bg-bb-lilla mb-3"},Bt={class:"flex justify-start items-center gap-4"},St={class:"text-white font-bold"},Tt={class:"text-white font-bold text-sm"},Vt={class:"text-white text-sm"},Nt={class:"flex justify-between items-center mt-6"},Ot={class:"text-sm text-[#2B3B77]"},$t=["href"],Gt={__name:"Dashboard",props:{stores:Object,bookings_next:Object,bookings_past:Object},setup(_){var j;const g=_;A(()=>{helpers.lg(r.day),console.log(route().params.day)}),F(()=>{helpers.lg(r.day),helpers.lg(g.bookings_next),helpers.lg(g.bookings_past)});const{searchQuery:zt,isSearching:At,filters:r}=q("stylist.dashboard",{store:null,day:(j=route().params.day)!=null?j:i().format("YYYY-MM-DD")}),V=h(P().props.value.user),N=h(g.stores);function Y(c){r.store=c?c.name:null}const a=[{name:"Prossimi app."},{name:"App. conclusi"}],u=h(a[0].name);function f(c){u.value=c}const v=h(r.day);function O(c){r.day=i(c).format("YYYY-MM-DD"),i(r.day).isSameOrAfter(i(),"day")?f(a[0].name):f(a[1].name)}function $(){r.day=i().format("YYYY-MM-DD"),v.value=r.day,f(a[0].name)}return(c,m)=>{const z=L("datepicker");return n(),D(Q,{title:"Dashboard"},{default:p(()=>[t("h1",Z,"Ciao "+o(V.value.full_name)+"!",1),G,t("div",H,[t("div",R,[t("div",X,[t("div",tt,[t("div",et,[t("div",null,[t("p",st,o(s(i)(s(r).day).format("DD MMMM YYYY")),1)]),t("div",at,[t("p",{onClick:$,class:"cursor-pointer"},rt),l(z,{class:"bb-datepicker-button-sm",modelValue:v.value,"onUpdate:modelValue":[m[0]||(m[0]=e=>v.value=e),O],locale:"it-IT",enableTimePicker:!1,monthNameFormat:"long",autoApply:""},null,8,["modelValue"])])]),t("div",nt,[lt,l(s(W),{as:"div",class:"relative inline-block text-left"},{default:p(()=>[t("div",null,[l(s(J),{class:"inline-flex w-full justify-center px-4 py-2 text-sm font-medium text-gray-700"},{default:p(()=>[U(o(s(r).store?s(r).store:"Tutti")+" ",1),l(s(E),{class:"-mr-1 ml-2 h-5 w-5","aria-hidden":"true"})]),_:1})]),l(I,{"enter-active-class":"transition ease-out duration-100","enter-from-class":"transform opacity-0 scale-95","enter-to-class":"transform opacity-100 scale-100","leave-active-class":"transition ease-in duration-75","leave-from-class":"transform opacity-100 scale-100","leave-to-class":"transform opacity-0 scale-95"},{default:p(()=>[l(s(K),{class:"absolute -right-12 sm:right-0 z-10 mt-2 w-56 sm:origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"},{default:p(()=>[t("div",it,[l(s(T),null,{default:p(({active:e})=>[t("p",{onClick:m[1]||(m[1]=b=>Y(null)),class:x([e?"bg-gray-100 text-gray-900":"text-gray-700","block px-4 py-2 text-sm cursor-pointer"])},"Tutti",2)]),_:1}),(n(!0),d(k,null,w(N.value,e=>(n(),D(s(T),null,{default:p(({active:b})=>[t("p",{onClick:Ft=>Y(e),class:x([b?"bg-gray-100 text-gray-900":"text-gray-700","block px-4 py-2 text-sm cursor-pointer"])},o(e.name),11,ct)]),_:2},1024))),256))])]),_:1})]),_:1})]),_:1})])]),t("div",null,[dt,t("div",mt,[t("div",ut,[t("nav",pt,[s(i)(s(r).day).isSameOrAfter(s(i)(),"day")?(n(),d("p",{onClick:m[2]||(m[2]=e=>f(a[0].name)),key:a[0].name,class:x([u.value===a[0].name?"border-bb-blue-500 text-bb-blue-500":"border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300","whitespace-nowrap pt-4 pb-2 px-1 border-b-4 font-medium text-sm"]),"aria-current":u.value===a[0].name?"page":void 0},o(a[0].name),11,bt)):y("",!0),s(i)(s(r).day).isSameOrBefore(s(i)(),"day")?(n(),d("p",{onClick:m[3]||(m[3]=e=>f(a[1].name)),key:a[1].name,class:x([u.value===a[1].name?"border-bb-blue-500 text-bb-blue-500":"border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300","whitespace-nowrap pt-4 pb-2 px-1 border-b-4 font-medium text-sm"]),"aria-current":u.value===a[1].name?"page":void 0},o(a[1].name),11,_t)):y("",!0)])])])]),C(t("div",ft,[_.bookings_next.length===0?(n(),d("p",ht,"Nessun appuntamento")):y("",!0),(n(!0),d(k,null,w(_.bookings_next,e=>(n(),d("div",xt,[t("div",yt,[t("p",gt,o(e.hour_formatted),1),l(B,{status:e.status},null,8,["status"])]),t("p",vt,o(e.customer.full_name),1),t("p",wt,o(e.slots.map(b=>b.service.title).join(" + ")),1),t("div",kt,[t("p",Yt,o(e.store.name),1),t("a",{href:c.route("stylist.booking.details",e.id)},[l(s(S),{class:"h-6 w-6 text-white"})],8,jt)])]))),256))],512),[[M,u.value===a[0].name]]),C(t("div",Dt,[_.bookings_past.length===0?(n(),d("p",Ct,"Nessun appuntamento")):y("",!0),(n(!0),d(k,null,w(_.bookings_past,e=>(n(),d("div",Mt,[t("div",Bt,[t("p",St,o(e.hour_formatted),1),l(B,{status:e.status},null,8,["status"])]),t("p",Tt,o(e.customer.full_name),1),t("p",Vt,o(e.slots.map(b=>b.service.title).join(" + ")),1),t("div",Nt,[t("p",Ot,o(e.store.name),1),t("a",{href:c.route("stylist.booking.details",e.id)},[l(s(S),{class:"h-6 w-6 text-white"})],8,$t)])]))),256))],512),[[M,u.value===a[1].name]])])])])]),_:1})}}};export{Gt as default};
