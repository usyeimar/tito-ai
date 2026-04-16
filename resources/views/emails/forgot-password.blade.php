@extends('emails.layouts.base')

@section('title')
Reset your password
@endsection

@section('preview')
Reset the password for your Tito account.
@endsection

@section('content')
<p class="greeting">Hello {{ $notifiable->first_name ?? ($notifiable->name ?? 'User') }},</p>

<p class="text">
    We received a request to reset the password for your Tito account.
    If you did not request this, you can safely ignore this email.
    <br><br>
    Otherwise, click the button below to choose a new password.
</p>
@endsection

@section('action')
<a href="{{ $url }}" class="button">Reset password</a>
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
