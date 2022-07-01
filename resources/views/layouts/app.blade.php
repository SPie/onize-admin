@extends('layouts.base')

@section('body')
    <div>
        <div>
            <div>
                <a href="{{ route('users.profile') }}">{{ __('menu.profile') }}</a>
            </div>
            <div>
                <a href="">{{ __('menu.logout') }}</a>
            </div>
        </div>
    </div>

    @yield('content')
    
    @isset($slot)
        {{ $slot }}
    @endisset
@endsection
