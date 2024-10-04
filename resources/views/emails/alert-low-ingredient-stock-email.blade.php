@component('mail::message')
# The {{$ingredient->title}} Stock is Low

The {{$ingredient->title}} stock is low. Please check the ingredient stock and refill it.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
