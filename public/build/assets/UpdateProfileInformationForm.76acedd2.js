import{a9 as B,r as f,o as d,e as w,w as a,c as S,a as l,b as o,aa as p,ab as _,ak as j,ae as h,L as v,U as t,a4 as E,Q as F,S as n,X as L}from"./app.c248d67c.js";import{_ as A}from"./Button.797ea591.js";import{_ as R}from"./FormSection.5e59807a.js";import{_ as V}from"./Input.a945c9cc.js";import{_ as g}from"./InputError.16dd130e.js";import{_ as k}from"./Label.6d73caef.js";import{_ as z}from"./ActionMessage.3ce81dd1.js";import{_ as C}from"./SecondaryButton.7694cc8f.js";const D=n(" Profile Information "),T=n(" Update your account's profile information and email address. "),M={key:0,class:"col-span-6 sm:col-span-4"},O={class:"mt-2"},Q=["src","alt"],X={class:"mt-2"},Y=n(" Select A New Photo "),q=n(" Remove Photo "),G={class:"col-span-6 sm:col-span-4"},H={class:"col-span-6 sm:col-span-4"},J={key:0},K={class:"text-sm mt-2"},W=n(" Your email address is unverified. "),Z=n(" Click here to re-send the verification email. "),ee={class:"mt-2 font-medium text-sm text-green-600"},oe=n(" Saved. "),te=n(" Save "),ue={__name:"UpdateProfileInformationForm",props:{user:Object},setup(c){const b=c,e=B({_method:"PUT",name:b.user.name,email:b.user.email,photo:null}),y=f(null),m=f(null),r=f(null),$=()=>{r.value&&(e.photo=r.value.files[0]),e.post(route("user-profile-information.update"),{errorBag:"updateProfileInformation",preserveScroll:!0,onSuccess:()=>P()})},I=()=>{y.value=!0},x=()=>{r.value.click()},N=()=>{const s=r.value.files[0];if(!s)return;const i=new FileReader;i.onload=u=>{m.value=u.target.result},i.readAsDataURL(s)},U=()=>{L.Inertia.delete(route("current-user-photo.destroy"),{preserveScroll:!0,onSuccess:()=>{m.value=null,P()}})},P=()=>{var s;(s=r.value)!=null&&s.value&&(r.value.value=null)};return(s,i)=>(d(),w(R,{onSubmitted:$},{title:a(()=>[D]),description:a(()=>[T]),form:a(()=>[s.$page.props.jetstream.managesProfilePhotos?(d(),S("div",M,[l("input",{ref_key:"photoInput",ref:r,type:"file",class:"hidden",onChange:N},null,544),o(k,{for:"photo",value:"Photo"}),p(l("div",O,[l("img",{src:c.user.profile_photo_url,alt:c.user.name,class:"rounded-full h-20 w-20 object-cover"},null,8,Q)],512),[[_,!m.value]]),p(l("div",X,[l("span",{class:"block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center",style:j("background-image: url('"+m.value+"');")},null,4)],512),[[_,m.value]]),o(C,{class:"mt-2 mr-2",type:"button",onClick:h(x,["prevent"])},{default:a(()=>[Y]),_:1},8,["onClick"]),c.user.profile_photo_path?(d(),w(C,{key:0,type:"button",class:"mt-2",onClick:h(U,["prevent"])},{default:a(()=>[q]),_:1},8,["onClick"])):v("",!0),o(g,{message:t(e).errors.photo,class:"mt-2"},null,8,["message"])])):v("",!0),l("div",G,[o(k,{for:"name",value:"Name"}),o(V,{id:"name",modelValue:t(e).name,"onUpdate:modelValue":i[0]||(i[0]=u=>t(e).name=u),type:"text",class:"mt-1 block w-full",autocomplete:"name"},null,8,["modelValue"]),o(g,{message:t(e).errors.name,class:"mt-2"},null,8,["message"])]),l("div",H,[o(k,{for:"email",value:"Email"}),o(V,{id:"email",modelValue:t(e).email,"onUpdate:modelValue":i[1]||(i[1]=u=>t(e).email=u),type:"email",class:"mt-1 block w-full"},null,8,["modelValue"]),o(g,{message:t(e).errors.email,class:"mt-2"},null,8,["message"]),s.$page.props.jetstream.hasEmailVerification&&c.user.email_verified_at===null?(d(),S("div",J,[l("p",K,[W,o(t(E),{href:s.route("verification.send"),method:"post",as:"button",class:"underline text-gray-600 hover:text-gray-900",onClick:h(I,["prevent"])},{default:a(()=>[Z]),_:1},8,["href","onClick"])]),p(l("div",ee," A new verification link has been sent to your email address. ",512),[[_,y.value]])])):v("",!0)])]),actions:a(()=>[o(z,{on:t(e).recentlySuccessful,class:"mr-3"},{default:a(()=>[oe]),_:1},8,["on"]),o(A,{class:F({"opacity-25":t(e).processing}),disabled:t(e).processing},{default:a(()=>[te]),_:1},8,["class","disabled"])]),_:1}))}};export{ue as default};