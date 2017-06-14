<?php

namespace App\Http\Controllers\Cdn;

// controllers
use App\Http\Controllers\Xns\XnsController;
use App\Http\Controllers\Controller;
// requests
use App\Http\Requests\Cdn\CdnPopRequest;
use App\Http\Requests\Cdn\CdnPopUpdateRequest;
// models
use App\Model\Cdn\CdnPop;
use App\Model\Cdn\Cdn_Resources;
// classes
use Auth;
use Datatables;
use DB;
use Exception;
use Illuminate\Http\Request;
use Input;
use Lang;
use Redirect;


class CdnPopController extends Controller
{

    public function __construct()
    {
        if (!\App::runningInConsole()) {
            // checking authentication
            $this->middleware('auth');
            $this->middleware('role.agent');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $table = \ Datatable::table()
        ->addColumn(Lang::get('lang.pop_hostname'),
            'IP',
            Lang::get('lang.status'),
            Lang::get('lang.dns_status'),
            Lang::get('lang.action'))
            ->noScript();
        return view('themes.default1.cdn.cdnpop_index', compact('table'));
    }

    public function list(CdnPop $cdnpop, Request $request)
    {
        $type = $request->input('profiletype');
        $search = $request->input('searchTerm');

        $total_default_pop = $cdnpop->where('status', 1)->whereRaw('LEFT(RIGHT(pop_hostname, 5), 2) = ?', ['hk'])->count();

        if ($type === 'active') {
            $cdnpop_list = $cdnpop->where('status', 1);
        } elseif ($type === 'inactive') {
            $cdnpop_list = $cdnpop->where('status', 0);
        } else {
            $cdnpop_list = $cdnpop;
        }

        $cdnpop_list = $cdnpop_list->select('pop_hostname', 'ip', 'status', 'dns_status', DB::raw('LEFT(RIGHT(pop_hostname, 5), 2) AS region'), DB::raw("{$total_default_pop} AS total_default_pop"));

        if ($search !== '') {
            $cdnpop_list = $cdnpop_list->where(function ($query) use ($search) {
                $query->where('pop_hostname', 'LIKE', '%'.$search.'%')
                    ->orWhere('ip', 'LIKE', '%'.$search.'%');
            });
        }

        return \Datatables::of($cdnpop_list)
                        ->removeColumn('region', 'total_default_pop')
                        ->addColumn('status', function ($model) {
                            $status = $model->status;
                            if ($status) {
                                $stat = '<span class="label label-primary">'.\Lang::get('lang.active').'</span>';
                            } else {
                                $stat = '<span class="label label-default">'.\Lang::get('lang.inactive').'</span>';
                            }
                            return $stat;
                        })
                        ->addColumn('dns_status', function ($model) {
                            $dns_status = $model->dns_status;
                            if ($dns_status == 2) {
                                $stat = '<span class="label label-primary">'.\Lang::get('lang.completed').'</span>';
                            } elseif ($dns_status == 1) {
                                $stat = '<span class="label label-warning">'.\Lang::get('lang.pop_updating').'</span>';
                            } else {
                                $stat = '<span class="label label-default">'.\Lang::get('lang.pending').'</span>';
                            }
                            return $stat;
                        })                        ->addColumn('Actions', function ($model) {
                            if (!($model->region == 'hk' && $model->total_default_pop == 1 && $model->status == 1)) {
                                if ($model->status) {
                                    return '<button data="'.$model->pop_hostname.'" change=0 class="btn btn-default btn-xs btn_change"> -&gt; '.\Lang::get('lang.inactive').'</a>';
                                } else {
                                    return '<button data="'.$model->pop_hostname.'" change=1 class="btn btn-success btn-xs btn_change"> -&gt; '.\Lang::get('lang.active').'</a>';
                                }

                            }
                        })
                        ->make();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        dd($request->session()->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CdnPop $cdn_pop, CdnPopRequest $request)
    {
//        DB::enableQueryLog();
        try {
            $cdn_pop->pop_hostname = $request->input('pop_hostname');
            $cdn_pop->ip = $request->input('ip');
            $status = $request->input('status');
            $cdn_pop->status = $status;
            if ($status) {
                $cdn_pop->dns_status = 0;
            } else {
                $cdn_pop->dns_status = 2;
            }
            if ($cdn_pop->save()) {
                return redirect('cdnpop')->with('success', Lang::get('lang.added_successfully'));
            } else {
                return redirect('cdnpop')->with('warning', Lang::get('lang.added_successfully').';'.Lang::get('lang.dns_create_failed'));
            }
//            dd(DB::getQueryLog());
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('fails', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($pop_hostname)
    {
        dd($request->session()->all());
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($pop_hostname)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($pop_hostname, CdnPopUpdateRequest $request)
    //public function update($pop_hostname, Request $request)
    {
        try {
            $ip = $request->input('ip');
            $status = $request->input('status');
            if ($cdn_pop = CdnPop::where('pop_hostname', $pop_hostname)->first()) {
                if (!($cdn_pop->ip == $ip && $cdn_pop->$status == $status)){
                    $xns = new XnsController();
                    $no_error = true;

                    if ($cdn_pop->status == 1) {
                        if ($cdn_pop->ip != $ip || $status == 0) {
                            if ($xns->del_cdn_pop($cdn_pop)) {
                                if ($status == 0) {
                                    $cdn_pop->dns_status = 2;
                                } else {
                                    $cdn_pop->dns_status = 0;
                                }
                            } else {
                                $cdnpop->dns_status = 0;
                                $no_error = false;
                            }

                        }
                    }

                    if ($no_error) {
                        $cdn_pop->ip = $ip;
                        $cdn_pop->status = $status;
                        if ($cdn_pop->save())
                        {
                            return redirect()->route('cdnpop.edit', $cdn_pop->pop_hostname)->with('success', Lang::get('lang.updated_successfully'));
                        }
                    }

                } else {
                    return redirect()->back()->withInput()->with('fails', Lang::get('lang.error-no_change'));
                }
            } else {
                return redirect()->route('cdnpop')->with('fails', Lang::get('lang.not_found'));
            }
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('fails', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        //
    }

    public function attack()
    {
        ini_set('max_execution_time', 300);
        $cdnpop = new CdnPop();
        $cdn_resource = new Cdn_Resources();
        $xns = new XnsController();
        if ($group_list = $cdn_resource->select('group')->distinct()->where('status', '>', 0)->get()) {
            foreach ($group_list as $rs_group) {
                $group_id = $rs_group->group;
                $group = $cdnpop->get_resource_group($group_id);
                $rs = $xns->delHost($group);
                if ($rs->getData()->result) {
                    $rs = $xns->addGroupPopDDOS($group_id);
                    if (!$rs->getData()->result) {
                        return $rs;
                    }
                } else {
                    return $rs;
                }
            }
            return $rs;
        } else {
            $error = Lang::get('lang.not_allowed');
            return response()->json(compact('error'));
        }
    }

    public function resume()
    {
        ini_set('max_execution_time', 300);
        $cdnpop = new CdnPop();
        $cdn_resource = new Cdn_Resources();
        $xns = new XnsController();
        if ($group_list = $cdn_resource->select('group')->distinct()->where('status', '>', 0)->get()) {
            foreach ($group_list as $rs_group) {
                $group_id = $rs_group->group;
                $group = $cdnpop->get_resource_group($group_id);
                $rs = $xns->delHost($group);
                if ($rs->getData()->result) {
                    $rs = $xns->addGroupPop($group_id);
                    if (!$rs->getData()->result) {
                        return $rs;
                    }
                } else {
                    return $rs;
                }
            }
            return $rs;
        } else {
            $error = Lang::get('lang.not_allowed');
            return response()->json(compact('error'));
        }
    }

    public function forceUpdate()
    {
        ini_set('max_execution_time', 300);
        $cdnpop = new CdnPop();
        $cdn_resource = new Cdn_Resources();
        $xns = new XnsController();
        if ($resource_list = $cdn_resource->where('status', '>', 0)->get()) {
            $rs = $xns->importHost();
            if ($rs->getData()->result) {
                foreach ($resource_list as  $resource) {
                    $group = $cdnpop->get_resource_group($resource->group);
                    $rs = $xns->delResourceCName($resource->id, false);
                    if ($rs->getData()->result) {
                        $rs = $xns->addResourceCNameGroup($resource);
                    }
                }
            }
            return $rs;
        } else {
            $error = Lang::get('lang.not_allowed');
            return response()->json(compact('error'));
        }
    }

    public function getStatus($console = false){
        $cdnpop = new CdnPop();
        $xns = new XnsController();
        $group = $cdnpop->get_resource_group(1);
        if ($host_list = $xns->getHostList()) {
            if (array_key_exists($group, $host_list)){
                $host_id = $host_list[$group]['id'];
                if ($record_list = $xns->getRecordList($host_id))
                {
                    $result = true;
                    if ($console){
                        $msg = $record_list[$group][0]['value'] == $cdnpop->get_ddos_pop_group().'.'.$xns->getDomainName().'.' ? 'ddos' : 'normal';
                    } else {
                        $msg = $record_list[$group][0]['value'] == $cdnpop->get_ddos_pop_group().'.'.$xns->getDomainName().'.' ? Lang::get('lang.ddos_attacking') : Lang::get('lang.normal');
                    }
                    //$msg = $record_list[$group][0]['value'] . " : " . $cdnpop->get_ddos_pop_group().'.'.$xns->getDomainName().'.';
                    return response()->json(compact('result', 'msg'));
                }
            }
        }
        $error = Lang::get('lang.system_errors');
        return response()->json(compact('error'));
    }

    public function changeStatus($pop_hostname, $status = null)
    {
        ini_set('max_execution_time', 300);
        $cdnpop = new CdnPop();
        $xns = new XnsController();
        if ($cdnpop = $cdnpop->where('pop_hostname', $pop_hostname)->first()) {
            if (is_null($status)) {
                $new_status  = $cdnpop->status ? 0 : 1;
            } else {
                $new_status = $status;
            }

            $no_error = true;
            if ($new_status) {
                $cdnpop->dns_status = 0;
            } else {
                if ($xns->del_cdn_pop($cdnpop))
                {
                    $cdnpop->dns_status = 2;
                } else {
                    $cdnpop->dns_status = 0;
                    $no_error = false;
                }
            }
            if ($no_error) {
                $cdnpop->status = $new_status;
                $result = $cdnpop->save();
                return response()->json(compact('result'));
            } else {
                $error = Lang::get('lang.update_failed');
                return response()->json(compact('error'));
            }
        } else {
            $error = Lang::get('lang.not_found');
            return response()->json(compact('error'));
        }
    }

    public function get_max_group()
    {
        $cdnpop = new CdnPop();
        if ($data = $cdnpop->select(DB::raw("MAX(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(pop_hostname, '-', -3), '-', 1), 'g', -1)) AS max_group"))->first())
        {
            return $data['max_group'];
        }
        return 0;
    }
}
