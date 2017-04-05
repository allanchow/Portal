<?php

namespace App\Http\Controllers\Cdn;

// controllers
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Xns\XnsController;
use App\Http\Controllers\Controller;
// requests
use App\Http\Requests\Cdn\CdnRequest;
use App\Http\Requests\Cdn\CdnUpdateRequest;
// models
use App\Model\Cdn\Cdn_Resources;
use App\Model\helpdesk\Agent_panel\Organization;
use App\Model\helpdesk\Agent_panel\User_org;
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

/**
 * CdnController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class CdnController extends Controller
{
    protected $ext_view = 'themes.default1.client.layout.dashboard';

    /**
     * Create a new controller instance.
     *
     * @return type void
     */
    public function __construct(PhpMailController $PhpMailController)
    {
        //$this->middleware('board');
        $this->PhpMailController = $PhpMailController;
        // checking authentication
        $this->middleware('auth');
		if (Auth::user()->role == "user") {
            $this->ext_view = 'themes.default1.client.layout.dashboard';
        }
        else {
            $this->ext_view = 'themes.default1.agent.layout.agent';
        }
    }

    public function index()
    {
        $table = \ Datatable::table()
        ->addColumn(Lang::get('lang.cdn_hostname'),
            Lang::get('lang.cname'),
            Lang::get('lang.type'),
            Lang::get('lang.status'),
            Lang::get('lang.created'),
            Lang::get('lang.action'))
            ->noScript();
        $ext_view = $this->ext_view;
        return view('themes.default1.cdn.index', compact('table', 'ext_view'));
    }

    public function resource_list(Cdn_Resources $resources, Request $request)
    {
        $type = $request->input('profiletype');
        $search = $request->input('searchTerm');

        if ($type === 'active') {
            $resources = $resources->where('status', 2);
        } elseif ($type === 'pending') {
            if (Auth::user()->role == "user") {
                $resources = $resources->where('status', 1);
            } else {
                $resources = $resources->where('status', 1)->orWhere('update_status', 3);
            }
        } elseif ($type === 'updating') {
            $resources = $resources->where('update_status', 1)->where('status', '<>', 0);
        } elseif ($type === 'suspended' && (Auth::user()->role == "agent" || Auth::user()->role == "admin")) {
            $resources = $resources->where('status', 0);
        } elseif ($type === 'deleting' && (Auth::user()->role == "agent" || Auth::user()->role == "admin")) {
            $resources = $resources->where('update_status', 2);
        } elseif ($type === 'dns_to_origin'){
            $resources = $resources->where('status', -1);
        } else {
            $resources = $resources->where('status', '<>', 0);
        }

        if (Auth::user()->role == "user") {
            $resources = $resources->where('org_id', User_org::where('user_id', '=', Auth::user()->id)->first()->org_id)->where('update_status', '<>', 2);
        } elseif ($type != 'deleting') {
            $resources = $resources->where('update_status', '<>', 2);
        }

        $resources = $resources->select('id', 'cdn_hostname', 'cname', 'file_type', 'status', 'update_status', 'force_update', 'created_at', 'error_msg');

        if ($search !== '') {
            $resources = $resources->where(function ($query) use ($search) {
                $query->where('cdn_hostname', 'LIKE', '%'.$search.'%')
                    ->orWhere('cname', 'LIKE', '%'.$search.'%')
                    ->orWhere('created_at', 'LIKE', '%'.$search.'%');
            });
        }

        return \Datatables::of($resources)
                        /* column username */
                        ->removeColumn('id', 'update_status', 'force_update', 'error_msg')
                        ->addColumn('cdn_hostname', function ($model) {
                                return '<a href="'.route('resource.edit', $model->id).'">'.$model->cdn_hostname.'</a>';
                        })
                        ->addColumn('file_type', function ($model) {
                            if (json_decode($model->file_type)) {
                                return '<span class="label label-primary">'.\Lang::get('lang.website').'</span>';
                            } else {
                                return '<span class="label label-warning">'.\Lang::get('lang.dynamic').'</span>';
                            }
                        })
                        ->addColumn('status', function ($model) {
                            $status = $model->status;
                            $update_status = $model->update_status;
                            if ($status == -1) {
                                $stat = '<span class="label label-default">'.\Lang::get('lang.dns_to_origin').'</span>';
                            } elseif ($status == 0) {
                                $stat = '<span class="label label-danger">'.\Lang::get('lang.suspended').'</span>';
                            } elseif ($status == 1) {
                                $stat = '<span class="label label-warning">'.\Lang::get('lang.pending').'</span>';
                            } else {
                                $stat = '<span class="label label-success">'.\Lang::get('lang.active').'</span>';
                            }
                            if ($update_status == 1) {
                                $stat .= ' <span class="label label-warning">'.\Lang::get('lang.updating').'</span>';
                            } elseif ($update_status == 2) {
                                $stat .= ' <span class="label label-danger">'.\Lang::get('lang.deleting').'</span>';
                            } elseif ($update_status == 3 && (Auth::user()->role == "agent" || Auth::user()->role == "admin")) {
                                $stat .= ' <span class="label label-warning">'.\Lang::get('lang.pending').'</span>';
                            }
                            if ($model->force_update == 1 && (Auth::user()->role == "agent" || Auth::user()->role == "admin")) {
                                $stat .= ' <span class="label label-warning">'.\Lang::get('lang.force_update').'</span>';
                            }
                            if ($model->error_msg != '' && !(Auth::user()->role == "user" && $status == 2 && $update_status == 0 && $model->force_update == 1)) {
                                $stat .= ' <span class="label label-danger">'.\Lang::get('lang.error').'</span>';
                            }

                            return $stat;
                        })
                        ->addColumn('Actions', function ($model) {
                                return '<a href="'.route('resource.edit', $model->id).'" class="btn btn-warning btn-xs">'.\Lang::get('lang.edit').'</a>';
                        })
                        ->make();
    }
    public function create(Cdn_Resources $resource)
    {
        try {
            if (Auth::user()->role == "agent" or Auth::user()->role == "admin") {
                $org = Organization::lists('name', 'id')->toArray();
            } else {
                $org = new Organization;
                $resource->org_id = User_org::where('user_id', '=', Auth::user()->id)->first()->org_id;
            }
            $resource->max_age = $resource->get_default_max_age();
            $resource->file_type = json_encode($resource->get_default_file_type());
            $resource->file_type_to_string();
            $ext_view = $this->ext_view;
            $mode = 'create';

            return view('themes.default1.cdn.resource', compact('resource', 'org', 'ext_view', 'mode'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function store(Cdn_Resources $resource, CdnRequest $request)
    {
        try {
            if (!$resource->validate_hostname($request->input('cdn_hostname'))) {
                return redirect()->back()->withInput()->with('fails', Lang::get('lang.invalid_hostname'));
            }
            $i_origin = explode("\n", str_replace(',', "\n", $request->input('origin')));
            foreach ($i_origin as $origin) {
                $origin = trim($origin);
                if ($origin != '') {
                    $ar_origin[] = ['ip'=>$origin];
                }
            }
            if ($resource->validate_origin($ar_origin) === false) {
                return redirect()->back()->withInput()->with('fails', Lang::get('lang.invalid_ip'));
            }

            if ($request->input('host_header') != '' && !$resource->validate_host_header($request->input('host_header'))) {
                return redirect()->back()->withInput()->with('fails', Lang::get('lang.invalid_host_header'));
            }

            $resource->cdn_hostname = $request->input('cdn_hostname');
            $resource->org_id = $request->input('org_id');
            if ($request->has('host_header')) {
                $resource->host_header = $request->input('host_header');
            }
            if (!is_null($request->input('file_type'))) {
                if ($request->input('file_type') == '') {
                    $resource->file_type = json_encode([]);
                } else {
                    $resource->file_type = json_encode(explode(",", $request->input('file_type')));
                }
            } else {
                $resource->file_type = json_encode($resource->get_default_file_type());
            }
            if ($request->has('max_age')) {
                $resource->max_age = $request->input('max_age');
            } else {
                $resource->max_age = $resource->get_default_max_age();
            }
            $resource->origin = json_encode($ar_origin);
            $resource->status = 1;
            $resource->update_status = 0;
            // saving inputs
            if ($resource->save() == true) {
                $resource->createCName();
                $resource->save();
            }

            return redirect('resources')->with('success', Lang::get('lang.added_successfully')."; ".Lang::get('lang.wait_few_mins'));
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('fails', $e->getMessage());
        }
    }

    public function edit($id, Cdn_Resources $resources)
    {
        try {
            $resource = $resources->where('id', '=', $id)->first();
            if (!$resource or (Auth::user()->role == "user" && $resource->org_id != User_org::where('user_id', '=', Auth::user()->id)->first()->org_id)) {
                return redirect()->route('resources')->with('fails', Lang::get('lang.not_found'));
            }
            $j_origin = json_decode($resource->origin, true);
            foreach ($j_origin as $origin) {
                $ar_origin[] = $origin['ip'];
            }
            $resource->origin = implode("\n", $ar_origin);
            $resource->file_type_to_string();
            $org = Organization::lists('name', 'id')->toArray();
            $ext_view = $this->ext_view;
            $mode = 'edit';

            return view('themes.default1.cdn.resource', compact('resource', 'org', 'ext_view', 'mode'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function update($id, CdnUpdateRequest $request)
    {
        try {
			$resource = Cdn_Resources::whereId($id)->first();
            if (!$resource or (Auth::user()->role == "user" && $resource->org_id != User_org::where('user_id', '=', Auth::user()->id)->first()->org_id)) {
                return redirect()->route('resources')->with('fails', Lang::get('lang.not_found'));
            }

            if (!$resource->validate_hostname($request->input('cdn_hostname'))) {
                return redirect()->back()->withInput()->with('fails', Lang::get('lang.invalid_hostname'));
            }

            if ($request->input('host_header') != '' && !$resource->validate_host_header($request->input('host_header'))) {
                return redirect()->back()->withInput()->with('fails', Lang::get('lang.invalid_host_header'));
            }

            $i_origin = explode("\n", str_replace(',', "\n", $request->input('origin')));
            foreach ($i_origin as $origin) {
                $origin = trim($origin);
                if ($origin != '') {
                    $ar_origin[] = ['ip'=>$origin];
                }
            }
            if ($resource->validate_origin($ar_origin) === false) {
                return redirect()->back()->withInput()->with('fails', Lang::get('lang.invalid_ip'));
            }
            $new_origin = json_encode($ar_origin);
            $resource->org_id = $request->input('org_id');

            $has_change = false;

            if ($request->has('host_header')) {
                if ($resource->host_header != $request->input('host_header')) {
                    $has_change = true;
                }
                $resource->host_header = $request->input('host_header');
            }


            if (!is_null($request->input('file_type'))) {

                if ($request->input('file_type') == '') {
                    $file_type = json_encode([]);
                } else {
                    $file_type = json_encode(explode(",", $request->input('file_type')));
                }

                if ($resource->file_type != $file_type) {
                    $has_change = true;
                }
                $resource->file_type = $file_type;
            }

            if ($request->has('max_age')) {
                if ($resource->max_age != $request->input('max_age')) {
                    $has_change = true;
                }
                $resource->max_age = $request->input('max_age');
            }

            if ($resource->origin == $new_origin && $resource->cdn_hostname == $request->input('cdn_hostname') && $resource->error_msg == '' && !$has_change) {
                return redirect()->back()->withInput()->with('fails', Lang::get('lang.error-no_change'));
            }
            $resource->cdn_hostname = $request->input('cdn_hostname');
            $resource->origin = $new_origin;
            $resource->update_status = 1;
            $resource->error_msg = null;
            // saving inputs
            $resource->save();

            return redirect()->route('resource.edit', $resource->id)->with('success', Lang::get('lang.updated_successfully').'; '.Lang::get('lang.wait_few_mins'));
        } catch (Exception $e) {
            return redirect()->route('resource.edit', $resource->id)->with('fails', $e->getMessage());
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            $resource = Cdn_Resources::whereId($id)->where('status', '<>', 0)->first();

            if (!$resource or (Auth::user()->role == "user" && $resource->org_id != User_org::where('user_id', '=', Auth::user()->id)->first()->org_id)) {
                $error = Lang::get('lang.not_found');
                return response()->json(compact('error'));
            }
            $xns = new XnsController();
            $rs = $xns->delResourceCName($id);
            if ($rs->getData()->result) {
                $resource->suspend_cdn_hostname();
                $resource->update_status = 2;
                $result = $resource->save();
                return response()->json(compact('result'));
            } else {
                $result = $rs->getData()->error;
                $error = Lang::get('lang.for_some_reason_your_request_failed');
                return response()->json(compact('result', 'error'));
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            return response()->json(compact('error'));
        }
    }

    public function forceUpdate(Cdn_Resources $resources, Request $request)
    {
        if (Auth::user()->role == "agent" or Auth::user()->role == "admin") {
            try {
                if ($request->has('id') && ($id = $request->input('id'))) {
                    $result = $resources->where('id', $id)->where('status', '>', 0)->update(['force_update' => 1, 'error_msg' => '']);
                } else {
                    $result = $resources->where('force_update', 0)->where('status', '>', 0)->update(['force_update' => 1, 'error_msg' => '']);
                }
                $msg = Lang::get('lang.updated_successfully');
                return response()->json(compact('result', 'msg'));
            } catch (Exception $e) {
                $error = $e->getMessage();
                return response()->json(compact('error'));
            }
        }
        else {
            $error = Lang::get('lang.not_allowed');
            return response()->json(compact('error'));
        }
    }

    public function cancelRevertDns($id)
    {
        try {
            $resource = Cdn_Resources::whereId($id)->where('status', -1)->first();

            if (!$resource or (Auth::user()->role == "user" && $resource->org_id != User_org::where('user_id', '=', Auth::user()->id)->first()->org_id)) {
                $error = Lang::get('lang.not_found');
                return response()->json(compact('error'));
            }
            $xns = new XnsController();
            $rs = $xns->delResourceCName($id);
            if ($rs->getData()->result) {
                $resource->status = 1;
                $result = $resource->save();
                return response()->json(compact('result'));
            } else {
                $result = $rs->getData()->error;
                $error = Lang::get('lang.for_some_reason_your_request_failed');
                return response()->json(compact('result', 'error'));
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            return response()->json(compact('error'));
        }      
    }
}
