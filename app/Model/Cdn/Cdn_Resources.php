<?php

namespace App\Model\Cdn;

use App\BaseModel;

class Cdn_Resources extends BaseModel
{
    protected $table = 'cdn_resources';
    protected $fillable = ['org_id', 'cdn_hostname', 'origin', 'host_header', 'max_age', 'file_type', 'cname', 'status', 'update_status', 'force_update', 'error_msg'];

    protected $cdn_domain = 'allcdn888.com';
    protected $exclude_domain = 'allbrightnetwork.com';
    protected $default_file_type = ["jpg", "jpeg", "png", "bmp", "gif", "html", "htm", "xml", "js", "css", "pdf", "swf", "ico", "wav"];
    protected $default_max_age = 1800;

    public function validate_origin($ar_origin)
    {
        foreach ($ar_origin as $origin){
            if (filter_var($origin['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false;
            }
        }
        return true;
    }

    public function validate_hostname($hostname)
    {
        return preg_match('/^([^-*]|\*\.)((?!\.-|-\.|'.$this->cdn_domain.'|'.$this->exclude_domain.')[a-zA-Z0-9\-\.])*(\.\*|)$/', $hostname);
    }

    public function get_cdn_domain()
    {
         return $this->cdn_domain;
    }

    public function get_default_file_type()
    {
         return $this->default_file_type;
    }


    public function get_default_max_age()
    {
         return $this->default_max_age;
    }
}
