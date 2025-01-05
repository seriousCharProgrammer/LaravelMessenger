
{{--
@php
$voicePath = json_decode($message->voice);
@endphp
<div class="wsus__single_chat_area message-card" data-id="{{ $message->id }}">
<div class="wsus__single_chat {{ $message->from_id === auth()->user()->id ? 'chat_right' : '' }}">

    <audio controls>
        <source src="{{ asset($voicePath) }}" type="audio/mpeg">
        Your browser does not support the audio element.
      </audio>
    <span class="time">{{ timeAgo($message->created_at) }}</span>
    @if ($message->from_id === auth()->user()->id)
        <a class="action dlt-message" data-id="{{ $message->id }}" href=""><i class="fas fa-trash"></i></a>
    @endif
</div>
</div>


--}}
