@extends('emails.layouts.base')

@section('title')
{{ $entityLabel }} import {{ $status === 'failed' ? 'failed' : 'completed' }}
@endsection

@section('preview')
Your {{ strtolower($entityLabel) }} import {{ $status === 'failed' ? 'failed' : 'is complete' }}. Review totals and download the report.
@endsection

@section('content')
<p class="greeting">Hello,</p>

<p class="text">
    Your {{ strtolower($entityLabel) }} import request for <strong>{{ $sourceFilename }}</strong> has finished.
    Mode: <span class="badge {{ $mode === 'dry_run' ? 'badge-success' : 'badge-success' }}">{{ $mode === 'dry_run' ? 'Dry run' : 'Execute' }}</span>.
</p>

<p class="text" style="margin-bottom: 12px;"><strong style="color: var(--tito-foreground);">Totals</strong></p>
<table class="data-table">
    <tr><td>Rows seen</td><td>{{ $summary['total'] ?? 0 }}</td></tr>
    <tr><td>Processed</td><td>{{ $summary['processed'] ?? 0 }}</td></tr>
    <tr><td>Created</td><td>{{ $summary['created'] ?? 0 }}</td></tr>
    <tr><td>Updated</td><td>{{ $summary['updated'] ?? 0 }}</td></tr>
    <tr><td>Skipped</td><td>{{ $summary['skipped'] ?? 0 }}</td></tr>
    <tr><td>Failed</td><td style="color: {{ ($summary['failed'] ?? 0) > 0 ? '#dc2626' : 'var(--tito-foreground)' }} !important;">{{ $summary['failed'] ?? 0 }}</td></tr>
    <tr><td>Warnings</td><td style="color: {{ ($summary['warnings'] ?? 0) > 0 ? '#ca8a04' : 'var(--tito-foreground)' }} !important;">{{ $summary['warnings'] ?? 0 }}</td></tr>
</table>

@if(!empty($errorMessage))
<p class="text" style="margin-bottom: 24px; padding: 16px; background-color: #fee2e2; border-radius: 8px; color: #dc2626;">
    <strong>Error:</strong> {{ $errorMessage }}
</p>
@endif

@if($reportUrl)
<p class="text" style="margin-bottom: 8px;">
    Full report:
    <a href="{{ $reportUrl }}" target="_blank" style="color: var(--tito-primary); text-decoration: none; font-weight: 500;">Download JSON report</a>
</p>
@endif

@if($failedRowsUrl)
<p class="text" style="margin-bottom: 24px;">
    Failed rows CSV:
    <a href="{{ $failedRowsUrl }}" target="_blank" style="color: var(--tito-primary); text-decoration: none; font-weight: 500;">Download failed rows</a>
</p>
@endif
@endsection

@section('action')
@if($reportUrl)
<a href="{{ $reportUrl }}" class="button" target="_blank">Open import report</a>
@endif
@endsection

@section('fallback')
@if($reportUrl)
If the button doesn't work, copy and paste this report link into your browser:
<a href="{{ $reportUrl }}" class="fallback-link" target="_blank">
    {{ $reportUrl }}
</a>
@endif
@endsection

@section('signature')
Regards,<br>
The <strong style="color: var(--tito-foreground);">Tito</strong> Team.
@endsection

@section('footer')
This email was sent by Tito.<br>
Tito Inc, Florida, USA.
@endsection
