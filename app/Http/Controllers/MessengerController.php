<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessengerController extends Controller
{
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

}
