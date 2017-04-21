<?php

namespace App\Model\Cdn;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class NgxAccessCdn extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'ngx-access-cdn';
}
