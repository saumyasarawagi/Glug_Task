<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Input;
use Validator;
use Redirect;
use Mail;
use Session;
use App\Event;
use App\onlineFeedback;
use App\offlineFeedback;

class PagesController extends Controller
{
    public function getHome()
    {
    	$events=Event::all();
    	return view('pages.home')->withEvents($events);
    }
    public function getDownload()
    {
    	//return view('')
    }
    public function getOnlineFeedback()
    {
    	$events=Event::all();
    	return view('pages.onlineFeedback')->withEvents($events);
    }
    public function postOnlineFeedback(Request $request)
    {
    	$data=Input::all();
		$rules=array(
			'event_name' => 'required',
			'event_heard_from' => 'required',						// => is separator for assosciative array
			'one' => 'required',		//one=>userfriendly
			'two' => 'required',		//two=>design
			'three' => 'required',		//three=>diversity
			'four' => 'required',		//four=>helpavailability
			'five' => 'required',		//five=>innovation
			'recommend' => 'required',
			'overall' =>'required',
			'yes_no'=>'required'
		);

		$validator=Validator::make($data,$rules);
		if($validator->fails()){
		return Redirect::to('/onlineFeedback')->withErrors($validator)->withInput();
		}
		$event=Event::find($request->event_name);
		$feedback=new onlineFeedback;
		$feedback->event_heard_from = $request->event_heard_from;
        $feedback->userfriendly  = $request->one;
        $feedback->design = $request->two;
        $feedback->diversity = $request->three;
        $feedback->helpavailability = $request->four;
        $feedback->innovation = $request->five;
        $feedback->recommend = $request->recommend;
        $feedback->overall = $request->overall;
        $feedback->yes_no = $request->yes_no;
        $feedback->suggestions =$request->suggestions;
        $feedback->event()->associate($event);
        $feedback->save();


		/* If you want to use mailing technology simply uncomment the below section*/

		/*$feedbackcontent=array(
			'event_name' => $data['event_name'],					// key(just like indexes) => value;
			'event_heard_from' => $data['event_heard_from'],
			'one' => $data['one'],
			'two' => $data['two'],
			'three' => $data['three'],
			'four' => $data['four'],
			'five' => $data['five'],
			'organized' => $data['organized'],
			'overall' => $data['overall'],
			'yes_no' => $data['yes_no'],
			'suggestions' => $data['suggestions']
		);
	
		Mail::send('pages.send_mail_template',$feedbackcontent,function($message)
		{
			$message->to('bholajrs@gmail.com','Bhola')->subject('Someone gave feedback to GLUG Event')->from('feedback@glug.com');
		});*/
		Session::flash('success_f','The feedback has been submitted!!');
		return Redirect::to('/');
    }

    public function getOfflineFeedback()
    {
    	$events=Event::all();
    	return view('pages.offlineFeedback')->withEvents($events);
    }
    public function postOfflineFeedback(Request $request)
    {
    	$data=Input::all();
		$rules=array(
			'event_name' => 'required',
			'event_heard_from' => 'required',						// => is separator for assosciative array
			'one' => 'required',		//one=>content
			'two' => 'required',		//two=>presentation
			'three' => 'required',		//three=>speaker
			'four' => 'required',		//four=>support_staff
			'five' => 'required',		//five=>organization
			'six' => 'required',        //six=>location
			'recommend' => 'required',
			'overall' =>'required',
			'yes_no'=>'required'
		);

		$validator=Validator::make($data,$rules);
		if($validator->fails()){
		return Redirect::to('/offlineFeedback')->withErrors($validator)->withInput();
		}
		$event=Event::find($request->event_name);
		$feedback=new offlineFeedback;
		$feedback->event_heard_from = $request->event_heard_from;
        $feedback->content  = $request->one;
        $feedback->presentation = $request->two;
        $feedback->speaker = $request->three;
		$feedback->support_staff = $request->four;
		$feedback->organization = $request ->five;
        $feedback->location = $request->six;
        $feedback->recommend = $request->recommend;
        $feedback->overall = $request->overall;
        $feedback->yes_no = $request->yes_no;
        $feedback->suggestions =$request->suggestions;
        $feedback->event()->associate($event);
        $feedback->save();


		/* If you want to use mailing technology simply uncomment the below section*/

		/*$feedbackcontent=array(
			'event_name' => $data['event_name'],					// key(just like indexes) => value;
			'event_heard_from' => $data['event_heard_from'],
			'one' => $data['one'],
			'two' => $data['two'],
			'three' => $data['three'],
			'four' => $data['four'],
			'five' => $data['five'],
			'organized' => $data['organized'],
			'overall' => $data['overall'],
			'yes_no' => $data['yes_no'],
			'suggestions' => $data['suggestions']
		);
	
		Mail::send('pages.send_mail_template',$feedbackcontent,function($message)
		{
			$message->to('bholajrs@gmail.com','Bhola')->subject('Someone gave feedback to GLUG Event')->from('feedback@glug.com');
		});*/
		Session::flash('success_f','The feedback has been submitted!!');
		return Redirect::to('/');
    }
}
