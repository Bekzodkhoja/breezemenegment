<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Mail\ApplicationCreated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class ApplicationController extends Controller
{

    public function store(Request $request)
    {

        if( $this->chackDay()){
           return redirect()->back()->with('message','You can create only 1 application a day');
        }
       


        if($request->hasFile('file'))
        {
            $name=$request->file('file')->getClientOriginalName();
            $path=$request->file('file')->storeAs('files',$name,'public');
        }

         $request->validate([
            'subject'=>'required|max:255',
            'message'=>'required',
            'file'=>'file|mimes:jpg,png,pdf',
            
         ]);

        $application=Application::create([
            'user_id'=>auth()->user()->id,
            'subject'=>$request->subject,
            'message'=>$request->message,
            'file_url'=>$path ?? null,
        ]);
 
    dispatch(new SendEmailJob($application));
  

    return redirect()->back();
}

protected function chackDay()
{
    if(auth()->user()->applications()->latest()->first()==null){
        return false;
    }
    $last_application=auth()->user()->applications()->latest()->first();
    $last_data=Carbon::parse($last_application->created_at)->format('Y.m.d');
    $today=Carbon::now()->format('Y.m.d');

    if($last_data==$today){
        return true;
    }

}
}
