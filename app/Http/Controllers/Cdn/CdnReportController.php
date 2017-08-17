<?php

namespace App\Http\Controllers\Cdn;

// controllers
use App\Http\Controllers\Controller;
// models
use App\Model\Cdn\Cdn_Resources;
use App\Model\Cdn\CdnDailyReport;
use App\Model\Cdn\NgxAccessCdn;
use MongoDB\BSON\ObjectID;

/**
 * CdnReportController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class CdnReportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return type void
     */
    public function __construct()
    {
    }

    public function genDailyByteSentReport($day = null, $id = null, $end_ts = null)
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '1024M');
        set_error_handler(null);
        set_exception_handler(null);
        //$t = DB::connection('mongodb');
        //dd($t->collection('logs.ngx-access-cdn')->where('request_at_ms', '>', 1490589000)->get());
        //dd(DB::collection('ngx-access-cdn')->first());
        if (is_null($day)) {
            $day = date('Y-m-d', strtotime('yesterday'));
        }
        $start_ts = strtotime($day.' 00:00:00.0000') * 1000;

        if (is_null($end_ts)) {
            $end_ts = $start_ts + 86399999;
        }

        $log = new NgxAccessCdn();
        //$rs = $log->orderBy('_id', 'desc')->take(5)->get();
        //$rs = $log->whereBetween('request_at_ms.bytes', [489, 800])->take(5)->get();
        $per_page = 100000;
        $query = $log->whereBetween('request_at_ms', [$start_ts, $end_ts]);
        $total = $query->count();
        $total_page = ceil($total/$per_page);
        loging('cdn-daily-report', "time: $start_ts - $end_ts", 'info');

        try {
            for ($page = 1; $page <= $total_page; $page++) {
                $rs_data = $total_b = $http_b = $https_b = $count = array();
                if (is_null($id)) {
                    loging('cdn-daily-report-query', "day: $day, time: $start_ts - $end_ts, page: $page / $total_page, per_page: $per_page", 'info');
                    $rs = NgxAccessCdn::select('resource_id', 'tags', 'resp.bytes', 'https', 'request_at_ms')->whereBetween('request_at_ms', [$start_ts, $end_ts])->take($per_page)->get();
                } else {
                    loging('cdn-daily-report-query', "last id: $id, time: $start_ts - $end_ts, page: $page / $total_page, per_page: $per_page", 'info');
                    $rs = NgxAccessCdn::select('resource_id', 'tags', 'resp.bytes', 'https', 'request_at_ms')->whereRaw(['_id' => ['$lt' => new ObjectID($id)]])->whereBetween('request_at_ms', [$start_ts, $end_ts])->take($per_page)->get();
                }

                foreach ($rs as $data) {
                    $id = $data->getKey();
                    $end_ts = $data['request_at_ms'];
                    $resource_id = $data['resource_id'];

                    if ((isset($data['tags']) && $data['tags'][0] == "allbrightnet") || !is_numeric($resource_id)) {
                        continue;
                    }

                    if (!isset($http_b[$resource_id])) {
                        $http_b[$resource_id] = 0;
                    }
                    if (!isset($https_b[$resource_id])) {
                        $https_b[$resource_id] = 0;
                    }
                    if (!isset($count[$resource_id])) {
                        $count[$resource_id] = 0;
                    }

                    if ($data['https']) {
                        $https_b[$resource_id] += $data['resp']['bytes'];
                    } else {
                        $http_b[$resource_id] += $data['resp']['bytes'];
                    }
                    $total_b[$resource_id] = $http_b[$resource_id] + $https_b[$resource_id];
                    $count[$resource_id]++;
                    $rs_data[$resource_id] = [
                        'http_b'  => $http_b[$resource_id] * 1,
                        'https_b' => $https_b[$resource_id] * 1,
                        'total_b' => $total_b[$resource_id] * 1,
                        'count'   => $count[$resource_id] *1
                    ];
                }
                ksort($rs_data);
                foreach ($rs_data as $resource_id=>$data){
                    $report = CdnDailyReport::firstOrNew(['report_date'=>$day, 'resource_id'=>$resource_id]);
                    $report->http_byte = $report->http_byte * 1 + $data['http_b'];
                    $report->https_byte = $report->https_byte * 1 + $data['https_b'];
                    $report->total_byte = $report->total_byte * 1 + $data['total_b'];
                    $report->total_req = $report->total_req *1 + $data['count'];
                    $report->save();
                }
                loging('cdn-daily-report-last', "last id: $id, end_ts: $end_ts", 'info');
            }
        } catch (Exception $ex) {
            $subject = '[CDN] Daily Report Error';
            $message = "last id: $id, time: $start_ts - $end_ts, page: $page / $total_page, per_page: $per_page<br>\n".$ex->getMessage();
            $this->send_mail($subject, $message);
        }

        return true;
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
}
