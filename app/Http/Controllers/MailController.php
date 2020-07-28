<?php

namespace App\Http\Controllers;

use App\Mail\MessageMail;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $email = auth('api') ? auth('api')->user()->email : 'null';
        $mails = \App\Mail::where('receiver_mail',$email)->get();
        return response()->json($mails);
    }
    public function sent()
    {
        $email = auth('api') ? auth('api')->user()->email : 'null';
        $mails = \App\Mail::where('sender_mail',$email)->get();
        return response()->json($mails);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $this->getData($request);
        $mail = \App\Mail::create($data);
        Mail::to($request['receiver_mail'])->send(new MessageMail($request));
        return response()->json($mail);

    }



    public function destroy(Request $request){
        $cart = Cart::destroy($request->id);
        return response()->json($cart);
    }
    public function modifyQty(Request $request){
        $data = $request->all();
        $cart = Cart::findOrFail($data['id']);
        $status = 1;
        if($data['add'] != 1){
            if($cart->quantity > 1){
                $cart->quantity = $cart->quantity - 1;
                $status = 1;
            }else {
                $status = 0;
            }
        }else {
            $cart->quantity = $cart->quantity + 1;
            $status = 1;
        }
        $cart->save();
        return response()->json($status);
    }
    protected function getData(Request $request)
    {
        $rules = [
            'receiver_mail' => 'required|string',
            'sender_mail' => 'nullable',
            'subject' => 'string',
            'message' => 'string',
        ];
        $data = $request->validate($rules);
        $data['sender_mail'] = auth('api')->user()->email;
        return $data;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $mail = \App\Mail::find($id);
        return response()->json($mail);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

}
