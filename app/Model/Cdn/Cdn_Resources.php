<?php

namespace App\Model\Cdn;

use App\BaseModel;

class Cdn_Resources extends BaseModel
{
    protected $table = 'cdn_resources';
    protected $fillable = ['org_id', 'cdn_hostname', 'origin', 'host_header', 'max_age', 'file_type', 'cname', 'status', 'update_status', 'force_update', 'error_msg'];

    public function validate_origin($ar_origin)
    {
        foreach ($ar_origin as $origin){
            if (filter_var($origin['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false;
            }
        }
        return true;
    }
}
