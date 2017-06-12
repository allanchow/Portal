<?php

namespace App\Http\Controllers\Cdn;

// controllers
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Xns\XnsController;
use App\Http\Controllers\Controller;
// models
use App\Model\Cdn\Cdn_Resources;
use App\Model\Cdn\CdnSSL;
// classes
use Crypt;
/**
 * CdnReportController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class CdnScheduleController extends Controller
{
    protected $cmd_path = '/usr/local/share/dehydrated/';
    //cd /usr/local/share/dehydrated/;./dehydrated -c -d ssl.allcdn888.com
    /**
     * Create a new controller instance.
     *
     * @return type void
     */
    public function __construct(PhpMailController $PhpMailController)
    {
        $this->PhpMailController = $PhpMailController;
    }

    public function genAutoSSL()
    {
        ini_set('max_execution_time', 1800);
        set_error_handler(null);
        set_exception_handler(null);
        $expire_date = date('Y-m-d H:i:s', strtotime('+3 weeks'));
        $ts = time();
        if ($ssl_list = CdnSSL::where('type', 'A')->where('expire_date', '<', $expire_date)->get()){
            foreach ($ssl_list as $ssl) {
                if ($resource = Cdn_Resources::where('id', $ssl->resource_id)->where('http', '>', 0)->first()){
                    if ($resource->verifyDNS()){
                        `cd {$this->cmd_path};./dehydrated -c -d {$resource->cdn_hostname} >> /tmp/d_log 2>&1`;
                        $cert_file = "{$this->cmd_path}certs/{$resource->cdn_hostname}/fullchain.pem";
                        $key_file = "{$this->cmd_path}certs/{$resource->cdn_hostname}/privkey.pem";
                        if (is_file($cert_file)) {
                            $cert = file_get_contents($cert_file);
                            $key = file_get_contents($key_file);
                            $cert_data = openssl_x509_parse($cert);
                            $expire_date = date('Y-m-d H:i:s', $cert_data['validTo_time_t']);
                            if ($cert_data['validTo_time_t'] > $ts && $ssl->expire_dat != $expire_date) {
                                $ssl->expire_date = $expire_date;
                                $ssl->cert = Crypt::encrypt($cert);
                                $ssl->key = Crypt::encrypt($key);
                                $ssl->status = 2;
                                if ($ssl->save())
                                {
                                    $resource->force_update = 1;
                                    $resource->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function checkXNS()
    {
        ini_set('max_execution_time', 1800);
        set_error_handler(null);
        set_exception_handler(null);
        if ($resources = Cdn_Resources::whereNull('xns_host_id')->where('status', '>', 0)->get()){
            $xns = new XnsController();
            if ($host_list = $xns->getHostList()) {
                foreach ($resources as $resource) {
                    $host = $resource->getHostFromCName();
                    if (array_key_exists($host, $host_list)){
                        $host_id = $host_list[$host]['id'];
                        $resource->xns_host_id = $host_id;
                        $resource->save();
                    } else {
                        $xns->addResourceCNameGroup($resource);
                    }
                }
            }
        }
    }
}
