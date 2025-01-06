<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Pusher\Pusher;
use Illuminate\Support\Facades\Auth;
class cancelCallController extends Controller
{
    public function index(Request $request)
    {
        //dd($request->all());
        $callerName=User::find($request->from_id)->name;

        $pusher = new Pusher( env('VITE_PUSHER_APP_KEY'),env('VITE_PUSHER_APP_SECRET'),env('VITE_PUSHER_APP_ID'),[ 'cluster' => env('VITE_PUSHER_APP_CLUSTER'),'useTLS' => true]
    );
    $htmlView = view('messenger.components.canceled-video-call',compact('callerName'))->render();

    $pusher->trigger('cancel.video.'.$request->to_id,'videoCallCancel',$htmlView);

    return view('messenger.components.canceled-video-call');

    }
}
