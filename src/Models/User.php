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

namespace Application\Models;

use Latitude\QueryBuilder\Conditions;

/**
 * The user class
 */
class User extends BaseModel
{
    protected $table = 'users';
    protected $primary_key = 'id';
    protected $hidden = ['password'];

    public function findByEmail(string $email)
    {
        return $this->select()
            ->where(Conditions::make('email = ?', $email));
    }
}
