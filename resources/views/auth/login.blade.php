@extends('layouts.app')

@section('content')
<div class="login-page">
    <div class="login-art"><div class="art-grid"></div><div class="art-copy"><div class="login-brand"><img src="{{ asset('dswd-logo-transparent.png') }}" alt="Department of Social Welfare and Development logo"><span>EDL Management System</span></div><p>External Dynamic List management, clear and accountable.</p></div><div class="art-stamp">EXTERNAL DYNAMIC LIST<br><span>EDL MANAGEMENT SYSTEM</span></div></div>
    <div class="login-panel"><div class="login-inner"><div class="eyebrow">Secure workspace</div><h1>Welcome back.</h1><p class="muted">Sign in to manage your network whitelist.</p>
        @if ($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('login.store') }}" class="stack-form">@csrf
            <label class="{{ $errors->has('email') ? 'has-error' : '' }}">Email address<input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@company.com">@error('email')<span class="field-error">{{ $message }}</span>@enderror</label>
            <label class="{{ $errors->has('password') ? 'has-error' : '' }}">Password<input type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">@error('password')<span class="field-error">{{ $message }}</span>@enderror</label>
            <label class="check-label"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
            <button class="primary-button" type="submit">Enter console <span>→</span></button>
        </form>
        <p class="login-note">Authorized access for EDL administrators and operators.</p>
    </div></div>
</div>
@endsection
