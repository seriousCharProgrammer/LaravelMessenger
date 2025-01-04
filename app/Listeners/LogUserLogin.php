<?php

namespace App\Listeners;
use App\Models\UserStatus;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
class LogUserLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event)
{
    $loggedInUsers = UserStatus::where('status', 'logged_in')->get()->pluck('user_id')->toArray();;
    if (!in_array(Auth::user()->id, $loggedInUsers)) {
        UserStatus::create([
            'user_id' => $event->user->id,
            'status' => 'logged_in',
        ]);
    }

}

}
