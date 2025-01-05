<?php

namespace App\Http\Controllers;
use Pusher\Pusher;
use App\Events\Message as MessageEvent;
use App\Events\MessageSent;
use App\Models\Favorite;
use App\Models\Message;
use App\Models\User;
use App\Models\UserStatus;
use App\Traits\FileUploadTrait;
use Illuminate\Container\Attributes\Log;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;



class MessengerController extends Controller
{
    use FileUploadTrait;
    private $usersLoggedIn = [];

    function index() :View {

        $onlineUsers = UserStatus::where('status', 'online')->pluck('user_id')->toArray();


    if (!in_array(Auth::user()->id, $onlineUsers)) {
        UserStatus::create([
            'user_id' => Auth::user()->id,
            'status' => 'online',
        ]);
        $onlineUsers[] = Auth::user()->id;
    }
         //$pusher->trigger('user.online', 'onlineUser', $onlineUsers);


        $favoriteList=Favorite::with('user:id,name,avatar')->where('user_id',Auth::user()->id)->get();
        $this->fetchOnlineStatus();





        return view('messenger.layouts.app',compact('favoriteList'));
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
    $favorite=Favorite::where(['user_id'=>Auth::user()->id,'favorite_id'=>$fetch->id])->exists();
    $sharedPhotos=Message::where(function($query) use ($request) {
        $query->where('from_id', Auth::user()->id)
              ->where('to_id', $request->id)->whereNotNull('attachment');
    })->orWhere(function($query) use ($request) {
        $query->where('from_id', $request->id)
              ->where('to_id', Auth::user()->id)->whereNotNull('attachment');
    })->latest()->get();
    $content='';
    foreach($sharedPhotos as $photo) {
        $content.=view('messenger.components.gallery-item',compact('photo'))->render();
    }

    return response()->json(['fetch'=>$fetch, 'favorite'=>$favorite,'shared_photos'=>$content]);
}
/*
function messageCard($message,$attachment=false,$voice=false)
{
    return view('messenger.components.message-card',compact('message','attachment','voice'))->render();
}
    */
    function messageCard($message, $attachment = false, $voice = false)
{
    return view('messenger.components.message-card', compact('message', 'attachment', 'voice'))->render();
}




 function sendMessage(Request $request) {

    //dd($request->all());


$pusher = new Pusher( env('VITE_PUSHER_APP_KEY'),env('VITE_PUSHER_APP_SECRET'),env('VITE_PUSHER_APP_ID'),[ 'cluster' => env('VITE_PUSHER_APP_CLUSTER'),'useTLS' => true]
    );

    $request->validate([
        'message'=>'string|nullable',
        'id'=>'required|integer',
        'temporaryMsgId'=>'required',
        'attachment'=>'nullable|image|max:1024'
    ]);

    //store the message in db
    $attachemntPath = $this->uploadFile($request,'attachment');
    $voicepath = $this->uploadVoice($request, 'audio_data');

    $message=new Message();
    $message->from_id=Auth::user()->id;
    $message->to_id=$request->id;
    $message->body=$request->message;
    if($attachemntPath) {
        $message->attachment=json_encode($attachemntPath);
    };

    if($voicepath)
    {
        $message->voice=json_encode($voicepath);
    }

    $message->save();

    //broadcast the message
    $pusher->trigger('message.sent.'.$message->to_id, 'MessageSent', ['id'=>$message->id,'body'=>$message->body,'from_id'=>$message->from_id,'to_id'=>$message->to_id,'attachment'=>json_decode($message->attachment),'voice'=>json_decode($message->voice)]);

    if($message->voice)
    {
        return response()->json(['message'=>$this->messageCard($message,false,true),'tempID'=>$request->temporaryMsgId]);
    }



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
            'messages' => '<div class="text-center no_messages"><p>No messages found.</p><p>Start a new Conversation</p></div>',
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
        } else if ($message->voice)
        {
            $allMessages .= $this->messageCard($message, false,true);
        }

        else {
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
        $loggedInUsers = UserStatus::where('status', 'logged_in')->get()->pluck('user_id')->toArray();
    $onlineUsers = UserStatus::where('status', 'online')->pluck('user_id')->toArray();
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
        $contacts='<div class="text-center no_contact"><p>No contacts found.</p></div>';
    }
    $pusher = new Pusher( env('VITE_PUSHER_APP_KEY'),env('VITE_PUSHER_APP_SECRET'),env('VITE_PUSHER_APP_ID'),[ 'cluster' => env('VITE_PUSHER_APP_CLUSTER'),'useTLS' => true]
    );
        $pusher->trigger('user.loggedin', 'LoggedIN', $loggedInUsers);
        $pusher->trigger('user.online', 'onlineUser', $onlineUsers);
    return response()->json(['contacts'=>$contacts,'last_page'=>$users->lastPage()]);

}

function getContactItem($user){
    $loggedInUsers = UserStatus::where('status', 'logged_in')->get()->pluck('user_id')->toArray();
    $onlineUsers = UserStatus::where('status', 'online')->pluck('user_id')->toArray();
    $lastMessage=Message::where('from_id',Auth::user()->id)->where('to_id',$user->id)->orWhere('from_id',$user->id)->where('to_id',Auth::user()->id)->latest()->first();

    $unseenCounter = Message::where('from_id', $user->id)
                            ->where('to_id', Auth::user()->id)
                            ->where('seen', 0)
                            ->count();

    return view('messenger.components.contact-list-item',compact('user','lastMessage','unseenCounter','onlineUsers','loggedInUsers'))->render();
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
function favorite(Request $request) {
    $request->validate([
        'user_id'=>'required|integer',
    ]);
    $query=Favorite::where('user_id',Auth::user()->id)->where('favorite_id',$request->user_id);
    $favoriteStatus=$query->exists();
    if(!$favoriteStatus) {
        $favorite=new Favorite();
        $favorite->user_id=Auth::user()->id;
        $favorite->favorite_id=$request->user_id;
        $favorite->save();
        return response()->json(['status'=>'added']);
    }
    else
    {
        $query->delete();
        return response()->json(['status'=>'removed']);
    }



}
/*
// Controller function
public function fetchFavorites() {
    $favorites = Favorite::with('user:id,name,avatar')
        ->where('user_id', Auth::user()->id)
        ->get();

    $html = view('messenger.components.favorites-list', compact('favorites'))->render();
    return response()->json(['favorite_list' => $html]);
}
    */
/*
 @foreach ($favoriteList as $item )
            <div class="col-xl-3 messenger-list-item" data-id="{{$item->user?->id}}">
                <div class="wsus__favourite_item ">
                  <div class="img">
                    <img
                      src="{{asset($item->user?->avatar)}}"
                      alt="User"
                      class="img-fluid"
                    />
                    <span class="inactive"></span>
                  </div>
                  <p>{{$item->user?->name}}</p>
                </div>
              </div>

            @endforeach

*/
//delete message
function deleteMessage(Request $request) {


    $request->validate([
        'id'=>'required|integer',
    ]);
    $message=Message::findOrfail($request->id);

    if($message->from_id != Auth::user()->id) {
        return response()->json(['error'=>'You are not authorized to delete this message.','success'=>false],200);
    }

    else {
        $message->delete();
        return response()->json(['message'=>'Message deleted successfully.','id'=>$request->id,'success'=>true],200);
    }


}

function deleteOnlineStatus(){
    UserStatus::where('user_id',Auth::user()->id)->where('status','online')->delete();
    return response()->json(['status'=>'deleted']);
}

function fetchOnlineStatus()
{
    $onlineUsers = UserStatus::where('status', 'online')->pluck('user_id')->toArray();
    $pusher = new Pusher( env('VITE_PUSHER_APP_KEY'),env('VITE_PUSHER_APP_SECRET'),env('VITE_PUSHER_APP_ID'),[ 'cluster' => env('VITE_PUSHER_APP_CLUSTER'),'useTLS' => true]
    );

        $pusher->trigger('user.online', 'onlineUser', $onlineUsers);




    return response()->json(['onlineUsers' => $onlineUsers]);
}

}
