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

use Slim\Http\Stream;

class StreamFileController extends BaseController
{
    public function handle($request, $response, $args)
    {
        $path = $this->container->get('settings')['public_path'] . '/files/empty.txt'; // a 100mb file
        $fh = fopen($path, 'rb');
        $file_stream = new Stream($fh);
        return $response->withBody($file_stream)
            ->withHeader('Content-Disposition', 'attachment; filename=empty.txt;')
            ->withHeader('Content-Type', mime_content_type($path))
            ->withHeader('Content-Length', filesize($path));
    }
}
