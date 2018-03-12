<?php

namespace Application\Controller;

use Psr\Container\ContainerInterface;
use Application\Models\Audio;
use Application\Models\UrlRequest;

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
            $s_audio->banner = "https://cdn2.jazztimes.com/2015/05/spotify-logo-primary-horizontal-dark-background-rgb_0-800x361.jpg";
            $s_audio->channel =  "Ancient Astronauts";
            $s_audio->album =  $s_audio->description;
            unset($s_audio->id);
            unset($s_audio->req_id);
        }

        return $response->withJson([
            'status' => 1,
            'audio' => $audios[0]
        ]);

    }

    #Insert new anonyme request
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
}
