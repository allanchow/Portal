<?php

namespace App\Model\Cdn;

use App\BaseModel;

class CdnSSL extends BaseModel
{
    protected $table = 'cdn_ssl';
    protected $fillable = ['resource_id', 'type', 'cert', 'key', 'status'];
}
