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

use Firebase\JWT\JWT;
use Application\Models\User;

class LoginController extends BaseController
{
    public function handle($request, $response, $args)
    {
        $body = $request->getParsedBody();

        $users = new User();
        $user = $users->findByEmail($body['email'])->raw();

        if (empty($user[0])) {
            throw new \Exception('No users found', 404);
        }

        if (password_verify($body['password'], $user[0]->password)) {
            $now = new \DateTime();
            $future = new \DateTime('now +2 hours');

            $payload = [
                'iat' => $now->getTimeStamp(),
                'exp' => $future->getTimeStamp(),
                'sub' => $body['email'],
                'scope' => ['*'],
            ];
            $secret = getenv('JWT_SECRET');
            if (empty($secret)) {
                throw new \Exception('No JWT_SECRET set', 500);
            }

            $token = JWT::encode($payload, $secret, 'HS256');

            return $response->withJson([
                'token' => $token,
                'issued' => $now->getTimestamp(),
                'expires' => $future->getTimestamp(),
            ]);
        } else {
            throw new \Exception('Invalid password', 403);
        }
    }
}
