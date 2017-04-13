<?php

namespace App\Model\Cdn;

use App\BaseModel;

class Cdn_Resources extends BaseModel
{
    protected $table = 'cdn_resources';
    protected $fillable = ['org_id', 'cdn_hostname', 'origin', 'host_header', 'max_age', 'file_type', 'cname', 'status', 'update_status', 'force_update', 'error_msg'];

    protected $cdn_domain = 'allcdn888.com';
    protected $exclude_domain = 'allbrightnetwork.com';
    protected $default_file_type = ["jpg", "jpeg", "png", "bmp", "gif", "html", "htm", "xml", "js", "css", "pdf", "swf", "ico", "wav", "txt"];
    protected $default_max_age = 1800;

    public function validate_origin($ar_origin)
    {
        foreach ($ar_origin as $origin){
            if (! isset($origin['ip'])) {
                return false;
            }
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

    public function validate_host_header($hostname)
    {
        return preg_match('/^[a-zA-Z0-9\-\.]+$/', $hostname);
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

    public function getHostFromCName()
    {
        preg_match('/^(.*)\.'.$this->get_cdn_domain().'$/', $this->cname, $matches);
        return isset($matches[1]) ? $matches[1] : false;
    }

    public function suspend_cdn_hostname()
    {
        $this->cdn_hostname = 'x-'.$this->id.'-'.$this->cdn_hostname;
    }

    public function revert_suspend_cdn_hostname()
    {
        $this->cdn_hostname = str_replace('x-'.$this->id.'-', '', $this->cdn_hostname);
    }

    public function file_type_to_string()
    {
        $this->file_type = implode(',', json_decode($this->file_type, true));
    }

    public function createCName()
    {
        if (\App::environment('production')) {
            $int_id = str_pad($this->id, 6, "0", STR_PAD_LEFT);
            $this->cname = "cdn-{$int_id}.{$this->get_cdn_domain()}";
        } else {
            if ($this->id > 100000) {
                $int_id = $this->id;
            } else {
                $int_id = 900000 + $this->id;
            }
            $this->cname = "uat-cdn-{$int_id}.{$this->get_cdn_domain()}";
        }
    }
}
