@extends('Centaur::layout')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    @if (Sentinel::check())
    <div class="jumbotron">
        <h1>Hello, {{ Sentinel::getUser()->email }}!</h1>
        <p>You are now logged in.</p>
    </div>
    @else
        <div class="jumbotron">
            <h1>Welcome, Guest!</h1>
            <p>You must login to continue.</p>
            <p><a class="btn btn-primary btn-lg" href="{{ route('auth.login.form') }}" role="button">Log In</a></p>
        </div>
    @endif

    <?php
        $user = Sentinel::findById(1);

        // var_dump(Activation::create($user));
    ?>
</div>
@stop