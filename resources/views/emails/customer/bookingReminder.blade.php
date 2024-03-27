@component('mail::message')
<img style="width: auto; margin-bottom: 20px;" src="{{asset('/img/abbonati.jpeg')}}">

{{-- Intro Lines --}}
@foreach ($introLines as $line)
<p>{{ $line }}</p>
@endforeach

<p style="text-align: center; margin-top: 20px;">
Ti ricordiamo che qualora volessi disdire o modificare la prenotazionepotrai farlo fino a 24 ore prima*, sempre attraverso la tua pagina personale all'interno della nostra Web App.
</p>

@component('mail::button', ['url' => "https://app.weareemma.com/login", 'color' => 'primary'])
    Vai all'appuntamento
@endcomponent

<table style="width: 100%; margin-bottom: 40px">
<tr>
<td style="width: 50%; padding: 10px;">
<img style="width: auto;" src="{{asset('/img/gin.png')}}">
</td>
<td style="width: 50%;  padding: 40px 10px 10px 10px;">
<p><strong>Vuoi rendere la tua visita ancora più piacevole?</strong></p>
<p>Consulta il menu del nostro Bar per scegliere un drink da sorseggiare mentre ci prediamo cura di te!</p>
@component('mail::button', ['url' => "https://weareemma-mercato.ipratico.com/menu/#/order", 'color' => 'primary'])
Vai al menu
@endcomponent
</td>
</tr>
</table>

<p style="font-size: 10px;">
    *E' possibile modificare o annullare la prenotazione entro e non oltre 24 ore prima dell'appuntamento.
</p>
@endcomponent
