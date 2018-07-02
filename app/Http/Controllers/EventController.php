<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Event;
use App\offlineFeedback;
use App\onlineFeedback;
use Session;
use Lava;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events=Event::all();
        return view('events.index')->withEvents($events);
    }

    public function create()
    {
        //
        return view('events.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $this->validate($request,array(
                'date'=>'required|max:255',
                'name' =>'required|max:255',
                'onoff'=>'required',
                'desc'=>'required',
                'venue' =>'required',
            ));
        $event=new Event;
        $event->date = $request->date;
        $event->name  = $request->name;
        $event->onoff = $request->onoff;
        $event->description = $request->desc;
        $event->venue = $request->venue;
        $event->save();
        Session::flash('success','The event was successfully saved !');
        return redirect()->route('events.index');

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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $event=Event::find($id);
        return view('events.edit')->withEvent($event);
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
        $event=Event::find($id);
        $this->validate($request,array(
                'date'=>'required|max:255',
                'name' =>'required|max:255',
                'onoff' =>'required|max:255',
                'description'=>'required',
                'venue' =>'required',
            ));
        $event->date = $request->date;
        $event->name  = $request->name;
        $event->onoff = $request->onoff;
        $event->description = $request->description;
        $event->venue = $request->venue;
        $event->save();
        Session::flash('success','The event was successfully saved !');
        return redirect()->route('events.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $event=Event::find($id);
        $event->delete();
        Session::flash('success','The event was successfully deleted');
        return redirect()->route('events.index');
    }

    public function getAdminDashboard()
    {
        return view('admin_dashboard');
    }

    public function getFeedbackDashboard()
    {
        $overall = Lava::DataTable();
        $events=Event::all();
        $overall->addStringColumn('Event')->addNumberColumn('Overall Rating');

         foreach($events as $event) {
                if($event->onoff=="online")
                $overall->addRow([$event->name, online_feedbacks::where('event_id',$event->id)->avg('overall')]);
               else 
                $overall->addRow([$event->name, offline_feedbacks::where('event_id',$event->id)->avg('overall')]);

            }
        
        Lava::AreaChart('Overall', $overall, [
            'title' => 'Overall increment | decrement',
            'legend' => [
            'position' => 'in'
            ]
            ]);

        return view('feedback_dashboard')->withEvents($events);
    }
    public function getOnlineFeedbackResult($id)
    {
        $event=Event::where('id','=',$id)->first();
        $total_feedback=online_feedbacks::where('event_id','=',$id)->count();
        /* Event heard from */
        $publicity = Lava::DataTable();
        $publicity->addStringColumn('reasons')
                 ->addNumberColumn('Percent')
                 ->addRow(['Posters', online_feedbacks::where([['event_heard_from','poster'],['event_id',$id]])->count()])
                 ->addRow(['Class Orientation', online_feedbacks::where([['event_heard_from','class_orientation'],['event_id',$id]])->count()])
                 ->addRow(['Friends', online_feedbacks::where([['event_heard_from','friends'],['event_id',$id]])->count()])
                 ->addRow(['Seniors', online_feedbacks::where([['event_heard_from','seniors'],['event_id',$id]])->count()])
                 ->addRow(['Others', online_feedbacks::where([['event_heard_from','others'],['event_id',$id]])->count()]);
        Lava::PieChart('event_heard', $publicity, [
            'title'  => 'Publicity of Event',
            'is3D'   => true
            ]);

        /* Satisfactory level of event */
        $rates  = Lava::DataTable();
        $rates->addStringColumn('Subject')
                ->addNumberColumn('Rates')
                ->addRow(['User FRiendly',online_feedbacks::where('event_id',$id)->avg('userfriendly')])
                ->addRow(['Design', online_feedbacks::where('event_id',$id)->avg('design')])
                ->addRow(['Diversity',  online_feedbacks::where('event_id',$id)->avg('diversity')])
                ->addRow(['Help Availability', online_feedbacks::where('event_id',$id)->avg('helpavailability')])
                ->addRow(['Innovation',   online_feedbacks::where('event_id',$id)->avg('innovation')]);
        Lava::BarChart('Rates', $rates, [ 'title' => 'Part rating of event']);

       /* recommend to your friends */
       $recommend  = Lava::DataTable();
       $recommend->addStringColumn('Subject')
               ->addNumberColumn('How likely, would you recommend the event to your friends ?')
               ->addRow(['Recommend',online_feedbacks::where('event_id',$id)->avg('recommend')]);
       Lava::BarChart('Recommend', $recommend, ['title'=>'How likely, would you recommend the event to your friends  ?']);

        /* Want such Event to be conducted */
        $options = Lava::DataTable();
        $options->addStringColumn('Event')
        ->addNumberColumn('Yes')
        ->addNumberColumn('No')
        ->addRow( [$event->name, ((online_feedbacks::where([['yes_no',1],['event_id',$id]])->count())/$total_feedback)*100, ((online_feedbacks::where([['yes_no',0],['event_id',$id]])->count())/$total_feedback)*100] );
        Lava::ColumnChart('Yes_No', $options, ['title' => 'Want such event to be conducted again (in %) ?']);

        /* Overall rating */
        $overall  = Lava::DataTable();
        $overall->addStringColumn('Subject')
                ->addNumberColumn('Overall Rating')
                ->addRow(['Overall',online_feedbacks::where('event_id',$id)->avg('overall')]);
        Lava::BarChart('Overall', $overall, ['title'=>'Overall Rating of the Event']);
        /*Suggestions */
        $feedbacks=online_feedbacks::where('event_id',$id)->get();
        return view('feedback_result')->withEvent($event)->withFeedbacks($feedbacks);
    }
    public function getOfflineFeedbackResult($id)
    {
        $event=Event::where('id','=',$id)->first();
        $total_feedback=offline_feedbacks::where('event_id','=',$id)->count();
        /* Event heard from */
        $publicity = Lava::DataTable();
        $publicity->addStringColumn('reasons')
                 ->addNumberColumn('Percent')
                 ->addRow(['Posters', offline_feedbacks::where([['event_heard_from','poster'],['event_id',$id]])->count()])
                 ->addRow(['Class Orientation', offline_feedbacks::where([['event_heard_from','class_orientation'],['event_id',$id]])->count()])
                 ->addRow(['Friends', offline_feedbacks::where([['event_heard_from','friends'],['event_id',$id]])->count()])
                 ->addRow(['Seniors', offline_feedbacks::where([['event_heard_from','seniors'],['event_id',$id]])->count()])
                 ->addRow(['Others', offline_feedbacks::where([['event_heard_from','others'],['event_id',$id]])->count()]);
        Lava::PieChart('event_heard', $publicity, [
            'title'  => 'Publicity of Event',
            'is3D'   => true
            ]);

        /* Satisfactory level of event */
        $rates  = Lava::DataTable();
        $rates->addStringColumn('Subject')
                ->addNumberColumn('Rates')
                ->addRow(['Content',offline_feedbacks::where('event_id',$id)->avg('content')])
                ->addRow(['Presentation', offline_feedbacks::where('event_id',$id)->avg('presentation')])
                ->addRow(['Speaker',  offline_feedbacks::where('event_id',$id)->avg('speaker')])
                ->addRow(['Support Staff', offline_feedbacks::where('event_id',$id)->avg('support_staff')])
                ->addRow(['Organisation',   offline_feedbacks::where('event_id',$id)->avg('organisation')])
                ->addRow(['Location',   offline_feedbacks::where('event_id',$id)->avg('location')]);
        Lava::BarChart('Rates', $rates, [ 'title' => 'Part rating of event']);

        /* recommend to your friends */
        $recommend  = Lava::DataTable();
        $recommend->addStringColumn('Subject')
                ->addNumberColumn('How likely, would you recommend the event to your friends ?')
                ->addRow(['Recommend',offline_feedbacks::where('event_id',$id)->avg('recommend')]);
        Lava::BarChart('Recommend', $recommend, ['title'=>'How likely, would you recommend the event to your friends  ?']);

        /* Want such Event to be conducted */
        $options = Lava::DataTable();
        $options->addStringColumn('Event')
        ->addNumberColumn('Yes')
        ->addNumberColumn('No')
        ->addRow( [$event->name, ((offline_feedbacks::where([['yes_no',1],['event_id',$id]])->count())/$total_feedback)*100, ((online_feedbacks::where([['yes_no',0],['event_id',$id]])->count())/$total_feedback)*100] );
        Lava::ColumnChart('Yes_No', $options, ['title' => 'Want such event to be conducted again (in %) ?']);

        /* Overall rating */
        $overall  = Lava::DataTable();
        $overall->addStringColumn('Subject')
                ->addNumberColumn('Overall Rating')
                ->addRow(['Overall',offline_feedbacks::where('event_id',$id)->avg('overall')]);
        Lava::BarChart('Overall', $overall, ['title'=>'Overall Rating of the Event']);
        /*Suggestions */
        $feedbacks=offline_feedbacks::where('event_id',$id)->get();
        return view('feedback_result')->withEvent($event)->withFeedbacks($feedbacks);
    }
}

