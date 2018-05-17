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
use Application\Models\Audio;
use Application\Models\UrlRequest;
use \Mail;
use \PEAR;

class AudioController extends BaseController
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

     #Upload an audio file
     public function upload($request, $response, $args)
     {
         #Init MIN_AUDIOS
         $MIN_AUDIOS = 5;
         
         #Init UrlRequest model
         $audio = new Audio();
         $urlR = new UrlRequest();
         $utils = new UtilsController();

         #Init body
         $body = $request->getParsedBody();

         if (!isset($_FILES['file'])) {
            $status = 0;
        }
        else{

            #Decode key
            $id = $audio->DecodeKey($body['key']);

            #Add new record to DB
            $insert_id = $audio->new([
                // 'title' => $body['title'],
                'description' => $body['description'],
                'theme' => "",
                'applicant_name' => $body['name'],
                'req_id' => $id
            ]);

             //Encode ID
             $insert_id = $audio->EncodeID($insert_id);

             //get uploaded image
             $temp_image = $_FILES['file']['tmp_name']; // full size image
             $file_name = $_FILES['file']['name'];
             $ext = pathinfo($file_name, PATHINFO_EXTENSION);
 
             //get random file name
             $origine_file = getenv("TEMP_AUDIO_FILES").$insert_id.".mp3";
 
             //move the file to temp folder
             move_uploaded_file($temp_image, $origine_file);
 
             #check if file exist
             if(is_file($origine_file)){
                 #Chmod audio
                 chmod($origine_file, 0777);
                 //send to s3
                 $utils->copytos3($origine_file, getenv("S3_AUDIO_PATH").$insert_id.".mp3");

                 unlink($origine_file);
              }

            $status = 1;

            #Check if samples number is higher than MIN_AUDIOS
            $audios = $audio->findByReqID($id)->raw();
            
            #Get request url data
            $request_data = $urlR->find($id)->raw()[0];

            if(sizeof($audios) >= $MIN_AUDIOS && $request_data->sample_email_sent != 1){
                #We need to send email to the client, saying that the samples are ready
                #Get client's email & name
                $email = $request_data->email;
                $name =  $request_data->name;

                #Send email
                $headers = array (
                    'From' => getenv("CONTACT"),
                    'To' => $email,
                    'Subject' => "Audivity audio samples are ready!"
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
                $message .= "Dear $name,\n \n \n";
                $message.= "We have professionally narrated you blog, open this link to listen to the fresh samples: \n \n \n";
                $message.= "https://audivity.com/audioSamples/".$body['key'];
                $message.= "\n \n \n";
                $message.= "Regards,\n";
                $message.= "The Audivity Team \n";
                $message.= "audivity.com \n";
                
                // Send the email.
                $result = $mail->send($email, $headers, $message);
        
                if (PEAR::isError($result)) $status = "0: ".$result->getMessage();

                #Update sample email sent status
                $urlR->update_profile([
                    'sample_email_sent' => 1
                ], $id);
            }
        }

         return $response->withJson([
             'status' => $status
         ]);
 
     }

    #Get audio => it could be a playlist too
    public function get($request, $response, $args)
    {
        #Init audios
        $audios = array();

        #Init UrlRequest model
        $audio = new Audio();

        #Get audio key
        $akey = $request->getParam('akey');
        #Decode Key
        $id = $audio->DecodeKey($akey);

        #Get list of audio samples
        $audios = $audio->find($id)->raw();

         #Parse array & encode ids
         foreach ($audios as &$s_audio) {
            $s_audio->akey = $audio->EncodeID($s_audio->id);
            $s_audio->url = getenv('CDN').getenv('AUDIO_PATH').$s_audio->akey.".mp3";
            $s_audio->banner = "https://embed.audivity.com/img/logo-audivity.jpg";
            $s_audio->channel =  $s_audio->theme;
            $s_audio->album =  $s_audio->description;
            unset($s_audio->id);
            unset($s_audio->req_id);
        }

        return $response->withJson([
            'status' => 1,
            'audio' => $audios[0]
        ]);

    }

    #Get audio samples
    public function sample_audios($request, $response, $args)
    {
        #Init audios
        $audios = array();

        #Init UrlRequest model
        $audio = new Audio();

        #Get request key
        $rkey = $request->getParam('rkey');
        #Decode Key
        $id = $audio->DecodeKey($rkey);

        #Get list of audio samples
        $audios = $audio->findByReqID($id)->raw();

         #Parse array & encode ids
         foreach ($audios as &$s_audio) {
            $s_audio->akey = $audio->EncodeID($s_audio->id);
            unset($s_audio->id);
            unset($s_audio->req_id);
        }

        return $response->withJson([
            'status' => 1,
            'players' => $audios
        ]);

    }

    #Select preferd audio
    public function preferd_audio_sample($request, $response, $args)
    {
        #Init UrlRequest model
        $urlR = new UrlRequest();

        #Get request rkey & selected_audio
        $rkey = $request->getParam('rkey');
        $selected_audio = $request->getParam('selected_audio');

        #Decode rKey & selected audio key
        $req_id = $urlR->DecodeKey($rkey);
        $selected_audio = $urlR->DecodeKey($selected_audio);

        #Update profile
        $urlR->update_profile([
            'preferred_audio' => $selected_audio
        ], $req_id);
        
        return $response->withJson([
            'status' => 1
        ]);

    }

    #Select user dissatisfaction
    public function dissatisfaction($request, $response, $args)
    {
        #Init UrlRequest model
        $urlR = new UrlRequest();
        
        $body = $request->getParsedBody();

        #Get request rkey, goal & interests
        $rkey = $body['rkey'];
        $goal = $body['goal'];
        $interests = $body['interests'];

        #Decode rKey & selected audio key
        $req_id = $urlR->DecodeKey($rkey);

        #Update profile
        $urlR->update_profile([
            'dissatisfaction_goal' => $goal,
            'dissatisfaction_interests' => $interests,
        ], $req_id);
        
        return $response->withJson([
            'status' => 1
        ]);

    }

    #User join action
    public function join($request, $response, $args)
    {
        #Init UrlRequest model
        $urlR = new UrlRequest();

        #Get request
        $rkey = $request->getParam('rkey');

        #Decode rKey
        $req_id = $urlR->DecodeKey($rkey);

        #Update profile
        $urlR->update_profile([
            'join_action' => 1
        ], $req_id);
        
        return $response->withJson([
            'status' => 1
        ]);

    }

    #Encode id
    public function encode($request, $response, $args)
    {
        #Init UrlRequest model
        $urlR = new UrlRequest();

        #Get id
        $id = $request->getParam('id');

        #Encode id
        $ukey = $urlR->EncodeID($id);

        
        return $response->withJson([
            'ukey' => $ukey
        ]);

    }

    #Decode id
    public function decode($request, $response, $args)
    {
        #Init UrlRequest model
        $urlR = new UrlRequest();

        #Get ukey
        $akey = $request->getParam('akey');

        #Decode ukey
        $id = $urlR->DecodeKey($akey);

        
        return $response->withJson([
            'id' => $id
        ]);

    }
}
