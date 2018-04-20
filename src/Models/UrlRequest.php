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
 * The UrlRequest class
 */
class UrlRequest extends BaseModel
{
    protected $table = 'url_requests';
    protected $primary_key = 'id';

    public function new($params)
    {
        #Insert new url & retrieve request ID
        $insert_id = $this->insert($params);
        return $insert_id;
    }

    public function update_profile($params, $id)
    {
        #Insert new url & retrieve request ID
        return $this->update($params, ['id' => $id]);
        
    }
}
