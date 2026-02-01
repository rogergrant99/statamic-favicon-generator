@extends('statamic::layout')
@section('title', __('statamic-favicon-generator::cp.general.headline'))

@section('content')
<favicon-generator
    title="@lang('statamic-favicon-generator::cp.general.headline')"
    :blueprint='@json($blueprint)'
    :meta='@json($meta)'
    :initial-values='@json($values)'
    generate="@lang('statamic-favicon-generator::cp.general.generate')"
></favicon-generator>

{{-- TEMPORARY DEBUG: Display initial-values directly --}}
<div style="background-color: yellow; padding: 10px; margin-top: 20px;">
    <h3>Debug: Initial Values (Raw)</h3>
    <pre>{{ @json($values) }}</pre>
    <h3>Debug: Initial Values (API Key)</h3>
    <p>{{ $values['api_key'] ?? 'N/A' }}</p>
    <h3>Debug: Initial Values (Icon)</h3>
    <p>{{ $values['icon'] ?? 'N/A' }}</p>
    <h3>Debug: Initial Values (HTML Tags)</h3>
    <pre>{{ e($values['html_tags'] ?? 'N/A') }}</pre>
    <h3>Debug: Initial Values (Generated At)</h3>
    <p>{{ $values['generated_at'] ?? 'N/A' }}</p>
</div>
@endsection

<style>
.animate-spin {
    animation-name: spin;
    animation-duration: 1s;
    animation-iteration-count: infinite;
}
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>