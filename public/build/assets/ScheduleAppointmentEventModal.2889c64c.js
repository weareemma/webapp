import{o as v,e as V,b as l,r as c,W as U,y as $,Y as m,a9 as A,g as L,c as x,a as e,S as u,K as o,U as a,w as d,L as p,q as T,X as C,I as g,a8 as h,a4 as q}from"./app.45a2e0de.js";import G from"./ScheduleAppointmentStatusBadge.d0c04c36.js";import K from"./BookingPaymentDialog.c21dfd47.js";function O(n,B){return v(),V("svg",{xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"2",stroke:"currentColor","aria-hidden":"true"},[l("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"})])}const R={key:0,class:"bb-card p-6 space-y-3 text-bb-gray-800"},W={class:"text-sm"},X={class:"flex items-center space-x-2"},J=["src"],Q={class:"font-bold text-lg underline"},Z={class:"flex"},ee=e("div",{class:"uppercase text-bb-gray-700 w-20 text-xs pt-[5px]"}," Creata ",-1),te={class:"flex-1"},se={class:"flex"},oe=e("div",{class:"uppercase text-bb-gray-700 w-20 text-xs pt-[5px]"}," Creata da ",-1),le={class:"flex-1"},ne={class:"flex"},ae=e("div",{class:"uppercase text-bb-gray-700 w-20 text-xs pt-[5px]"}," Servizi ",-1),ie={class:"flex-1"},de={key:0,class:"flex items-center"},ue=e("div",{class:"uppercase text-bb-gray-700 w-20 text-xs pt-[5px]"}," Stylist ",-1),re={class:"flex-1"},ce={class:"flex items-center"},ve=e("div",{class:"uppercase text-bb-gray-700 w-20 text-xs"},"Status",-1),me={class:"flex-1"},pe={class:"flex items-center"},_e=e("div",{class:"uppercase text-bb-gray-700 w-20 text-xs"},"Durata",-1),fe={class:"flex-1"},be=u(" Il cliente deve pagare in store "),xe=e("div",{class:"pt-4 mb-4 border-b border-bb-gray-300"},null,-1),ge={class:"flex items-center justify-end space-x-3"},he=u("Elimina"),ye=u("Modifica"),ke=u("Dettaglio"),we=u(" Elimina appuntamento "),De=e("p",null,"Una volta eliminato, non potrai pi\xF9 recuperare le informazioni.",-1),Ce=e("br",null,null,-1),Ve=e("p",null,"Scegli una modalit\xE0 di cancellazione",-1),Be=u(" Elimina "),Ye={__name:"ScheduleAppointmentEventModal",props:{event:Object},emits:["edited","deleted"],setup(n,{emit:B}){const S=n,M=c(U().props.value.role);function _(){return M.value===m.role_admin||M.value===m.role_manager}const t=c(null);$(()=>{m.lg(S.event.extendedProps),t.value=A(S.event.extendedProps.booking),_()&&j()});const y=L(()=>t.value.amount_to_pay>0?t.value.amount_to_pay:null),k=c(!1);function P(i,s){C.Inertia.visit(route(i,s))}const Y=c([]);function j(){axios.get(route("booking.stylists",t.value.id)).then(i=>{Y.value=i.data})}function E(i){C.Inertia.post(route("booking.update.stylist",t.value.id),{stylist_id:i},{onSuccess:s=>{m.flash(s.props.flash)}})}const w=c(null),D=c(!1),f=c("refund");function F(){D.value=!0,C.Inertia.delete(route("booking.destroy",t.value.id),{data:{method:f.value},onFinish:()=>{m.flash({type:"success",message:"Appuntamento cancellato"}),w.value.close(),B("deleted"),D.value=!1}})}return(i,s)=>{const H=g("bb-select"),b=g("BbButton"),I=g("bb-radio-group"),N=g("BbDialog");return v(),x(T,null,[t.value?(v(),x("div",R,[e("div",W,[u(o(a(h)(t.value.date).format("dddd DD MMMM YYYY"))+" /\xA0 ",1),e("strong",null,o(a(h)(n.event.start).format("HH:mm"))+" - "+o(a(h)(n.event.end).format("HH:mm")),1)]),e("div",X,[e("img",{src:t.value.customer.profile_photo_url,class:"block rounded-full w-8 h-8 object-cover object-center"},null,8,J),e("div",Q,[l(a(q),{href:i.route("customer.show",t.value.customer.id)},{default:d(()=>[u(o(t.value.customer.full_name),1)]),_:1},8,["href"])]),e("div",null,o(t.value.is_father?"":"Amica "+t.value.guest),1)]),e("div",Z,[ee,e("div",te,o(a(h)(n.event.extendedProps.booking.created_at).format("DD/MM/YYYY HH:mm")),1)]),e("div",se,[oe,e("div",le,o(n.event.extendedProps.booking.created_by),1)]),e("div",ne,[ae,e("div",ie,o(n.event.extendedProps.services),1)]),_()?(v(),x("div",de,[ue,e("div",re,[l(H,{key:"stylist_select",mode:"single",placeholder:"Seleziona lo stylist","close-on-select":!0,options:Y.value,modelValue:t.value.stylist_id,"onUpdate:modelValue":s[0]||(s[0]=r=>t.value.stylist_id=r),onChange:E},null,8,["options","modelValue"])])])):p("",!0),e("div",ce,[ve,e("div",me,[l(G,{status:n.event.extendedProps.booking.status},null,8,["status"])])]),e("div",pe,[_e,e("div",fe,o(n.event.extendedProps.booking.duration),1)]),a(y)?(v(),x("div",{key:1,class:"rounded-lg bg-[#FFDEAD] flex items-center px-3 py-2 space-x-4 cursor-pointer",onClick:s[1]||(s[1]=()=>k.value=!0)},[l(a(O),{class:"w-4 h-4 text-bb-danger"}),e("div",null,[be,e("strong",null,o(a(y))+"\u20AC",1)])])):p("",!0),xe,e("div",ge,[l(b,{danger:"",onClick:s[2]||(s[2]=()=>{f.value="refund",w.value.open()})},{default:d(()=>[he]),_:1}),_()?(v(),V(b,{key:0,light:"",onClick:s[3]||(s[3]=r=>{var z;return P("booking.edit",(z=t.value.parent_id)!=null?z:t.value.id)})},{default:d(()=>[ye]),_:1})):p("",!0),l(b,{autofocus:"",onClick:s[4]||(s[4]=r=>P(_()?"booking.show":"stylist.appointment.details",t.value.id))},{default:d(()=>[ke]),_:1})])])):p("",!0),t.value?(v(),V(K,{key:1,"booking-id":t.value.id,amount:a(y),modelValue:k.value,"onUpdate:modelValue":s[5]||(s[5]=r=>k.value=r),onFinish:s[6]||(s[6]=()=>{t.value.amount_to_pay=0,i.$emit("edited")})},null,8,["booking-id","amount","modelValue"])):p("",!0),l(N,{ref_key:"deleteDialog",ref:w,type:"plain",size:"sm"},{title:d(()=>[we]),buttons:d(()=>[l(b,{danger:"",disabled:D.value,onClick:F},{default:d(()=>[Be]),_:1},8,["disabled"])]),default:d(()=>[De,Ce,Ve,l(I,{class:"py-2",modelValue:f.value,"onUpdate:modelValue":s[7]||(s[7]=r=>f.value=r),vertical:!0,options:[{value:"refund",label:"Rimborso"},{value:"discount",label:"Genera sconto"},{value:"none",label:"Nessuna azione"}]},null,8,["modelValue"])]),_:1},512)],64)}}};export{Ye as default};
