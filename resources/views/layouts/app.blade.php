@extends('layouts.base')

@section('body')
    <div>
        <div>
            <div>
                {{ __('menu.profile') }}
            </div>
            <div>
                {{ __('menu.logout') }}
            </div>
        </div>
    </div>

    @yield('content')
    
    @isset($slot)
        {{ $slot }}
    @endisset
@endsection
