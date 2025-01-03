<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


// Define authorization logic for a private channel
Broadcast::channel('message.sent.{id}', function ($user, $id) {
    // Check if the user is the intended recipient of the message or has access to it
    return (int) $user->id === (int) $id;
});
