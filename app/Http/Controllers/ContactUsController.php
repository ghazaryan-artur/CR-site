<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
//use Illuminate\Support\Facades\Mail;

use App\Mail\Mail; // coming soon
use App\Mail\TestEmail;
use Session;


class ContactUsController extends Controller
{
    private $pageTitle = 'Software Development Company - Contact Us | CodeRiders';
    private $pageMetaDescription = 'Let\'s talk about your business needs on custom software development, web development and design, software outsourcing, IT consulting, BI solution, CRM, etc.';



    public function index() {
        $response['title'] = $this->pageTitle;
        $response['description'] = $this->pageMetaDescription;
        $response['target_blog']['image']['full_url'] = '/public/images/fbshare/contactus.jpg';

        return view('contactUs', $response);
    }

    public function send(Request $request) {
        $caller = $request->server('HTTP_REFERER');
        if(!$caller) {
            return redirect('');
        }
        $is_home = !Str::contains($caller, 'contact-us');
        if($is_home) {
            $caller = '/#home_form';
        }



        /*------------------------------------- Required fields formats check ----------------------------------------*/

        $request->validate([
            'name' => 'required | max:100',
            'email' => 'required | email | max:100',
            'company' => 'required | max:100',
            'message' => 'required | max:500',
            'phone' => 'regex:/[0-9+\-()]{8,}/',
            'attachment' => "file | mimes:jpg,jpeg,png,gif,pdf,doc,docx,ppt,pptx,txt,xls,xlsx,xlxs|max:10240",
            'g-recaptcha-response' => 'required | captcha'
        ]);

        $message = array();
        $message['name']     = $request->name;
        $message['email']    = $request->email;
        $message['company']  = $request->company;
        $message['message']  = $request->message;
        $message['phone']    = $request->phone;
        $message['jobTitle'] = $request->jobTitle;



        /*----------------------------------- File attachment check and upload ---------------------------------------*/
        if($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('images');
        }

        /* --- */
// coming soon
//        $subscriber = new Mail;
//        $subscriber->subscribe($email, 'ContactList');

        /* ----  */

        $mesConfig = array();
        $mesConfig['$to']           =  "info@coderiders.am";
        $mesConfig['$subject']      =  "Coderiders Support System";
        $mesConfig['message_html'] =  $this->buildHtmlTemplate( $message, $mesConfig['$subject'] );
        $from         =  "info@coderiders.am";



// I stoped here



        $mail = new Mail; // coming soon
        $send = $mail->sendSendgridContact($to, $message_html, $path);

        if($send) {
            $support = new Support;
            $add_new = $support->add($insert_array);
            if($add_new) {
                if($is_home) {
                    $_SESSION['contact_error'] = "success";
                } else {
                    $_SESSION['contact']['contact_error'] = "success";
                }
            } else {
                if($is_home) {
                    $_SESSION['contact_error'] = "Oops, something went wrong! Please try again.";
                } else {
                    $message = $_POST['message'];
                    $_SESSION['contact']['contact_error'] = "Oops, something went wrong, please try again";
                    $this->buildResponse($name, $email, $company, $message, $phone, $jobTitle);
                }
            }
        } else {
            if($is_home) {
                $_SESSION['contact_error'] = "Oops, something went wrong! Please try again.";
            } else {
                $message = $_POST['message'];
                $_SESSION['contact']['contact_error'] = "Oops, something went wrong, please try again";
                $this->buildResponse($name, $email, $company, $message, $phone, $jobTitle);
            }
        }

        header('Location: '.$caller);
        die;
    }

    public function subscriber() {
        $email = trim($_POST['email']);
        $token = trim($_POST['token']);

        if(empty($email) || empty($token)) {
            echo "error";
            die;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "email error";
            die;
        }

        $subscribe      = new Mail;
        $add_subscriber = $subscribe->subscribe($email);

        if($add_subscriber !== false) {

            $subscribe      = new Subscribe;
            $new_subscriber = $subscribe->add($email, $add_subscriber);

            if($new_subscriber === true) {
                echo "success";
                die;
            } else if($new_subscriber == "duplicate") {
                echo "exist";
                die;
            }else if($new_subscriber === false){
                echo "error";
                die;
            }
        } else {
            echo "error";
            die;
        }
    }

    public function buildResponse($name, $email, $company, $message, $phone, $jobTitle) {
        $_SESSION['contact']['name']     = $name;
        $_SESSION['contact']['email']    = $email;
        $_SESSION['contact']['company']  = $company;
        $_SESSION['contact']['message']  = $message;
        $_SESSION['contact']['phone']    = $phone;
        $_SESSION['contact']['jobTitle'] = $jobTitle;
    }


    public function buildHtmlTemplate($message, $title, $name, $email, $company, $phone, $jobTitle) {
        $phone_html = '';
        if($phone != ''){
            $phone_html = '<p> Phone: '.$phone.'</p>';
        }
        $job_title_html = '';
        if($jobTitle!= ''){
            $job_title_html = '<p> Job Title: '.$jobTitle.'</p>';
        }
        $template = "
            <html>
                <head>
                    <title>".$title."</title>
                </head>
                <body>
					<p> Sender IP: ".$_SERVER['REMOTE_ADDR']."</p>
                    <p> Name: ".$name."</p>
                    <p> Email address: ".$email."</p>
                    <p> Company name: ".$company."</p>
                    ".$phone_html ."
                    ".$job_title_html."
                    <p> Message: ".$message."</p>
                </body>
            </html>";

        return $template;
    }
}
