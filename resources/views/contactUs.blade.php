@extends('layouts.app')

@section('content')
    {!! NoCaptcha::renderJs() !!}
    <form method="POST" action="/aaa" enctype="multipart/form-data">
        {{ csrf_field() }}
        <input type="file" name="attachment">
        <input type="submit">

        {!! NoCaptcha::display() !!}
    </form>


    @if ($errors->has('g-recaptcha-response'))

        {{ $errors->first('g-recaptcha-response') }}
    @endif
@endsection
