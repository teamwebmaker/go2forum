@extends('layouts.user-profile')

@section('title', 'პირადი მიმოწერა')

@section('profile-content')
    <livewire:private-chat :initial-conversation-id="$initialConversationId" :initial-recipient="$initialRecipient" />
@endsection
