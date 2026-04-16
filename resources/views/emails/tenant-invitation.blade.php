@extends('emails.layouts.base')

@section('title')
Invitation to join {{ $tenantName }}
@endsection

@section('preview')
You have been invited to join the {{ $tenantName }} workspace.
@endsection

@section('content')
<p class="greeting">Hello,</p>

<p class="text">
    {{ $inviterName }} has invited you to join the {{ $tenantName }} workspace in Tito.
    This workspace is where your team collaborates on projects, shares updates, and manages work in one place.
</p>

@if($expiresAt)
<p class="text">
    This invitation expires on {{ $expiresAt->format('jS F Y') }}. Please accept it before then to gain access.
</p>
@endif
@endsection

@section('action')
<a href="{{ $acceptUrl }}" class="button">Accept invitation</a>
@endsection

@section('fallback')
If the button doesn't work, copy and paste this link into your browser:
<a href="{{ $acceptUrl }}" class="fallback-link" target="_blank">
    {{ $acceptUrl }}
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
