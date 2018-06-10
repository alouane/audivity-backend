<?php

/**
 * @author: ALOUANE Nour-Eddine/Steven Horkey
 *
 * @version 0.1
 *
 * @email: alouane00@gmail.com
 * @date: 14/03/2018
 * @company: Audivity 
 * @country: Morocco/USA 
 * Copyright (c) 2018-2019 Audivity
 */

namespace Application\Controller;

require_once 'Mail.php';

use Psr\Container\ContainerInterface;
use Application\Models\UrlRequest;
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

    

    #Submit Scripts endpoint => send email from support@audivity.com to edgomberg@gmail.com
    // NOTE: THIS IS CURRENTLY NOT WORKING
    public function submit_scripts($request, $response, $args)
    {
        $body = $request->getParsedBody();

        $email = "stevenhorkey@gmail.com";

        #Init email
        // $email = $body['email'];
        $from = "support@audivity.com";
        $subject = "Finalized Scripts!";

        #Init header
        $headers = array (
            'From' => $from,
            'To' => $to,
            'Subject' => $subject
        );
    
        $smtpParams = array (
            'host' => 'email-smtp.us-east-1.amazonaws.com',
            'port' => '465',
            'auth' => true,
            'username' => 'AKIAJBCW3CCXQPAEG26A',
            'password' => 'Al6Vh0MuGGkrz2WRjXoIlZPDonZpu9a5jYmWGs8L1scc'
        );

        // Create an SMTP client.
        $mail = Mail::factory('smtp', $smtpParams);
        
        #Init body
        $message = 'testing';

        // Send the email.
        $result = $mail->send($email, $headers, $message);

        if (PEAR::isError($result)) $status = "0: ".$result->getMessage();
        
        return $response->withJson([
            'status' => 1
        ]);
    }
    
}
