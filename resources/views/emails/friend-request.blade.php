@component('mail::message')
# Pedido de Amizade

Olá, **{{ $addresseeName }}**!

**{{ $requesterName }}** quer se conectar com você no **BT Tournament**.

Aceite o pedido de amizade para ver o perfil, resultados e conquistas um do outro.

@component('mail::button', ['url' => $acceptUrl, 'color' => 'primary'])
Aceitar Pedido de Amizade
@endcomponent

Se você não reconhece **{{ $requesterName }}**, pode ignorar este e-mail.

Abraços,<br>
{{ config('app.name') }}
@endcomponent
