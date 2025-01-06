<?php

namespace App\Http\Controllers;

use App\Models\BlockedContacts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Pusher\Pusher;

class BlockContactController extends Controller
{
    function blockContact (Request $request){

            $request->validate([
                'blocked_user_id' => 'required',
            ]);



            $user = Auth::user()->id;
            $blockedUser = $request->blocked_user_id;

            // Check if already blocked
            $exists = BlockedContacts::where('id', $blockedUser)->exists();
            $htmlView=view('messenger.layouts.user-info-sidebar',compact('exists'));

            if (!$exists) {
                BlockedContacts::create([
                    'id' => $blockedUser,
                    'user_id'=>$user,
                ]);

                return response()->json(['status' => 'success','html'=>$htmlView]);
            }

            return response()->json(['status' => 'failed', 'message' => 'User already blocked']);
        }

        public function unblockContact(Request $request)
        {
            $request->validate([
                'blocked_user_id' => 'required|exists:users,id',
            ]);

            $user = Auth::user();
            $blockedUser = $request->blocked_user_id;

            BlockedContacts::where('user_id', $user->id)
                ->where('id', $blockedUser)
                ->delete();

            return response()->json(['status' => 'success', 'message' => 'User unblocked']);
        }

        public function fetchBlockContact(Request $request)
        {
            $pusher = new Pusher( env('VITE_PUSHER_APP_KEY'),env('VITE_PUSHER_APP_SECRET'),env('VITE_PUSHER_APP_ID'),[ 'cluster' => env('VITE_PUSHER_APP_CLUSTER'),'useTLS' => true]
    );
            $blockedContactIds = BlockedContacts::where('user_id', Auth::user()->id)->pluck('id');
            $allBlockedusers=BlockedContacts::all()->pluck('id');
            $pusher->trigger('blocked.users','allBlockedusers',$allBlockedusers);
            return response()->json(['status'=>'success','blockedList'=>$blockedContactIds,'allBlockedusers'=>$allBlockedusers]);
        }

}
