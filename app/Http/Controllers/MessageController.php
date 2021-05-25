<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;

use function PHPUnit\Framework\isEmpty;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return Message::get()->all();

    }
    public function fetchUsers() {
        $contacts = User::where('id', '!=', auth()->user()->id)->orderBy('name')->get();

        return response()->json($contacts);
    }
    public function fetchMessages($id) {
        Message::where('user_id', $id)->where('receiver_id', auth()->user()->id)->update(['is_read' => true]);
        $messages = Message::where(function($query) use ($id){
            $query->where('receiver_id', $id)->where('user_id', auth()->user()->id);
        })
            ->orWhere(function($query) use ($id) {
                $query->where('user_id', $id)->where('receiver_id', auth()->user()->id);
            })->orderBy('created_at', 'DESC')
            ->get();
        return response()->json($messages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fileName='';
         $this->validate($request, [
             'messages' => 'nullable',
             'receiver_id'=>'required',
             'file'=>'nullable|mimes:pdf,jpg,bmp,png',
         ]);
        if($request->receiver_id==auth()->user()->id)
        {
            return response()->json('unauthorized,sender id == receiver id',401);

        }
        if ($request->hasFile('file')){
            $fileName = hexdec(uniqid()) . "." . $this->file->extension();
            $request->file('file')->storeAs('media/',$fileName,'public');
        }
        if(isEmpty($request->messages)&&isEmpty($request->file))
        {
            return response()->json('empty message', 400);

        }else{
            $message = Message::create([
                'messages' => $request->messages,
                'user_id' => auth()->user()->id,
                'receiver_id' => $request->receiver_id,
                'file' => $fileName,
            ]);
    
            return response()->json($message, 201);
        }
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }
    // Output all messages for the user with the information of the friend
    public function getAllConversation(Request $request)
    {
        $id=$request->id;

        $conversations[] = Message::with('receiver')->where('user_id', $id)->get();
        $conversations[] = Message::with('sender')->where('receiver_id', $id)->get();

        $allConversations = json_decode(json_encode($conversations), true);

        return response()->json($allConversations, 200);

    }
    public function allConversationForDetectUser(Request $request){
        $id=auth()->user()->id;
        $conversations[] =Message::where('user_id', $id)
            ->orWhere('receiver_id', $id)->get();
        return response()->json($conversations);
    }

    public function privateMessage(Request $request)
    {
        $userId = $request->user;
        $friend = $request->friend;

        // Get messages for private chat
        $messages = Message::where(['user_id' => $userId, 'receiver_id' => $friend])
            ->orWhere(function($query) use($userId, $friend) {
                $query->where(['user_id' => $friend, 'receiver_id' => $userId]);
            })->get();

        return response()->json($messages, 200);

    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $message = Message::findOrFail($id);
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $message = Message::findOrFail($id);

        $message->delete();

        return ['message' => 'Deleted successfully'];
    }
}
