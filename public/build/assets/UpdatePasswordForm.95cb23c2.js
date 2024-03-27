import{r as i,a9 as b,o as v,e as g,w as a,a as d,b as r,U as o,Q as V,S as n,I as _}from"./app.45a2e0de.js";import{_ as P}from"./ActionMessage.a3d1100b.js";import{_ as k}from"./FormSection.1eca2177.js";import{_ as u}from"./InputError.dc7c3f49.js";import{_ as p}from"./Label.704e6f42.js";const y=n(" Aggiorna la tua password "),S=n(" La password dovrebbe essere lunga almeno 8 caratteri. Per una maggiore sucurezza utilizza lettere maiuscole e minuscole, numeri e simboli. "),z={class:"col-span-6"},I={class:"col-span-6"},U={class:"col-span-6"},x=n(" Salvato "),B=n(" Salva "),L={__name:"UpdatePasswordForm",setup(C){const c=i(null),m=i(null),s=b({current_password:"",password:"",password_confirmation:""}),w=()=>{s.put(route("user-password.update"),{errorBag:"updatePassword",preserveScroll:!0,onSuccess:()=>s.reset(),onError:()=>{s.errors.password&&(s.reset("password","password_confirmation"),c.value.focus()),s.errors.current_password&&(s.reset("current_password"),m.value.focus())}})};return(N,e)=>{const l=_("bb-input"),f=_("bb-button");return v(),g(k,{onSubmitted:w},{title:a(()=>[y]),description:a(()=>[S]),form:a(()=>[d("div",z,[r(p,{for:"current_password",value:"Password corrente"}),r(l,{id:"current_password",ref_key:"currentPasswordInput",ref:m,modelValue:o(s).current_password,"onUpdate:modelValue":e[0]||(e[0]=t=>o(s).current_password=t),type:"password",class:"mt-1 block w-full",autocomplete:"current-password"},null,8,["modelValue"]),r(u,{message:o(s).errors.current_password,class:"mt-2"},null,8,["message"])]),d("div",I,[r(p,{for:"password",value:"Nuova Password"}),r(l,{id:"password",ref_key:"passwordInput",ref:c,modelValue:o(s).password,"onUpdate:modelValue":e[1]||(e[1]=t=>o(s).password=t),type:"password",class:"mt-1 block w-full",autocomplete:"new-password"},null,8,["modelValue"]),r(u,{message:o(s).errors.password,class:"mt-2"},null,8,["message"])]),d("div",U,[r(p,{for:"password_confirmation",value:"Conferma nuova password"}),r(l,{id:"password_confirmation",modelValue:o(s).password_confirmation,"onUpdate:modelValue":e[2]||(e[2]=t=>o(s).password_confirmation=t),type:"password",class:"mt-1 block w-full",autocomplete:"new-password"},null,8,["modelValue"]),r(u,{message:o(s).errors.password_confirmation,class:"mt-2"},null,8,["message"])])]),actions:a(()=>[r(P,{on:o(s).recentlySuccessful,class:"mr-3"},{default:a(()=>[x]),_:1},8,["on"]),r(f,{class:V({"opacity-25":o(s).processing}),disabled:o(s).processing},{default:a(()=>[B]),_:1},8,["class","disabled"])]),_:1})}}};export{L as default};
