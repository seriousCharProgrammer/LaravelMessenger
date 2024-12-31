<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Traits\FileUploadTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessengerController extends Controller
{
    use FileUploadTrait;
    function index() :View {
        return view('messenger.layouts.app');
    }

    /**
     *
     *Search user progiles
     */

    public function search(Request $request) {
        $getRecords=null;
        $query=$request->get('query');
        $records= User::where('id','!=',Auth::user()->id)->where('name','LIKE',"%{$query}%")->orWhere('username','LIKE',"%{$query}%")->paginate(10);
        if($records->count() == 0) {
            return response()->json(['records'=>"<div class='text-center'><p>  No user found.</p></div>"]);

        }

/*
        foreach($records as $record) {
            $getRecords.=view('messenger.components.search-item',compact('record'))->render();
        }
*/
$getRecords = $records->map(function($record) {
    return view('messenger.components.search-item', compact('record'))->render();
})->join('');


         return response()->json(['records'=>$getRecords,
        'last_page'=>$records->lastPage(),]);
}

public function fetchIdInfo(Request $request) {
    $fetch =User::where('id',$request->id)->first();
    return response()->json(['fetch'=>$fetch]);
}

function messageCard($message,$attachment=false)
{
    return view('messenger.components.message-card',compact('message','attachment'))->render();
}

 function sendMessage(Request $request) {



    $request->validate([
        'message'=>'string|nullable',
        'id'=>'required|integer',
        'temporaryMsgId'=>'required',
        'attachment'=>'nullable|image|max:1024'
    ]);

    //store the message in db
    $attachemntPath = $this->uploadFile($request,'attachment');

    $message=new Message();
    $message->from_id=Auth::user()->id;
    $message->to_id=$request->id;
    $message->body=$request->message;
    if($attachemntPath) {
        $message->attachment=json_encode($attachemntPath);
    };
    $message->save();

    return response()->json(['message'=>$message->attachment?$this->messageCard($message,true):$this->messageCard(message: $message),'tempID'=>$request->temporaryMsgId]);

}



}
