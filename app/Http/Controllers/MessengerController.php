<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Traits\FileUploadTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

//fetch mesages from database
/*
function fetchMessages(Request $request) {
    $request->validate([
        'id'=>'required|integer',
        'page'=>'required|integer',
    ]);

    $messages=Message::where(function($query) use ($request) {
        $query->where('from_id',Auth::user()->id)->where('to_id',$request->id);
    })->orWhere(function($query) use ($request) {
        $query->where('from_id',$request->id)->where('to_id',Auth::user()->id);
    })->latest()->paginate(10);

    if($messages->count() == 0) {
        return response()->json(['messages'=>'<div class="text-center"><p>No messages found.</p><p> Start a new Conversation</p></div>']);
    };
    $messages=$messages->reverse();



    $response=[
        'messages'=>$messages,
        'last_page'=>$messages->lastPage(),
    ];

   $allMessages='';
   foreach($messages as $message) {
    if($message->attachment) {
        $allMessages.=$this->messageCard($message,true);
    } else {
       $allMessages.=$this->messageCard($message);
    }
   }

   $response['messages']=$allMessages;
    return response()->json($response);

}

*/
function fetchMessages(Request $request) {
    $request->validate([
        'id' => 'required|integer',
        'page' => 'required|integer',
    ]);

    $messages = Message::where(function($query) use ($request) {
        $query->where('from_id', Auth::user()->id)
              ->where('to_id', $request->id);
    })->orWhere(function($query) use ($request) {
        $query->where('from_id', $request->id)
              ->where('to_id', Auth::user()->id);
    })->latest()->paginate(15);

    if ($messages->isEmpty()) {
        return response()->json([
            'messages' => '<div class="text-center"><p>No messages found.</p><p>Start a new Conversation</p></div>',
        ]);
    }

    // Preserve pagination information before reversing
    $paginatedData = [
        'current_page' => $messages->currentPage(),
        'last_message'=> $messages->last(),
        'last_page' => $messages->lastPage(),
        'total' => $messages->total(),
        'total_pages' => $messages->lastPage(),
    ];

    // Reverse the message order
    $reversedMessages = $messages->getCollection()->reverse();

    $allMessages = '';
    foreach ($reversedMessages as $message) {
        if ($message->attachment) {
            $allMessages .= $this->messageCard($message, true);
        } else {
            $allMessages .= $this->messageCard($message);
        }
    }

    return response()->json([
        'messages' => $allMessages,
        'last_page' => $paginatedData['last_page'],
        'totalmessages' => $paginatedData['total'],
        'totalpages' => $paginatedData['total_pages'],
        'last_message' => $paginatedData['last_message'],
    ]);
}

 //fetch conatcts from database
    function fetchContacts(Request $request) {
        $users=Message::join('users',function($join){
             $join->on('messages.from_id','=','users.id')->orOn('messages.to_id','=','users.id');
        })->where(function($q){
            $q->where('messages.from_id',Auth::user()->id)->orWhere('messages.to_id',Auth::user()->id);
        })->where('users.id','!=',Auth::user()->id)->select('users.*',DB::raw('MAX(messages.created_at) max_created_at'))->orderBy('max_created_at','desc')->groupBy('users.id')->paginate(8);



    if($users->count() >0){
        $contacts='';
        foreach($users as $user) {
           $contacts.=$this->getContactItem($user);
        }

    }
    else{
        $contacts='<div class="text-center"><p>No contacts found.</p></div>';
    }
    return response()->json(['contacts'=>$contacts,'last_page'=>$users->lastPage()]);

}

function getContactItem($user){
    $lastMessage=Message::where('from_id',Auth::user()->id)->where('to_id',$user->id)->orWhere('from_id',$user->id)->where('to_id',Auth::user()->id)->latest()->first();

    $unseenCounter = Message::where('from_id', $user->id)
                            ->where('to_id', Auth::user()->id)
                            ->where('seen', 0)
                            ->count();

    return view('messenger.components.contact-list-item',compact('user','lastMessage','unseenCounter'))->render();
}

function updatecontactItem(Request $request){
    $request->validate([
        'user_id'=>'required|integer',
    ]);
    $user=User::where('id',$request->user_id)->first();
    if(!$user) {
        return response()->json(['error'=>'User not found.'],401);
    }

    $contactItem=$this->getContactItem($user);
    return response()->json(['contactItem'=>$contactItem],200);
}

function makeSeen(Request $request) {
    Message::where('from_id',$request->id)->where('to_id',Auth::user()->id)->where('seen',0)->update(['seen'=>1]);
    return true;
}
}
