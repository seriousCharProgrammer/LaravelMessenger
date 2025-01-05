<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\VoiceMessage;
use Illuminate\Support\Facades\Auth;
use Pusher\Pusher;
use Illuminate\Support\Facades\Storage;
class VoiceMessageController extends Controller
{
    private $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher( env('VITE_PUSHER_APP_KEY'),env('VITE_PUSHER_APP_SECRET'),env('VITE_PUSHER_APP_ID'),[ 'cluster' => env('VITE_PUSHER_APP_CLUSTER'),'useTLS' => true]
    );
    }

    public function sendVoiceMessage(Request $request)
    {

        $request->validate([
            'audio_data' => 'required|file',
            'receiver_id' => 'required|integer',

        ]);

        $audioFile = $request->file('audio_data');

        $path = $audioFile->store('voice-messages', 'public');

        $voiceMessage = VoiceMessage::create([
            'sender_id' => Auth::user()->id,
            'receiver_id' => $request->receiver_id,
            'file_path' => $path,
            'duration' => $request->duration
        ]);

        $message=new Message();
        $message->from_id=Auth::user()->id;
        $message->to_id=$request->receiver_id;
        $message->voice=json_encode($path);

        $message->save();
        // Trigger Pusher event
        $this->pusher->trigger('voice.channel.'.$request->receiver_id, 'new-voice-message', [
            'message_in_voicemessage_database' => $voiceMessage->load('sender'),
            'message_in_message_database'=> $message

        ]);

        return response()->json([
            'message' => 'Voice message sent successfully',
            'voice_message' => $voiceMessage,

        ]);
    }

    public function fetchVoiceMessages(Request $request)
    {
        $messages = VoiceMessage::where(function($query) {
            $query->where('sender_id', Auth::user()->id)
                  ->orWhere('receiver_id', Auth::user()->id);
        })->with(['sender', 'receiver'])->latest()->get();

        return response()->json($messages);
    }
}
