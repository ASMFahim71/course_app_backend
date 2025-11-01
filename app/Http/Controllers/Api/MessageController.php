<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Chat;
use App\Models\Message;

use function Pest\Laravel\json;

class MessageController extends Controller
{
    //fetch all users
    public function users(Request $request)
    {
        $current_user = Auth::user()->id;
        $chatUserIds = Chat::where('sender_id', $current_user)->orWhere('receiver_id', $current_user)->pluck('sender_id','receiver_id')->unique()->filter(fn($id) => ($id != $current_user))->values();
        $users=Member::whereIn('id',$chatUserIds)->get();
        return response()->json([
            'code'=>200,
            'msg'=>"Successfully fetched users",
            'data'=>$users,
        ],200);
    }
    // fetch author

    public function author(Request $request)
    {
        $token = $request->token;
        $author = Member::where('token', $token)->first();
        return response()->json([
            'code' => 200,
            'msg' => "Successfully fetch author",
            'data' => $author
        ], 200);
    }

    //send message to others

    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'message' => "required",
                'receiver_id' => "required|exists:members,id",
                'type' => "required|in:text,video,photo",
            ]);
            $chat = Chat::Where(function ($query) use ($request) {
                $query->where('sender_id', Auth::user()->id)->where('receiver_id', $request->receiver_id);
            })->orWhere(function ($query) use ($request) {
                $query->where('sender_id', $request->receiver_id)->where('receiver_id', Auth::user()->id);
            })->first();
            if (!$chat) {
                Chat::create([
                    'sender_id' => Auth::user()->id,
                    'receiver_id' => $request->receiver_id
                ]);
            }
            $msg = Message::create([
                'receiver_id' => $request->receiver_id,
                'sender_id' => Auth::user()->id,
                'message' => $request->message,
                'type' => $request->type,
                'chat_id' => $chat->id,
            ]);

            broadcast(new MessageSent($msg))->toOthers();

            return response()->json([
                'code' => 200,
                'msg' => "message sent successfully",
                'data' => $msg,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'code' => 500,
                    'msg' => "failed to sent message",
                    'data' => $e->getMessage(),
                ],
                500
            );
        }
    }

    //get message from other person

    public function getMessage($id)
    {
        $messages = Message::where(function ($query) use ($id) {
            $query->where('sender_id', Auth::user()->id)->where('receiver_id', $id);
        })->orWhere(function ($query) use ($id) {
            $query->where('sender_id', $id)->where('receiver_id', Auth::user()->id);
        })->get();

        $messages = $messages->map(function ($message) {
            $message->is_me = $message->sender_id == Auth::user()->id;
            return $message;
        });
        return response()->json($messages);
    }
}
