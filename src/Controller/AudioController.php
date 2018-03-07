<?php

namespace Application\Controller;

use Psr\Container\ContainerInterface;
use Application\Models\Audio;

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

        #Add new record to DB
        $insert_id = $urlR->update_profile([
            'preferred_audio' => $selected_audio
        ], $req_id);
        
        return $response->withJson([
            'status' => 1
        ]);

    }
}
