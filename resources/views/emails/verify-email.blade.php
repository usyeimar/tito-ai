@extends('emails.layouts.base')

@section('title')
Verify your email
@endsection

@section('preview')
Verify your email address to activate your account.
@endsection

@section('content')
<p class="greeting">Hello {{ $notifiable->first_name ?? ($notifiable->name ?? 'User') }},</p>

<p class="text">
    Thank you for signing up with Tito. To activate your account, please verify your email address
    by clicking the button below.
    <br>
    If you did not create this account, you can safely ignore this email.
</p>
@endsection

@section('action')
<a href="{{ $url }}" class="button">Verify email address</a>
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
