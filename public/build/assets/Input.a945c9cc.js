import{r as s,y as r,o as l,c as i}from"./app.c248d67c.js";const c=["value"],f={__name:"Input",props:{modelValue:String},emits:["update:modelValue"],setup(u,{expose:t}){const e=s(null);return r(()=>{e.value.hasAttribute("autofocus")&&e.value.focus()}),t({focus:()=>e.value.focus()}),(a,o)=>(l(),i("input",{ref_key:"input",ref:e,class:"border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm",value:u.modelValue,onInput:o[0]||(o[0]=n=>a.$emit("update:modelValue",n.target.value))},null,40,c))}};export{f as _};
