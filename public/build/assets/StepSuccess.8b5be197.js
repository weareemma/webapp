import{o as a,c as o,L as c,b as n,w as s,a as d,I as p,S as i}from"./app.c248d67c.js";const l={class:"text-center space-y-6"},_={key:0},u=i(" Acquista altri servizi "),m=d("div",null,"oppure",-1),h=i(" Torna agli appuntamenti "),B={__name:"StepSuccess",props:{paymentLinkSent:{type:Boolean,default:!1}},setup(r){return(e,f)=>{const t=p("BbLink");return a(),o("div",l,[r.paymentLinkSent?(a(),o("p",_," Link di pagamento inviato al cliente ")):c("",!0),n(t,{href:e.route("booking.admin-dashboard")},{default:s(()=>[u]),_:1},8,["href"]),m,n(t,{href:e.route("schedule.appointment.index")},{default:s(()=>[h]),_:1},8,["href"])])}}};export{B as default};
