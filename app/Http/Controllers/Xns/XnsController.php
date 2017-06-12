<?php

namespace App\Http\Controllers\Xns;

require_once base_path('vendor/cloudxns/cloud-xns-api-sdk-php/vendor/autoload.php');

// controllers
use App\Http\Controllers\Controller;
// model
use App\Model\Xns\Xns;
use App\Model\Cdn\Cdn_Resources;
use App\Model\Cdn\CdnPop;
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
    protected $domain_name;
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

    protected $default_cdn_line = array(
                                        'hk' => ['default', 'asia'],
                                        'us' => ['oth'],
                                        'jp' => ['jp'],
                                        'kr' => ['kr'],
                                        'sg' => ['sg']
    );

    protected $default_line = 'default';

    private $per_page = 2000;
    private $ttl = 60;
    /**
     * Create a new controller instance.
     *
     * @return type void
     */
    public function __construct()
    {
        if (!\App::runningInConsole()) {
            // checking authentication
            $this->middleware('auth');
            // checking if role is agent
            //$this->middleware('role.agent');
        }
        $this->xns_api = new Api();
        $this->initXNS();
    }

    protected function initXNS($domain_name = '')
    {
        if ($domain_name == '') {
            $cdn_resources = new Cdn_Resources();
            $this->domain_name = $cdn_resources->get_cdn_domain();
        }
        $xns = new XNS();
        $xns_data = $xns->where('domain_name', $this->domain_name)->first();
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

    public function getRecordList($host_id = 0)
    {
        $record_list = $xns_list = array();
        $offset = 0;
        do
        {
            $rs = json_decode($this->xns_api->record->recordList($this->domain_id, $host_id, $offset, $this->per_page), true);
            if ($xns_list = $rs['data'])
            {
                foreach ($xns_list as $record)
                {
                    $record_list[$record['host']][] = $record;
                }
            }
            $offset += $this->per_page;
        } while(!is_null($xns_list) && count($xns_list) == $this->per_page);
        return $record_list;
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
                $record_id = $rs['record_id'];
                return response()->json(compact('result', 'record_id'));
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

    public function delRecord($record_id)
    {
        try {
            $rs = json_decode($this->xns_api->record->recordDelete($record_id, $this->domain_id), true);
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

    public function addResourceCNameGroup(Cdn_Resources $resource) {
        if ($host = $resource->getHostFromCName()) {
            $cdnpop = new CdnPop;
            return $this->addRecord($host, 'CNAME', $cdnpop->get_resource_group($resource->group).'.'.$this->domain_name, $this->default_line, $mx = 1);
        } else {
            $error = 'invalid_cname';
        }
        $result = false;
        return response()->json(compact('result', 'error'));
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

    public function add_cdn_pop(CdnPop $cdn_pop) {
        $region = $cdn_pop->get_region();
        $this->default_line = 'default';
        if (!array_key_exists($region, $this->default_cdn_line)) {
            $rs_line = [$this->default_line];
        } else {
            $rs_line = $this->default_cdn_line[$region];
        }

        if ($host_list = $this->getHostList()) {
            $pop_group = $cdn_pop->get_pop_group();
            if (!array_key_exists($pop_group, $host_list)) {
                if ($region != 'hk') {
                    return false;
                }
                foreach ($rs_line as $line) {
                    $rs = $this->addRecord($pop_group, 'A', $cdn_pop->ip, $line, $mx = 1);
                    if (!$rs->getData()->result) {
                        return false;
                    }
                }
            } else {
                $host_id = $host_list[$pop_group]['id'];
                if ($record_list = $this->getRecordList($host_id))
                {
                    $not_exists = 1;
                    foreach ($record_list[$pop_group] as $record) {
                        if ($record['type'] == 'A' && $record['value'] == $cdn_pop->ip) {
                            $not_exists = 0;
                        }
                    }

                    if ($not_exists) {
                        foreach ($rs_line as $line) {
                            $rs = $this->addRecord($pop_group, 'A', $cdn_pop->ip, $line, $mx = 1);
                            if (!$rs->getData()->result) {
                                return false;
                            }
                        }
                    }

                }
                else {
                    return false;
                }
            }

            $group = $cdn_pop->get_group();
            if (!array_key_exists($group, $host_list)) {
                $rs = $this->addRecord($group, 'CNAME', $pop_group.'.'.$this->domain_name, $this->default_line, $mx = 1);
                if (!$rs->getData()->result) {
                    return false;
                }                
            }

            if ($cdn_pop->is_ddos_pop()) {
                $ddos_pop_group = $cdn_pop->get_ddos_pop_group();
                if (array_key_exists($ddos_pop_group, $host_list)) {

                    $host_id = $host_list[$ddos_pop_group]['id'];

                    if ($record_list = $this->getRecordList($host_id))
                    {
                        $not_exists = 1;
                        foreach ($record_list[$ddos_pop_group] as $record) {
                            if ($record['type'] == 'A' && $record['value'] == $cdn_pop->ip) {
                                $not_exists = 0;
                            }
                        }
                        if ($not_exists) {
                            $rs = $this->addRecord($ddos_pop_group, 'A', $cdn_pop->ip, $this->default_line, $mx = 1);
                            if (!$rs->getData()->result) {
                                return false;
                            }
                        }

                    }
                    else {
                        return false;
                    }
                } else {
                    $rs = $this->addRecord($ddos_pop_group, 'A', $cdn_pop->ip, $this->default_line, $mx = 1);
                    if (!$rs->getData()->result) {
                        return false;
                    }      
                }
            }
        }
        return true;
    }

    public function del_cdn_pop(CdnPop $cdn_pop)
    {
        if ($host_list = $this->getHostList()) {
            $pop_group = $cdn_pop->get_pop_group();
            if (array_key_exists($pop_group, $host_list)) {
                $host_id = $host_list[$pop_group]['id'];
                if ($record_list = $this->getRecordList($host_id))
                {
                    foreach ($record_list[$pop_group] as $record) {
                        if ($record['type'] == 'A' && $record['value'] == $cdn_pop->ip) {
                            $rs = $this->delRecord($record['record_id']);
                            if (!$rs->getData()->result) {
                                return false;
                            }
                        }
                    }

                }
            }

            if ($cdn_pop->is_ddos_pop()) {
                $ddos_pop_group = $cdn_pop->get_ddos_pop_group();
                if (array_key_exists($ddos_pop_group, $host_list)) {
                    $host_id = $host_list[$ddos_pop_group]['id'];

                    if ($record_list = $this->getRecordList($host_id))
                    {
                        foreach ($record_list[$ddos_pop_group] as $record) {
                            if ($record['type'] == 'A' && $record['value'] == $cdn_pop->ip) {
                                $rs = $this->delRecord($record['record_id']);
                                if (!$rs->getData()->result) {
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function delHost($host) {
        if ($host_list = $this->getHostList()) {
                if (array_key_exists($host, $host_list)) {
                    $host_id = $host_list[$host]['id'];
                    $rs = json_decode($this->xns_api->host->hostDelete($host_id), true);
                    if ($rs['code'] == 1) {
                        $result = true;
                        return response()->json(compact('result'));
                    } else {
                        $error = $rs['code'];
                    }
                } else {
                    $result = true;
                    return response()->json(compact('result'));
                }            
            $result = false;
            return response()->json(compact('result', 'error'));
        } else {
            $result = false;
            $error = 'not_found';
            return response()->json(compact('result', 'error'));
        }
    }

    public function addGroupPop($group_id) {
        $cdnpop = new CdnPop;
        $group = $cdnpop->get_resource_group($group_id);
        $pop_group = $cdnpop->get_resource_pop_group($group_id);
        return $this->addRecord($group, 'CNAME', $pop_group.'.'.$this->domain_name, $this->default_line, $mx = 1);
    }

    public function addGroupPopDDOS($group_id) {
        $cdnpop = new CdnPop;
        $group = $cdnpop->get_resource_group($group_id);
        $ddos_pop_group = $cdnpop->get_ddos_pop_group();
        return $this->addRecord($group, 'CNAME', $ddos_pop_group.'.'.$this->domain_name, $this->default_line, $mx = 1);
    }

    public function getDomainName(){
        return $this->domain_name;
    }
}
