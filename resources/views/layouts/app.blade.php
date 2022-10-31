@extends('layouts.base')

@section('body')
    <div>
        <div>
            <div>
                <a href="{{ route('users.profile') }}">{{ __('menu.profile') }}</a>
            </div>
            <livewire:auth.logout />
        </div>
    </div>

    @yield('content')
    
    @isset($slot)
        {{ $slot }}
    @endisset
@endsection
