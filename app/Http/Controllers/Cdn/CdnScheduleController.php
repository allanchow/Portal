<?php

namespace App\Http\Controllers\Cdn;

// controllers
use App\Http\Controllers\Xns\XnsController;
use App\Http\Controllers\Cdn\CdnPopController;
use App\Http\Controllers\Controller;
// models
use App\Model\Cdn\Cdn_Resources;
use App\Model\Cdn\CdnDailyReport;
use App\Model\Cdn\CdnSSL;
use App\Model\Cdn\CdnPop;
use App\Model\helpdesk\Email\Emails;
use App\User;
// classes
use Crypt;
use DB;
use Mail;

class CdnScheduleController extends Controller
{
    protected $cmd_path = '/usr/local/share/dehydrated/';
    //cd /usr/local/share/dehydrated/;./dehydrated -c -d ssl.allcdn888.com
    /**
     * Create a new controller instance.
     *
     * @return type void
     */
    public function __construct()
    {
        if (!\App::runningInConsole()) {
            $this->middleware('auth');
        }
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
        return true;
    }

    public function checkXNS()
    {
        ini_set('max_execution_time', 600);
        //set_error_handler(null);
        //set_exception_handler(null);
        $xns = new XnsController();
        loging('check-xns', "Start get resources list", 'info');
        if ($resources = Cdn_Resources::whereNull('xns_host_id')->where('status', '>', 0)->get()){
            loging('check-xns', "Get resources list completed", 'info');
            if ($host_list = $xns->getHostList()) {
                loging('check-xns', "Get xns host list completed", 'info');
                foreach ($resources as $resource) {
                    $host = $resource->getHostFromCName();
                    if (array_key_exists($host, $host_list)) {
                        $host_id = $host_list[$host]['id'];
                        $resource->xns_host_id = $host_id;
                        $resource->save();
                        loging('check-xns', "Update host: $host, xns_host_id: $host_id completed", 'info');
                    } else {
                        loging('check-xns', "Start create XNS host: $host", 'info');
                        $xns->addResourceCNameGroup($resource);
                        loging('check-xns', "Create XNS host: $host completed", 'info');
                    }
                }
            }
        }

        if (($cdnpop_list = CdnPop::where('status', 0)->where('dns_status', 0)->get()) && count($cdnpop_list)) {
            loging('check-xns', "Get inactive cdnpop list completed", 'info');
            foreach ($cdnpop_list as $cdnpop) {
                loging('check-xns', "Start delete inactive cdnpop: {$cdnpop->pop_hostname}", 'info');
                if ($xns->del_cdn_pop($cdnpop)) {
                    loging('check-xns', "Delete XNS inactive cdnpop: {$cdnpop->pop_hostname} completed", 'info');
                    $cdnpop->dns_status = 2;
                    $cdnpop->dns_updated_at = DB::raw('now()');
                    $cdnpop->save();
                    loging('check-xns', "Update inactive cdnpop: {$cdnpop->pop_hostname} completed", 'info');
                }
            }
        }

        if (($cdnpop_list = CdnPop::where('status', 1)->where('dns_status', 0)->get()) && count($cdnpop_list) && (Cdn_Resources::where('force_update', 0)->where('status', '>', 0)->count() > 0) && (Cdn_Resources::where('force_update', 0)->where('status', '>', 0)->update(['force_update' => 1, 'error_msg' => '']) !== false )) {
            loging('check-xns', "Get new active cdnpop list completed", 'info');
            foreach ($cdnpop_list as $cdnpop) {
                loging('check-xns', "Start update new active cdnpop: {$cdnpop->pop_hostname}", 'info');
                $cdnpop->dns_status = 1;
                $cdnpop->save();
                loging('check-xns', "Update new active cdnpop: {$cdnpop->pop_hostname} to waiting completed", 'info');
            }
        }

        if (($cdnpop_list = CdnPop::where('status', 1)->where('dns_status', 1)->get()) && count($cdnpop_list) && (Cdn_Resources::where('force_update', 0)->where('status', '>', 0)->count() > 0)) {
            loging('check-xns', "Get completed force_update active cdnpop list completed", 'info');
            foreach ($cdnpop_list as $cdnpop) {
                loging('check-xns', "Start add XNS new active cdnpop: {$cdnpop->pop_hostname}", 'info');
                if ($xns->add_cdn_pop($cdnpop)){
                    loging('check-xns', "Add XNS new active cdnpop: {$cdnpop->pop_hostname} completed", 'info');
                    $cdnpop->dns_status = 2;
                    $cdnpop->dns_updated_at = DB::raw('now()');
                    $cdnpop->save();
                    loging('check-xns', "Update new active cdnpop: {$cdnpop->pop_hostname} completed", 'info');
                }
            }

        }
        return true;
    }

    public function checkPop()
    {
        loging('check-pop', "Start", 'info');
        $nagios_server = env('NAGIOS_SERVER');
        $nagios_port = env('NAGIOS_PORT');
        $time_buffer = 900;
        $tcp_service = "/^TCP-80-Monitor/";
        if (\App::environment('production')) {
            $pop_group = 'all_dedicated_cdn';
            $pattern = '/^cdn-g\d+-(\w{2})-\d+/';
        } else {
            $pop_group = 'all_cdn_uat';
            $pattern = '/^uat-cdn-g\d+-(\w{2})-\d+/';
        }

        $action = "/_status/_hostgroup/{$pop_group}/_service";
        $base_url = "http://{$nagios_server}:{$nagios_port}{$action}";
        if (($rs = file_get_contents($base_url)) && ($pop_list = json_decode($rs, true)))
        {

            $all_outdate = true;
            $ts = time();
            $hk_down = $hk_total = 0;
            $pop_down = array();

            foreach ($pop_list as $hostname=>$pop) {
                if (is_array($pop) && preg_match($pattern, $hostname, $matches)) {
                    $pop_hostname = $matches[0];
                    $region = $matches[1];
                    if ($is_hk = ($region == 'hk')) {
                        $hk_total++;
                    }

                    $tcp_service_down = 0;
                    $services_name = preg_grep($tcp_service, array_keys($pop));
                    $service_down = array();
                    foreach ($services_name as $service_name) {
                        $service = $pop[$service_name];
                        if ($ts - $service['last_check'] < $time_buffer * 2 ) {
                            $all_outdate = false;
                        }
                        if ($service['current_state'] > 0 && $service['current_attempt'] == $service['max_attempts'] && $service['last_check'] - $service['last_state_change'] >= $time_buffer) {
                            $service_down[] = $service_name;
                        }
                    }

                    if (count($service_down) > 1) {
                        if ($is_hk) {
                            $hk_down++;
                        }
                        $pop_down[$pop_hostname] = $service_down;
                    }
                }
            }

            if ($all_outdate) {
                loging('check-pop', "Restart Nagios API service", 'info');
                $restart_rs = json_decode(trim(`ssh -t -o StrictHostKeyChecking=no {$nagios_server} 'sudo /etc/init.d/nagira restart >/dev/null 2>&1 && echo {\"result\":1}'`), true);
                $subject = "[XNS] Nagios API not update";
                $message = $subject;
                if (isset($restart_rs['result']))
                {
                    $subject .= ' and restarted';
                    $message .= '<br><br>Nagios API <span style="color:#0000FF">succeeded</span> to restart<br>';
                }
                else
                {
                    $subject .= ' and failed to restart';
                    $message .= '<br><br>Nagios API <span style="color:#FF0000">failed</span> to restart<br>';
                }
                $this->send_mail($subject, $message);
            } elseif (count($pop_down)) {
                $CdnPopController = new CdnPopController();
                foreach ($pop_down as $pop_hostname=>$services_name) {
                    if ($cdnpop = CdnPop::where('pop_hostname', $pop_hostname)->where('status', 1)->first())
                    {
                        $rs = $CdnPopController->changeStatus($pop_hostname, 0);
                        if ($rs->getData()->result) {
                            $subject = "[XNS] TCP 80 CRITICAL @{$pop_hostname}";
                            $message = $subject;
                            $message .= "\n<br><br>Nagios Services CRITICAL:<br>\n";
                            $message .= implode("<br>\n", $services_name);
                            $this->send_mail($subject, $message);
                        }
                    }

                }
                if ($hk_total > 2 && $hk_down > $hk_total / 2){
                    $status = $CdnPopController->getStatus(true);
                    if ($status->getData()->result && $status->getData()->msg == 'normal') {
                        $rs = $CdnPopController->attack($pop_hostname, 0);
                        if ($rs->getData()->result) {
                            $subject = "[XNS] Changed to DDoS Mode";
                        } else {
                            $subject = "[XNS] Change to DDoS Mode Failed";
                        }
                        $message = $subject;
                        $message .= "\n<br><br>CDN POP downed:<br>\n";
                        $message .= implode("<br>\n", array_keys($pop_down));
                        $this->send_mail($subject, $message);

                    }
                }

            }
            loging('check-pop', "Complete", 'info');
            return true;
        }
        return false;
    }

    public function send_mail($subject, $message)
    {
        $from = Emails::where('id', 1)->first();
        if (\App::environment('production')) {
            $to = 'monitors-push@allbrightnet.com';
        } else {
            $to = 'tommy.ho@allbrightnet.com';
        }
        Mail::send([], [], function ($m) use ($from, $to, $message, $subject) {
            $m->from($from->email_address, $from->email_name);
            $m->to($to)
                ->subject($subject)
                ->setBody($message, 'text/html');
        });
    }

    public function checkDNSSwitched()
    {
        loging('check-dns-switched', "Start", 'info');
        $day = date('Y-m-d', strtotime('yesterday'));
        set_error_handler(null);
        set_exception_handler(null);
        if ($resources = Cdn_Resources::where('status', '>', 0)->get()) {
            foreach ($resources as $resource) {
                $chk_hostname = $resource->cdn_hostname;
                $old_switched = $resource->dns_switched;

//                loging('check-dns-switched', "Start check hostname: $chk_hostname", 'info');

                if ($report = CdnDailyReport::where('report_date', $day)->where('resource_id', $resource->id)->first()){
//                    loging('check-dns-switched', "Resource_id found in report: {$resource->resource_id}", 'info');
                    $resource->dns_switched = 1;
                }

                if ($resource->dns_switched == 0) {
                    if (($dns_record = dns_get_record($chk_hostname, DNS_CNAME)) && $dns_record[0]['target'] == $resource->cname) {
                        $resource->dns_switched = 1;
//                        loging('check-dns-switched', "hostname: $chk_hostname found in DNS", 'info');
                    } else {
                        if ($resource->is_wildcard($resource->cdn_hostname)) {
                            $chk_hostname = preg_replace('/^\*\./', 'www.', $chk_hostname);
                            if (($dns_record = dns_get_record($chk_hostname, DNS_CNAME)) && $dns_record[0]['target'] == $resource->cname) {
                                $resource->dns_switched = 1;
//                                loging('check-dns-switched', "hostname: $chk_hostname found in DNS", 'info');
                            } else {
                                $resource->dns_switched = 0;
                            }
                        } else {
                            $resource->dns_switched = 0;
                        }
                    }
                }

                if ($old_switched != $resource->dns_switched) {
                    $resource->save();
                }
            }
            loging('check-dns-switched', "Complete", 'info');
            return true;
        }
        return false;
    }
}