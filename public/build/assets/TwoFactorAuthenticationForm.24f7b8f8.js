import{r as _,ap as H,o as t,c as s,a as c,a5 as I,b as a,w as o,S as n,K as C,as as B,Q as b,D as N,a9 as Q,g as D,B as U,e as y,W as Y,U as h,L as u,q as W,J as z,X as A}from"./app.c248d67c.js";import{b as G,a as J}from"./DialogModal.87f8d7ac.js";import{_ as $}from"./Button.797ea591.js";import{_ as R}from"./Input.a945c9cc.js";import{_ as L}from"./InputError.16dd130e.js";import{_ as S}from"./SecondaryButton.7694cc8f.js";import{_ as O}from"./DangerButton.902d00db.js";import{_ as X}from"./Label.6d73caef.js";const j={class:"mt-4"},Z=n(" Cancel "),w={__name:"ConfirmsPassword",props:{title:{type:String,default:"Confirm Password"},content:{type:String,default:"For your security, please confirm your password to continue."},button:{type:String,default:"Confirm"}},emits:["confirmed"],setup(g,{emit:x}){const i=_(!1),e=H({password:"",error:"",processing:!1}),l=_(null),v=()=>{axios.get(route("password.confirmation")).then(r=>{r.data.confirmed?x("confirmed"):(i.value=!0,setTimeout(()=>l.value.focus(),250))})},p=()=>{e.processing=!0,axios.post(route("password.confirm"),{password:e.password}).then(()=>{e.processing=!1,d(),N().then(()=>x("confirmed"))}).catch(r=>{e.processing=!1,e.error=r.response.data.errors.password[0],l.value.focus()})},d=()=>{i.value=!1,e.password="",e.error=""};return(r,m)=>(t(),s("span",null,[c("span",{onClick:v},[I(r.$slots,"default")]),a(G,{show:i.value,onClose:d},{title:o(()=>[n(C(g.title),1)]),content:o(()=>[n(C(g.content)+" ",1),c("div",j,[a(R,{ref_key:"passwordInput",ref:l,modelValue:e.password,"onUpdate:modelValue":m[0]||(m[0]=T=>e.password=T),type:"password",class:"mt-1 block w-3/4",placeholder:"Password",onKeyup:B(p,["enter"])},null,8,["modelValue","onKeyup"]),a(L,{message:e.error,class:"mt-2"},null,8,["message"])])]),footer:o(()=>[a(S,{onClick:d},{default:o(()=>[Z]),_:1}),a($,{class:b(["ml-3",{"opacity-25":e.processing}]),disabled:e.processing,onClick:p},{default:o(()=>[n(C(g.button),1)]),_:1},8,["class","disabled"])]),_:1},8,["show"])]))}},ee=n(" Two Factor Authentication "),te=n(" Add additional security to your account using two factor authentication. "),oe={key:0,class:"text-lg font-medium text-gray-900"},se={key:1,class:"text-lg font-medium text-gray-900"},ae={key:2,class:"text-lg font-medium text-gray-900"},ne=c("div",{class:"mt-3 max-w-xl text-sm text-gray-600"},[c("p",null," When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application. ")],-1),re={key:3},ce={key:0},ie={class:"mt-4 max-w-xl text-sm text-gray-600"},le={key:0,class:"font-semibold"},ue={key:1},de=["innerHTML"],me={key:0,class:"mt-4 max-w-xl text-sm text-gray-600"},fe={class:"font-semibold"},_e=n(" Setup Key: "),pe=["innerHTML"],he={key:1,class:"mt-4"},ve={key:1},ye=c("div",{class:"mt-4 max-w-xl text-sm text-gray-600"},[c("p",{class:"font-semibold"}," Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost. ")],-1),we={class:"grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 rounded-lg"},ge={class:"mt-5"},be={key:0},xe=n(" Enable "),ke={key:1},Ce=n(" Confirm "),Se=n(" Regenerate Recovery Codes "),Te=n(" Show Recovery Codes "),Fe=n(" Cancel "),$e=n(" Disable "),Me={__name:"TwoFactorAuthenticationForm",props:{requiresConfirmation:Boolean},setup(g){const x=g,i=_(!1),e=_(!1),l=_(!1),v=_(null),p=_(null),d=_([]),r=Q({code:""}),m=D(()=>{var f;return!i.value&&((f=Y().props.value.user)==null?void 0:f.two_factor_enabled)});U(m,()=>{m.value||(r.reset(),r.clearErrors())});const T=()=>{i.value=!0,A.Inertia.post("/user/two-factor-authentication",{},{preserveScroll:!0,onSuccess:()=>Promise.all([q(),M(),F()]),onFinish:()=>{i.value=!1,e.value=x.requiresConfirmation}})},q=()=>axios.get("/user/two-factor-qr-code").then(f=>{v.value=f.data.svg}),M=()=>axios.get("/user/two-factor-secret-key").then(f=>{p.value=f.data.secretKey}),F=()=>axios.get("/user/two-factor-recovery-codes").then(f=>{d.value=f.data}),K=()=>{r.post("/user/confirmed-two-factor-authentication",{errorBag:"confirmTwoFactorAuthentication",preserveScroll:!0,preserveState:!0,onSuccess:()=>{e.value=!1,v.value=null,p.value=null}})},E=()=>{axios.post("/user/two-factor-recovery-codes").then(()=>F())},V=()=>{l.value=!0,A.Inertia.delete("/user/two-factor-authentication",{preserveScroll:!0,onSuccess:()=>{l.value=!1,e.value=!1}})};return(f,P)=>(t(),y(J,null,{title:o(()=>[ee]),description:o(()=>[te]),content:o(()=>[h(m)&&!e.value?(t(),s("h3",oe," You have enabled two factor authentication. ")):h(m)&&e.value?(t(),s("h3",se," Finish enabling two factor authentication. ")):(t(),s("h3",ae," You have not enabled two factor authentication. ")),ne,h(m)?(t(),s("div",re,[v.value?(t(),s("div",ce,[c("div",ie,[e.value?(t(),s("p",le," To finish enabling two factor authentication, scan the following QR code using your phone's authenticator application or enter the setup key and provide the generated OTP code. ")):(t(),s("p",ue," Two factor authentication is now enabled. Scan the following QR code using your phone's authenticator application or enter the setup key. "))]),c("div",{class:"mt-4",innerHTML:v.value},null,8,de),p.value?(t(),s("div",me,[c("p",fe,[_e,c("span",{innerHTML:p.value},null,8,pe)])])):u("",!0),e.value?(t(),s("div",he,[a(X,{for:"code",value:"Code"}),a(R,{id:"code",modelValue:h(r).code,"onUpdate:modelValue":P[0]||(P[0]=k=>h(r).code=k),type:"text",name:"code",class:"block mt-1 w-1/2",inputmode:"numeric",autofocus:"",autocomplete:"one-time-code",onKeyup:B(K,["enter"])},null,8,["modelValue","onKeyup"]),a(L,{message:h(r).errors.code,class:"mt-2"},null,8,["message"])])):u("",!0)])):u("",!0),d.value.length>0&&!e.value?(t(),s("div",ve,[ye,c("div",we,[(t(!0),s(W,null,z(d.value,k=>(t(),s("div",{key:k},C(k),1))),128))])])):u("",!0)])):u("",!0),c("div",ge,[h(m)?(t(),s("div",ke,[a(w,{onConfirmed:K},{default:o(()=>[e.value?(t(),y($,{key:0,type:"button",class:b(["mr-3",{"opacity-25":i.value}]),disabled:i.value},{default:o(()=>[Ce]),_:1},8,["class","disabled"])):u("",!0)]),_:1}),a(w,{onConfirmed:E},{default:o(()=>[d.value.length>0&&!e.value?(t(),y(S,{key:0,class:"mr-3"},{default:o(()=>[Se]),_:1})):u("",!0)]),_:1}),a(w,{onConfirmed:F},{default:o(()=>[d.value.length===0&&!e.value?(t(),y(S,{key:0,class:"mr-3"},{default:o(()=>[Te]),_:1})):u("",!0)]),_:1}),a(w,{onConfirmed:V},{default:o(()=>[e.value?(t(),y(S,{key:0,class:b({"opacity-25":l.value}),disabled:l.value},{default:o(()=>[Fe]),_:1},8,["class","disabled"])):u("",!0)]),_:1}),a(w,{onConfirmed:V},{default:o(()=>[e.value?u("",!0):(t(),y(O,{key:0,class:b({"opacity-25":l.value}),disabled:l.value},{default:o(()=>[$e]),_:1},8,["class","disabled"]))]),_:1})])):(t(),s("div",be,[a(w,{onConfirmed:T},{default:o(()=>[a($,{type:"button",class:b({"opacity-25":i.value}),disabled:i.value},{default:o(()=>[xe]),_:1},8,["class","disabled"])]),_:1})]))])]),_:1}))}};export{Me as default};
