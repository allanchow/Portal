<?php

namespace App\Http\Controllers\Cdn;

// controllers
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Cdn\CdnController;
use App\Http\Controllers\Controller;
// models
use App\Model\Cdn\Cdn_Resources;
use App\Model\Cdn\CdnDailyReport;
use App\Model\Cdn\NgxAccessCdn;

/**
 * CdnReportController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class CdnReportController extends Controller
{
    protected $ext_view = 'themes.default1.client.layout.dashboard';

    /**
     * Create a new controller instance.
     *
     * @return type void
     */
    public function __construct(PhpMailController $PhpMailController)
    {
        $this->PhpMailController = $PhpMailController;
    }

    public function genDailyByteSentReport($day = null)
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
        $end_ts = $start_ts + 86399999;
        $log = new NgxAccessCdn();
        //$rs = $log->orderBy('_id', 'desc')->take(5)->get();
        //$rs = $log->whereBetween('request_at_ms.bytes', [489, 800])->take(5)->get();
        $per_page = 10000;
        $query = $log->whereBetween('request_at_ms', [$start_ts, $end_ts]);
        $total = $query->count();
        $total_page = ceil($total/$per_page);
        $rs_data = $total_b = $http_b = $https_b = $count = array();
        for ($page = 0; $page < $total_page; $page++) {
            $rs = $query->skip($page*$per_page)->take($per_page)->get();
            foreach ($rs as $data) {

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
        }
        ksort($rs_data);
        foreach ($rs_data as $resource_id=>$data){
            $report = new CdnDailyReport();
            $report->resource_id = $resource_id;
            $report->report_date = $day;
            $report->http_byte = $data['http_b'];
            $report->https_byte = $data['https_b'];
            $report->total_byte = $data['total_b'];
            $report->total_req = $data['count'];
            $report->save();
        }
    }
}
