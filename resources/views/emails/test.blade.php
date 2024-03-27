@component('mail::message')
@if (! empty($greeting))
# {{ $greeting }}
@endif

The body of your message.

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
