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
use Latitude\QueryBuilder\InsertQuery;

/**
 * The Audio class
 */
class Audio extends BaseModel
{
    protected $table = 'audio';
    protected $primary_key = 'id';

    public function new($params)
    {
        #Insert new audio & fetch audio ID
        $insert_id = $this->insert($params);
        return $insert_id;
    }

    public function findByReqID(int $id)
    {
        return $this->select()
            ->where(Conditions::make('req_id = ?', $id));
    }

}
