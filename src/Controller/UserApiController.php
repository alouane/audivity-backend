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

use Psr\Container\ContainerInterface;
use Application\Models\User;

class UserApiController
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

    public function index($request, $response, $args)
    {
        $users = new User();
        $data = $users->all();
        if (empty($data)) {
            throw new \Exception('No users found', 404);
        }
        $user = (array)$this->container['user'];
        $this->container->logger->info(getenv('APP_ENV') . sprintf(' accessed by user "%s"', $user['username']));

        // Render index view
        return $response->withJson([
            'data' => $data,
        ]);
    }
}
