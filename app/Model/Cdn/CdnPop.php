<?php

namespace App\Model\Cdn;

use App\BaseModel;

class CdnPop extends BaseModel
{
    protected $table = 'cdn_pop';
    protected $fillable = ['pop_hostname', 'ip', 'deployment_ips', 'status', 'dns_updated_at'];
    protected $primaryKey = 'pop_hostname';
    public $incrementing = false;

    public function get_prefix()
    {
        if (\App::environment('production')) {
            $prefix = 'cdn-';
        } else {
        	$prefix = 'uat-cdn-';
        }
        return $prefix;  	
    }

    public function validate_hostname($hostname = null)
    {
    	if (is_null($hostname)) {
    		$hostname = $this->pop_hostname;
    	}
        $prefix = $this->get_prefix();
    	return preg_match('/^'.$prefix.'g\d+-\w{2}-\d+$/', $hostname);
    }

    public function get_pop_group($hostname = null)
    {
    	if (is_null($hostname)) {
    		$hostname = $this->pop_hostname;
    	}
        $prefix = $this->get_prefix();
    	return preg_replace('/^('.$prefix.'g\d+)-\w{2}-\d+$/', '\1-pop', $hostname);
    }

    public function get_resource_pop_group($group)
    {
    	$prefix = $this->get_prefix();
    	return $prefix."g{$group}-pop";
    }

    public function get_group($hostname = null)
    {
    	if (is_null($hostname)) {
    		$hostname = $this->pop_hostname;
    	}
        $prefix = $this->get_prefix();
    	return preg_replace('/^('.$prefix.'g\d+)-\w{2}-\d+$/', '\1', $hostname);
    }

    public function get_resource_group($group)
    {
    	$prefix = $this->get_prefix();
    	return $prefix."g{$group}";
    }

    public function get_region($hostname = null)
    {
    	if (is_null($hostname)) {
    		$hostname = $this->pop_hostname;
    	}
        $prefix = $this->get_prefix();
    	return preg_replace('/^'.$prefix.'g\d+-(\w{2})-\d+$/', '\1', $hostname);
    }

    public function is_ddos_pop($hostname = null)
    {
    	if (is_null($hostname)) {
    		$hostname = $this->pop_hostname;
    	}
        $prefix = $this->get_prefix();
    	return preg_match('/^('.$prefix.'g\d+)-us-\d+$/', $hostname);
    }

    public function get_ddos_pop_group()
    {
    	return $this->get_prefix().'ddos-pop';
    }
}
