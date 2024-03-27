import{a9 as _,g as h,o as r,c as l,b as e,U as t,w as s,q as p,$ as y,L as g,a as o,Q as v,a4 as c,ae as x,S as a}from"./app.45a2e0de.js";import{J as b}from"./AuthenticationCard.cc7b2565.js";import{_ as k}from"./AuthenticationCardLogo.89d638a3.js";import{_ as w}from"./Button.ef41f357.js";const V=o("div",{class:"mb-4 text-sm text-gray-600"}," Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another. ",-1),E={key:0,class:"mb-4 font-medium text-sm text-green-600"},S=["onSubmit"],B={class:"mt-4 flex items-center justify-between"},C=a(" Resend Verification Email "),L=a(" Edit Profile"),N=a(" Log Out "),q={__name:"VerifyEmail",props:{status:String},setup(d){const m=d,i=_(),u=()=>{i.post(route("verification.send"))},f=h(()=>m.status==="verification-link-sent");return(n,$)=>(r(),l(p,null,[e(t(y),{title:"Email Verification"}),e(b,null,{logo:s(()=>[e(k)]),default:s(()=>[V,t(f)?(r(),l("div",E," A new verification link has been sent to the email address you provided in your profile settings. ")):g("",!0),o("form",{onSubmit:x(u,["prevent"])},[o("div",B,[e(w,{class:v({"opacity-25":t(i).processing}),disabled:t(i).processing},{default:s(()=>[C]),_:1},8,["class","disabled"]),o("div",null,[e(t(c),{href:n.route("profile.show"),class:"underline text-sm text-gray-600 hover:text-gray-900"},{default:s(()=>[L]),_:1},8,["href"]),e(t(c),{href:n.route("logout"),method:"post",as:"button",class:"underline text-sm text-gray-600 hover:text-gray-900 ml-2"},{default:s(()=>[N]),_:1},8,["href"])])])],40,S)]),_:1})],64))}};export{q as default};
