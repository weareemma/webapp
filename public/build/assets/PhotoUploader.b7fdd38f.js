import{o as a,e as m,b as u,r as c,c as i,a as t,ae as w,U as h,q as k,J as b,au as x,Y as f}from"./app.45a2e0de.js";function y(d,n){return a(),m("svg",{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor","aria-hidden":"true"},[u("path",{"fill-rule":"evenodd",d:"M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z","clip-rule":"evenodd"})])}const C=t("div",null,null,-1),B={class:"flex justify-start items-center gap-4"},P={class:"relative inline-block"},j={class:"flex justify-start items-center gap-3 flex-wrap"},N=["src"],z={class:"absolute bottom-0 right-0 block translate-y-1/2 translate-x-1/2 transform rounded-full border-0 border-white"},F={__name:"PhotoUploader",props:{photos:Object},emits:["changed"],setup(d,{emit:n}){const r=c(d.photos),o=c(null),p=c(-1);function v(e){r.value=r.value.filter(l=>l.id!==e),n("changed",r.value)}const _=()=>{o.value.click()},g=()=>{f.lg(o.value.files);for(const e of o.value.files)if(e.size>4e6){f.flash({type:"error",message:"Non puoi caricare un'immagine pi\xF9 grande di 4 MB"});return}for(const e of o.value.files){if(!e)return;const l=new FileReader;l.onload=s=>{r.value.push({id:p.value,url:s.target.result,file:e}),p.value--},l.readAsDataURL(e)}n("changed",r.value)};return(e,l)=>(a(),i("div",null,[C,t("div",B,[t("div",P,[u(h(y),{onClick:w(_,["prevent"]),class:"h-16 w-16 text-bb-gray-200 cursor-pointer"},null,8,["onClick"]),t("input",{ref_key:"photoInput",ref:o,type:"file",class:"hidden",accept:".jpg, .jpeg, .png",onChange:g,multiple:""},null,544)]),t("div",j,[(a(!0),i(k,null,b(r.value,s=>(a(),i("div",{key:s.id,class:"relative"},[t("img",{class:"h-16 w-16 rounded-full",src:s.url,alt:""},null,8,N),t("span",z,[u(h(x),{onClick:U=>v(s.id),class:"block h-5 w-5 rounded-full text-red-600 cursor-pointer"},null,8,["onClick"])])]))),128))])])]))}};export{F as _};
