import{a9 as B,r as v,o as w,e as I,w as i,a as e,b as t,aa as y,ab as g,ak as U,ae as k,U as s,c as D,au as F,L as z,S as _,Y as b,X as E,I as m}from"./app.c248d67c.js";import{_ as L}from"./StylistLayout.8d0f81c6.js";import{r as M}from"./PencilIcon.50f1eb3f.js";import"./Logo.d971df1b.js";import"./wizardStore.2226bc2a.js";import"./XIcon.0ce515ba.js";import"./menu.6da08311.js";import"./disclosure.e9de6139.js";const R={class:"mx-auto w-full md:w-1/2"},$=e("h3",{class:"text-xl text-bb-blue-500 font-extrabold mb-5 text-center"},"Dati account",-1),A={class:"bb-card py-8 px-10 shadow-xl"},O={class:"my-4"},T=_("Foto profilo"),X={class:"col-span-6 sm:col-span-4"},Y={class:"flex justify-start items-center gap-2"},q={class:"mt-2"},G=["src","alt"],H={class:"mt-2"},J=["onClick"],K=["onClick"],Q={class:"my-4"},W=_("Email"),Z={class:"my-4"},ee=_("Password"),oe={class:"my-4"},te=_("Conferma password"),se={class:"flex justify-end items-center mt-4"},le=e("span",null,"Salva",-1),me={__name:"Profile",props:{user:Object},setup(d){const x=d,V={password:null,password_confirmation:null,photo:null},o=B({...x.user,...V}),c=v(null),n=v(null);function C(){n.value&&(o.photo=n.value.files[0]),o.post(route("stylist.profile.update"),{preserveScroll:!0,onSuccess:l=>{b.flash(l.props.flash),h()}})}const P=()=>{n.value.click()},S=()=>{const l=n.value.files[0];if(b.lg(l.size),l.size>2e6){b.flash({type:"success",message:"Non puoi caricare un'immagine pi\xF9 grande di 2 MB"});return}if(!l)return;const a=new FileReader;a.onload=r=>{c.value=r.target.result},a.readAsDataURL(l)},j=()=>{E.Inertia.delete(route("current-user-photo.destroy"),{preserveScroll:!0,onSuccess:()=>{c.value=null,h()}})},h=()=>{var l;(l=n.value)!=null&&l.value&&(n.value.value=null),o.reset("photo")};return(l,a)=>{const r=m("bb-label"),p=m("bb-input-validation"),f=m("bb-input"),N=m("bb-button");return w(),I(L,{title:"Dati account"},{default:i(()=>[e("div",R,[$,e("div",A,[e("div",O,[t(r,{class:"mb-1"},{default:i(()=>[T]),_:1}),e("div",X,[e("input",{ref_key:"photoInput",ref:n,type:"file",class:"hidden",accept:".jpg, .jpeg, .png",onChange:S},null,544),e("div",Y,[y(e("div",q,[e("img",{src:d.user.profile_photo_url,alt:d.user.name,class:"rounded-full h-20 w-20 object-cover"},null,8,G)],512),[[g,!c.value]]),y(e("div",H,[e("span",{class:"block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center",style:U("background-image: url('"+c.value+"');")},null,4)],512),[[g,c.value]]),e("button",{type:"button",class:"mt-2 icon-button",onClick:k(P,["prevent"])},[t(s(M))],8,J),d.user.profile_photo_path||s(o).photo?(w(),D("button",{key:0,type:"button",class:"mt-2 icon-button",onClick:k(j,["prevent"])},[t(s(F))],8,K)):z("",!0)]),t(p,{form:s(o),name:"photo"},null,8,["form"])])]),e("div",Q,[t(r,{class:"mb-1"},{default:i(()=>[W]),_:1}),t(f,{type:"email",placeholder:"Email",modelValue:s(o).email,"onUpdate:modelValue":a[0]||(a[0]=u=>s(o).email=u)},null,8,["modelValue"]),t(p,{form:s(o),name:"email"},null,8,["form"])]),e("div",Z,[t(r,{class:"mb-1"},{default:i(()=>[ee]),_:1}),t(f,{type:"password",placeholder:"Password",modelValue:s(o).password,"onUpdate:modelValue":a[1]||(a[1]=u=>s(o).password=u)},null,8,["modelValue"]),t(p,{form:s(o),name:"password"},null,8,["form"])]),e("div",oe,[t(r,{class:"mb-1"},{default:i(()=>[te]),_:1}),t(f,{type:"password",placeholder:"Conferma Password",modelValue:s(o).password_confirmation,"onUpdate:modelValue":a[2]||(a[2]=u=>s(o).password_confirmation=u)},null,8,["modelValue"]),t(p,{form:s(o),name:"password_confirmation"},null,8,["form"])]),e("div",se,[t(N,{type:"button",onClick:C,disabled:s(o).processing},{default:i(()=>[le]),_:1},8,["disabled"])])])])]),_:1})}}};export{me as default};