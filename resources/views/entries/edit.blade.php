@extends('layouts.app')

@section('content')
<div class="page-wrap narrow"><a href="{{ route('dashboard') }}" class="back-link">← Back to entries</a><div class="eyebrow">Edit entry</div><h1>Update approved address</h1><p class="muted">Keep this access record accurate and current.</p><form method="POST" action="{{ route('whitelist.update', $entry) }}" class="edit-panel">@csrf @method('PUT') @include('entries.form', ['entry' => $entry])<div class="modal-actions"><a href="{{ route('dashboard') }}" class="ghost-button">Cancel</a><button class="primary-button" type="submit">Save changes <span>→</span></button></div></form></div>
@endsection
