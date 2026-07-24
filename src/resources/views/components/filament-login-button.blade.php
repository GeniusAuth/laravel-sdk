@php
    $panelPath = config('geniusauth.filament_admin_panel_path', 'admin');
    $loginRoute = "/{$panelPath}/geniusauth/login";
@endphp

<div class="geniusauth-filament-login">
    <a href="{{ $loginRoute }}"
       class="fi-btn flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
       style="width: 100%;">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ $label ?? __('Sign in with GeniusAuth') }}
    </a>
</div>
