@extends('layouts.user-profile')

@section('title', 'პროფილი')

@section('profile-content')
    @include('profile._details-section')
    @include('profile._password-section')
    @include('profile._delete-account-section')
@endsection
