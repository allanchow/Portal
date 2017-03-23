<?php

namespace App\Http\Controllers\Xns;

require_once base_path('vendor/cloudxns/cloud-xns-api-sdk-php/vendor/autoload.php');

// controllers
use App\Http\Controllers\Controller;
// model
use App\Model\Xns\Xns;
use App\Model\Cdn\Cdn_Resources;
// classes
use Auth;
use Datatables;
use DateTime;
use DB;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Input;
use Lang;
use Redirect;
use CloudXNS\Api;

/**
 * CdnController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class XnsController extends Controller
{
    protected $xns_api;
    protected $domain_id;
    protected $host_list;

    protected $line_list = array(
                                 'default' => 1,
                                 'oth' => 9,
                                 'asia' => 31,
                                 'jp' => 71,
                                 'kr' => 73,
                                 'sg' => 123
    );
    protected $default_cdn = array(
                                   'default' => 'hk',
                                   'oth' => 'us',
                                   'asia' => 'hk',
                                   'jp' => 'jp',
                                   'kr' => 'kr',
                                   'sg' => 'sg'
    );


    private $per_page = 10;
    private $ttl = 60;
    /**
     * Create a new controller instance.
     *
     * @return type void
     */
    public function __construct()
    {
        // checking authentication
        $this->middleware('auth');
        // checking if role is agent
        //$this->middleware('role.agent');
        $this->xns_api = new Api();
        $this->initXNS();
    }

    protected function initXNS($domain_name = '')
    {
        if ($domain_name == '') {
            $cdn_resources = new Cdn_Resources();
            $domain_name = $cdn_resources->get_cdn_domain();
        }
        $xns = new XNS();
        $xns_data = $xns->where('domain_name', $domain_name)->first();
        $this->xns_api->setApiKey($xns_data->api_key);
        $this->xns_api->setSecretKey($xns_data->secret_key);
        $this->xns_api->setProtocol(true);
        $this->domain_id = $xns_data->domain_id;
    }

    public function getHostList()
    {
        $this->host_list = $xns_list = array();
        $offset = 0;
        do
        {
            $rs = json_decode($this->xns_api->host->hostList($this->domain_id, $offset, $this->per_page), true);
            if ($xns_list = $rs['hosts'])
            {
                foreach ($xns_list as $host)
                {
                    $this->host_list[$host['host']] = $host;
                }
            }
            $offset += $this->per_page;
        } while(!is_null($xns_list) && count($xns_list) == $this->per_page);
        return $this->host_list;
    }

    public function importHost($id = null)
    {
        $ar_host = array();
        $host_list = $this->getHostList();
        foreach ($host_list as $host=>$host_data) {
            $hostname = rtrim($host_data['domain_name'], '.');
            $ar_host[$hostname] = $host_data['id'];
        }
        if ($id) {
            $resources = Cdn_Resources::where('id', $id)->get();
        } else {
            $resources = Cdn_Resources::get();
        }
        try {
            $host_id_list = array();
            $result = true;
            foreach ($resources as $resource) {
                if (isset($ar_host[$resource['cname']])){
                    $resource->xns_host_id = $ar_host[$resource['cname']];
                    if ($result = $resource->save()) {
                        $host_id_list[$resource->id] = $resource->xns_host_id;
                    }
                }
            }
            return response()->json(compact('result', 'host_id_list'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $result = false;
            return response()->json(compact('result', 'error'));
        }
    }

    public function test()
    {
        //dd($this->getHostList());
        //dd($this->delResourceCName(900022));
        dd($this->xns_api->domain->domainList());
        //$rs = $this->importHost();
        //$rs = $rs->getData(true);
        //var_dump($rs);
        //dd($this->revertResourceDNS(900029));
        //$rs = $rs->getData();
        //dd($this->revertResourceDNS(900029));
    }

    public function addRecord($host, $type, $value, $line = 'default', $mx = 1)
    {
        try {
            $rs = json_decode($this->xns_api->record->recordAdd($this->domain_id, $host, $value, $type, 1, $this->ttl, $this->line_list[$line]), true);
            if ($rs['code'] == 1) {
                $result = true;
                return response()->json(compact('result'));
            } else {
                $error = $rs['code'];
            }
            $result = false;
            return response()->json(compact('result', 'error'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $result = false;
            return response()->json(compact('result', 'error'));
        }

    }

    public function delResourceCName($id, $check = true)
    {
        try {
            if ($resource = Cdn_Resources::where('id', $id)->first()) {
                if ($check) {
                    $rs = $this->importHost($id);
                    $rs_data = $rs->getData(true);
                    if ($rs_data['result']) {
                        if (isset($rs_data['host_id_list'][$id])) {
                            $resource->xns_host_id = $rs_data['host_id_list'][$id];
                            $resource->save();
                        }
                    } else {
                        return $rs;
                    }
                }
                if ($resource->xns_host_id) {
                    $rs = json_decode($this->xns_api->host->hostDelete($resource->xns_host_id), true);
                    if ($rs['code'] == 1) {
                        $resource->xns_host_id = null;
                        $result = $resource->save();
                        return response()->json(compact('result'));
                    } else {
                        $error = $rs['code'];
                    }
                } else {
                    $result = true;
                    return response()->json(compact('result'));
                }
            } else {
                $error = 'not_found';
            }
            $result = false;
            return response()->json(compact('result', 'error'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $result = false;
            return response()->json(compact('result', 'error'));
        }
    }

    public function revertResourceDNS($id)
    {
        try {
            if ($resource = Cdn_Resources::where('id', $id)->first()) {
                if ($host = $resource->getHostFromCName()) {
                    $rs = $this->delResourceCName($id);
                    if ($rs->getData()->result) {
                        $j_origin = json_decode($resource->origin, true);
                        foreach ($j_origin as $origin) {
                            $rs = $this->addRecord($host, 'A', $origin['ip']);
                            if ($rs->getData()->result) {
                                $rs = $this->importHost($id);
                                $rs_data = $rs->getData(true);
                                if ($rs_data['result']) {
                                    if (isset($rs_data['host_id_list'][$id])) {
                                        $resource->xns_host_id = $rs_data['host_id_list'][$id];
                                        $resource->status = -1;
                                        $resource->force_update = 0;
                                        $resource->update_status = 0;
                                        $resource->error_msg = '';
                                        $result = $resource->save();
                                        return response()->json(compact('result'));
                                    }
                                } else {
                                    return $rs;
                                }
                            } else {
                                return $rs;
                            }
                        }
                    } else {
                        return $rs;
                    }
                    $error = $rs['code'];
                } else {
                    $error = 'invalid_cname';
                }

            } else {
                $error = 'not_found';
            }
            $result = false;
            return response()->json(compact('result', 'error'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $result = false;
            return response()->json(compact('result', 'error'));            
        }
    }
}
