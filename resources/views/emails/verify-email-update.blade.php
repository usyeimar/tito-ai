@extends('emails.layouts.base')

@section('title')
Confirm your updated email
@endsection

@section('preview')
Confirm your updated email address to keep your account secure.
@endsection

@section('content')
<p class="greeting">Hello {{ $notifiable->first_name ?? ($notifiable->name ?? 'User') }},</p>

<p class="text">
    We received a request to update the email address for your Tito account.
    Please confirm this change by clicking the button below.
    <br>
    If you did not request this change, you can safely ignore this email.
</p>
@endsection

@section('action')
<a href="{{ $url }}" class="button">Confirm updated email</a>
@endsection

@section('expiry')
This link will expire in <strong>{{ $expire }} minutes</strong> for security.
@endsection

@section('fallback')
If the button doesn't work, paste this URL into your browser:
<a href="{{ $url }}" class="fallback-link" target="_blank">
    {{ $url }}
</a>
@endsection

@section('signature')
Regards,<br>
The <strong>Tito</strong> Team.
@endsection

@section('footer')
This email was sent by Tito.<br>
Tito Inc, Florida, USA.
@endsection
