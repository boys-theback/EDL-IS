@extends('layouts.app')

@section('content')
<div class="page-wrap"><header class="topbar"><div><div class="eyebrow">Administration / Accountability</div><h1>Audit log</h1><p class="muted">A complete record of whitelist, user, and destination changes.</p></div></header><section class="workspace-panel"><div class="table-wrap"><table><thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Details</th><th>Request IP</th></tr></thead><tbody>@forelse ($logs as $log)<tr><td class="muted">{{ $log->created_at->format('Y-m-d H:i') }}</td><td><strong>{{ $log->user?->name ?: 'Deleted user' }}</strong></td><td><span class="tag tag-general">{{ $log->action }}</span></td><td>{{ $log->description }}</td><td><code>{{ $log->ip_address ?: '—' }}</code></td></tr>@empty<tr><td colspan="5" class="empty-state">No audit events yet.</td></tr>@endforelse</tbody></table></div>@if ($logs->hasPages())<div class="pagination">{{ $logs->links() }}</div>@endif</section></div>
@endsection
