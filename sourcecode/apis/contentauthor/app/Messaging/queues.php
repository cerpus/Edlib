<?php

use Vinelab\Bowler\Facades\Registrator;

Registrator::subscriber('edlib_gdpr_delete_request-contentauthor', 'App\Messaging\Handlers\EdlibGdprDeleteRequest', ['*'], 'edlib_gdpr_delete_request', 'fanout');
