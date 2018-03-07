<?php

namespace Application\Controller;

use Psr\Container\ContainerInterface;
use Application\Models\UrlRequest;

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
        ]);
        #Encode request ID
        $key = $urlR->EncodeID($insert_id);
        
        return $response->withJson([
            'status' => 1,
            'key' => $key
        ]);

    }

    #Update request infos => name & email
    public function register_profile($request, $response, $args)
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
        ], $id);
        
        return $response->withJson([
            'status' => 1
        ]);

    }
}
