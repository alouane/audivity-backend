<?php

/**
 * @author: ALOUANE Nour-Eddine
 *
 * @version 0.1
 *
 * @email: alouane00@gmail.com
 * @date: 14/03/2018
 * @company: Audivity 
 * @country: Morocco 
 * Copyright (c) 2018-2019 Audivity
 */

namespace Application\Controller;

require_once 'Mail.php';

use Psr\Container\ContainerInterface;
use Application\Models\UrlRequest;
use Upwork\API\Client;
use Upwork\API\Routers\Hr\Jobs;
use \Mail;
use \PEAR;

class RegisterController extends BaseController
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;
    
        // constructor receives container instance
        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;
        }

    #Insert new anonyme request
    public function anonyme_url($request, $response, $args)
    {
        $body = $request->getParsedBody();
        #Init UrlRequest model
        $urlR = new UrlRequest();

        #Add new record to DB
        $insert_id = $urlR->new([
            'url' => $body['url'],
            'gender' => $body['gender'],
            'age' => $body['age'],
            'industry' => $body['industry'],
        ]);
        #Encode request ID
        $key = $urlR->EncodeID($insert_id);
        
        return $response->withJson([
            'status' => 1,
            'key' => $key
        ]);

    }

    #Update request infos => name & email
    public function register_profile_beta($request, $response, $args)
    {
        $body = $request->getParsedBody();
        #Init UrlRequest model
        $urlR = new UrlRequest();
        #Decode key
        $id = $urlR->DecodeKey($body['key']);
        #Add new record to DB
        $insert_id = $urlR->update_profile([
            'email' => $body['email'],
            'name' => $body['name'],
            'company' => $body['company'],
        ], $id);
        
        return $response->withJson([
            'status' => 1
        ]);

    }

     #Get ReqUrl
     public function get($request, $response, $args)
     {
         #Init Url
         $urlR = array();
 
         #Init UrlRequest model
         $urlR = new UrlRequest();
 
         #Get request key
         $rkey = $request->getParam('rkey');

         #Decode Key
         $id = $urlR->DecodeKey($rkey);
 
         #Get ReqUrl infos 
         $data = $urlR->find($id)->raw()[0];
 
         return $response->withJson([
             'status' => 1,
             'ReqUrl' => $data
         ]);
 
     }

    #Update request infos => name & email => Create upwork job
    public function register_profile($request, $response, $args)
    {
        $_SESSION['access_token'] = "322abbd63af198f66b64f23975affbcf";
        $_SESSION['access_secret'] = "9001823813df7be5";
        //Company reference id: ~~2ba4eb75832c4fe0
        //team ID: 0fskktj3j3u7rob_qah17q => 4500052
        
        // Requested key token: 22681f48face165c0c1bd440dd6dfc5b
        // Requested secret token: 1738b702dd899a62

        //Init upwork
        $config = new \Upwork\API\Config(
            array(
                'consumerKey'       => getenv('UPWORK_KEY'),  // SETUP YOUR CONSUMER KEY
                'consumerSecret'    => getenv('UPWORK_SECRET'),                  // SETUP YOUR KEY SECRET
                'accessToken'       => $_SESSION['access_token'],       // got access token
                'accessSecret'      => $_SESSION['access_secret'],      // got access secret
                'debug'             => false,                            // enables debug mode
            )
        );

        $client = new Client($config);

        //Authenticat upwork client
        $accessTokenInfo = $client->auth();

        $body = $request->getParsedBody();

        #Init UrlRequest model
        $urlR = new UrlRequest();

        #Decode key
        $id = $urlR->DecodeKey($body['key']);

        #Update url request record in DB
        $urlR->update_profile([
            'email' => $body['email'],
            'name' => $body['name'],
            'company_name' => $body['company']
        ], $id);

        #Get Request url infos
        $ReqUrl = $urlR->find($id)->raw()[0];

        //Post upwork job post
        $jobs = new Jobs($client);
        $params = array(
          "buyer_team__reference" => "4500052",
          "title" => "New ".$ReqUrl->company_name." Podcast - Narration",
          "job_type" => "hourly",
          "description" => "Audivity is offering an exclusive opportunity to narrate a ".$ReqUrl->company_name." articles. We are looking for an artist ".$ReqUrl->gender." between ".$ReqUrl->age." years of. \n \n Requirements/Suggestions: \n 1) Knows a thing or two about ".$ReqUrl->industry." industry \n 2) Experience in conveying info-heavy content (podcasts/ radio/ announcements/ voice-over) \n 3) Ability to do professional audio editing (cut, mix, clean up) - not required but desired. \n \n Your goal should be to convet this specific article ".$ReqUrl->url." in an engaging and informative way. We are looking for a creative way to make this informational audio content compelling. Share they key details and focus on driving to point home. Podcasting is an art so take it from here and feel free to find your own pace, tone. Do change article structure as you see fit so listeners digest the message and come back for more. \n \n We do not require/request you to submit a sample of your narration. However, you should know that most of our chosen voice over artists  (92%) send an edited 10-30 second narration with a custom audio bed. \n \n A sample audition should be specific to this article and does make a difference. If you decide to do so, upload your Mp3 (Mp3 only)  sample here https://audivity.com/UploadAudio/".$body['key'].". Please limit your application to a brief memo and a 10-30 edited audition with intro to the content and your name if you wish to do so. \n \n Depending on the success of this narration, you will be chosen to consistently narrate articles featured at ".$ReqUrl->company_name.". Stay tuned, Audivity has many more professional narration projects coming up exclusively through Upwork.",
          "visibility" => "invite-only", //invite-only
          "category2" => "Design & Creative",
          "subcategory2" => "Audio Production",
          "duration" => 30,
          "skills" => "audio-production;audio-editing"
        );

        $job = $jobs->postJob($params);
        // $job = $jobs->editJob("~01a1a14029ee358ae8", $params);

        // Check if job reference is ok
        if($job){
            #Update url request record in DB
            $urlR->update_profile([
                'job_reference' => $job->job->reference,
                "job_posted" => 1
            ], $id);
        }
        

        return $response->withJson([
            'status' => 1
        ]);
    }

    #Contact us endpoint => send email from support@audivity.com to edgomberg@gmail.com
    public function contact_us($request, $response, $args)
    {
        $body = $request->getParsedBody();

        #Init email
        $email = $body['email'];

        #Init header
        $headers = array (
            'From' => getenv("CONTACT"),
            'To' => [$email, getenv("SUPPORT")],
            'Subject' => "Query from ".$body['name']. ' ('.$email.')'
        );
    
        $smtpParams = array (
        'host' => getenv("SMPT_HOST"),
        'port' => getenv("SMTP_PORT"),
        'auth' => true,
        'username' => getenv("SMTP_USERNAME"),
        'password' => getenv("SMTP_PASSWORD")
        );

        // Create an SMTP client.
        $mail = Mail::factory('smtp', $smtpParams);
        
        #Init body
        $message .= $body['message'];

        
        // Send the email.
        $result = $mail->send($email, $headers, $message);

        if (PEAR::isError($result)) $status = "0: ".$result->getMessage();
        
        return $response->withJson([
            'status' => 1
        ]);

    }
}
