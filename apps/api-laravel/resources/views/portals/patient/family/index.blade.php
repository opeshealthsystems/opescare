@extends('layouts.portal')

@section('title', 'Family Accounts — OpesCare Patient Portal')

@section('content')
<div>
    @foreach($links as $link)
        <div>{{ $link->relationship }}</div>
    @endforeach
</div>
@endsection
