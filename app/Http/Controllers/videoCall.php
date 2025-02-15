<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Pusher\Pusher;
use Illuminate\Support\Facades\Auth;

class videoCall extends Controller
{
    public function index(Request $request)
    {
        $callerName=User::find($request->from_id)->name;

        $pusher = new Pusher( env('VITE_PUSHER_APP_KEY'),env('VITE_PUSHER_APP_SECRET'),env('VITE_PUSHER_APP_ID'),[ 'cluster' => env('VITE_PUSHER_APP_CLUSTER'),'useTLS' => true]
    );
    $htmlView = view('messenger.components.recever-video-call',compact('callerName'))->render();
    $videoHtml=view('messenger.components.video-call')->render();

    $pusher->trigger('video.call.'.$request->to_id,'videoCall',['html'=>$htmlView,'to_id'=>$request->to_id,'from_id'=>$request->from_id,'videoHtml'=>$videoHtml]);
    return view('messenger.components.video-call');

}
function endCall(Request $request)
{

    $pusher = new Pusher( env('VITE_PUSHER_APP_KEY'),env('VITE_PUSHER_APP_SECRET'),env('VITE_PUSHER_APP_ID'),[ 'cluster' => env('VITE_PUSHER_APP_CLUSTER'),'useTLS' => true]);

    $pusher->trigger('video.end.'.$request->to_id,'endCall',['success'=>true]);
    return response()->json(['success'=>true]);
}





}
