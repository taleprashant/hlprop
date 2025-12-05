<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

     public function sms($numbers,$msg)
    {
                   
    // $authKey = "228382ANs0nvxTx5b59d12e";
    $authKey = "238511AVkNouU05ba286dd";

    //Multiple mobiles numbers separated by comma
  
    //Sender ID,While using route4 sender id should be 6 characters long.
    $senderId = "RealEs";

    //Your message to send, Add URL encoding here.
  //  $message = urlencode("à¤®à¤¹à¤¾à¤•à¤‚à¤¨à¥‡à¤•à¥à¤Ÿ à¤‡-à¤¡à¤¿à¤°à¥‡à¤•à¥à¤Ÿà¤°à¥€ à¤®à¤§à¥à¤¯à¥‡ à¤†à¤ªà¤²à¥‡ à¤¸à¥à¤µà¤¾à¤—à¤¤ à¤†à¤¹à¥‡. à¤¾à¤¿.=à¤¤à¥€ à¤µ".$msg);

    //Define route
    $route = "4";
    //Prepare you post parameters
    $postData = array(
    'authkey' => $authKey,
    'mobiles' => $numbers,
    'message' => $msg,
    'sender' => $senderId,
    'route' => $route,
    'unicode' => 1
    );

    //API URL
    $url="http://vtermination.com/api/sendhttp.php";

    // init the resource
    $ch = curl_init();
    curl_setopt_array($ch, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData
    //,CURLOPT_FOLLOWLOCATION => true
    ));
    

    //Ignore SSL certificate verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


    //get response
        $output = curl_exec($ch);

    //Print error if any
    if(curl_errno($ch))
    {
    echo 'error:' . curl_error($ch);
    }

    curl_close($ch);

        return $output;
    }

    // public function sms($numbers,$msg)
    // {

    //     $curl = curl_init();
    //     $authKey = "236666AzaYupLif5b962739";

    //     //Multiple mobiles numbers separated by comma
      
    //     //Sender ID,While using route4 sender id should be 6 characters long.
    //     $senderId = "RealEs";


    //     $route = "4";
    //     //Prepare you post parameters
    //     $postData = array(
    //     'mobiles' => $numbers,
    //     'message' => $msg,
    //     'sender' => $senderId,
    //     'route' => $route,
    //     'unicode' => 1
    //     );

    //     curl_setopt_array($curl, array(
    //       CURLOPT_URL => "http://api.msg91.com/api/v2/sendsms?campaign=&response=&afterminutes=&schtime=&unicode=&flash=&message=&encrypt=&authkey=236666AzaYupLif5b962739&mobiles=&route=&sender=&country=91",
    //       CURLOPT_RETURNTRANSFER => true,
    //       CURLOPT_ENCODING => "",
    //       CURLOPT_MAXREDIRS => 10,
    //       CURLOPT_TIMEOUT => 30,
    //       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //       CURLOPT_CUSTOMREQUEST => "POST",
    //       CURLOPT_POSTFIELDS => json_encode($postData),
    //       CURLOPT_SSL_VERIFYHOST => 0,
    //       CURLOPT_SSL_VERIFYPEER => 0,
    //       CURLOPT_HTTPHEADER => array(
    //         "authkey: ",
    //         "content-type: application/json"
    //       ),
    //     ));

    //     $response = curl_exec($curl);
    //     $err = curl_error($curl);

    //     curl_close($curl);

    //     if ($err) {
    //       echo "cURL Error #:" . $err;
    //     } else {
    //       echo $response;
    //     }
    // }
}
