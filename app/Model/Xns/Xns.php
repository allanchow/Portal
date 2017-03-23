<?php

namespace App\Model\Xns;

use App\BaseModel;

class Xns extends BaseModel
{
    protected $table = 'xns';
    protected $fillable = ['domain_id', 'domain_name', 'api_key', 'secret_key'];
}
