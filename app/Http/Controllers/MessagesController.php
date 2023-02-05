<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MessagesController extends Controller
{
    public function sendMessage($id){
        $user = auth()->user();

        if(!User::find($id)){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Invalid User ID' : 'لقد قمت بإدخال معرف مستخدم خاطئ'
            ]);
        }

        $message = new Message([
            'senderId' => $user->id,
            'receiverId' => $id,
            'body' => request('body'),
        ]);

        $message->save();

        return response()->json([
            'status' => 1,
            'message' => auth()->user()->local == 'en' ? 'Message Sent Successfully' : 'تم الإرسال بنجاح'
        ]);
    }


    public function showMessages($id){
        $user = auth()->user();

        $messages = Message::where([ ['senderId' , $user->id] , ['receiverId' , $id] ])
            ->orWhere([ ['senderId' , $id] , ['receiverId' , $user->id] ])->orderBy('id')->get();

        return response()->json([
            'status' => 1,
            'user_id' => $user->id,
            'data' => $messages
        ]);

    }
}
