import{g as r,aa as u,af as d,U as l,o as i,c as p,ag as f}from"./app.c248d67c.js";const g=["value"],k={__name:"Checkbox",props:{checked:{type:[Array,Boolean],default:!1},value:{type:String,default:null}},emits:["update:checked"],setup(o,{emit:c}){const s=o,e=r({get(){return s.checked},set(t){c("update:checked",t)}});return(t,a)=>u((i(),p("input",{"onUpdate:modelValue":a[0]||(a[0]=n=>f(e)?e.value=n:null),type:"checkbox",value:o.value,class:"rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"},null,8,g)),[[d,l(e)]])}};export{k as _};