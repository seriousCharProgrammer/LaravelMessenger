<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Pusher\Pusher;
use Illuminate\Support\Facades\Auth;

class WebRtcController extends Controller
{
    function handleSignal (Request $request)
    {
        $pusher = new Pusher( env('VITE_PUSHER_APP_KEY'),env('VITE_PUSHER_APP_SECRET'),env('VITE_PUSHER_APP_ID'),[ 'cluster' => env('VITE_PUSHER_APP_CLUSTER'),'useTLS' => true]);
        $data = [
            'peerId' => $request->peerId,
            'username' => $request->username,
        ];


        $pusher->trigger('webrtc.channel.'.$request->to_id,'user-connected',$data);
        return response()->json(['success'=>true]);

    }
}
