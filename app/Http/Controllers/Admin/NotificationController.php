<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use JD\Cloudder\Facades\Cloudder;
use Illuminate\Support\Facades\DB;
use App\Helpers\APIHelpers;
use App\Notification;
use App\UserNotification;
use App\User;
use App\Visitor;

class NotificationController extends AdminController{
    
    // get all notifications
    public function show(){
        $data['notifications'] = Notification::orderBy('id' , 'desc')->get();
        return view('admin.notifications' , ['data' => $data]);
    }

    // get notification details
    public function details(Request $request){
        $data['notification'] = Notification::find($request->id);
        return view('admin.notification_details' , ['data' => $data]);
    }

    // delete notification
    public function delete(Request $request){
        $notification = Notification::find($request->id);
        if($notification){
            $notification->delete();
        }
        return redirect('admin-panel/notifications/show');
    }

    // type : get - get send notification page
    public function getsend(){
        return view('admin.notification_form');
    }

    // send notification and insert it in database
    public function send(Request $request){
        $notification = new Notification();
        if($request->file('image')){
            $image_name = $request->file('image')->getRealPath();
            Cloudder::upload($image_name, null);
            $imagereturned = Cloudder::getResult();
            $image_id = $imagereturned['public_id'];
            $image_format = $imagereturned['format'];    
            $image_new_name = $image_id.'.'.$image_format;
            $notification->image = $image_new_name;
        }else{
            $notification->image = null;
        }

        $notification->title = $request->title;
        $notification->body = $request->body;
        $notification->save();

        $users = Visitor::select('id','fcm_token', 'user_id')->where('fcm_token' ,'!=' , null)->get();
        for($i =0; $i < count($users); $i++){
            $fcm_tokens[$i] = $users[$i]['fcm_token'];
            $user_notification = new UserNotification();
            $user_notification->visitor_id = $users[$i]['id'];
            $user_notification->user_id = $users[$i]['user_id'];
            $user_notification->notification_id = $notification->id;
            $user_notification->save();            
        }
		
		$the_image = "https://res.cloudinary.com/dzqo7w4cp/image/upload/w_200,q_100/v1600733461/".$notification->image;

        $notificationss = APIHelpers::send_notification($notification->title , $notification->body , $the_image , null , $fcm_tokens); 
          
        return redirect('admin-panel/notifications/show');
    }



    // resend notifications 
    public function resend(Request $request){
        $notification_id = $request->id;
        $notification = Notification::find($notification_id);

		$users = Visitor::select('id','fcm_token', 'user_id')->where('fcm_token' ,'!=' , null)->get();
        for($i =0; $i < count($users); $i++){
            $fcm_tokens[$i] = $users[$i]['fcm_token'];
            $user_notification = new UserNotification();
            $user_notification->visitor_id = $users[$i]['id'];
            $user_notification->user_id = $users[$i]['user_id'];
            $user_notification->notification_id = $notification->id;
            $user_notification->save();            
        }
        $users_tokens = Visitor::where('fcm_token' ,'!=' , null)->select('fcm_token')->pluck('fcm_token');
        
        $nots = APIHelpers::send_notification($notification->title , $notification->body , $notification->image , null , $users_tokens);
        
        return redirect()->back()->with('status', 'Sent succesfully');
    }

}