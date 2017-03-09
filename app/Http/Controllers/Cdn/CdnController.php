<?php

namespace App\Http\Controllers\Cdn;

// controllers
use App\Http\Controllers\Common\PhpMailController;
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
    protected $default_file_type = ["jpg", "jpeg", "png", "bmp", "gif", "html", "htm", "xml", "js", "css", "pdf", "swf", "ico", "wav"];
    protected $default_max_age = 1800;

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
        } elseif ($type === 'suspended') {
            $resources = $resources->where('status', 0);
        } elseif ($type === 'updating') {
            $resources = $resources->where('update_status', 1);
        }

        if (Auth::user()->role == "user") {
           $resources = $resources->where('org_id', User_org::where('user_id', '=', Auth::user()->id)->first()->org_id);
        }

        $resources = $resources->select('id', 'cdn_hostname', 'cname', 'status', 'update_status', 'force_update', 'created_at', 'error_msg');

        if ($search !== '') {
            $resources = $resources->where(function ($query) use ($search) {
                $query->where('cdn_hostname', 'LIKE', '%'.$search.'%');
                $query->orWhere('cname', 'LIKE', '%'.$search.'%');
            });
        }

        return \Datatables::of($resources)
                        /* column username */
                        ->removeColumn('id', 'update_status', 'force_update', 'error_msg')
                        ->addColumn('cdn_hostname', function ($model) {
                                return '<a href="'.route('resource.edit', $model->id).'">'.$model->cdn_hostname.'</a>';
                        })
                        ->addColumn('status', function ($model) {
                            $status = $model->status;
                            $update_status = $model->update_status;
                            if ($status == 0) {
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
                            } elseif ($update_status == 3) {
                                $stat .= ' <span class="label label-warning">'.\Lang::get('lang.pending').'</span>';
                            }
                            if ($model->force_update == 1 && (Auth::user()->role == "agent" || Auth::user()->role == "admin")) {
                                $stat .= ' <span class="label label-warning">'.\Lang::get('lang.force_update').'</span>';
                            }
                            if ($model->error_msg != '') {
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
            $i_origin = explode("\n", str_replace(',', "\n", $request->input('origin')));
            foreach ($i_origin as $origin) {
                $ar_origin[] = ['ip'=>$origin];
            }
            if ($resource->validate_origin($ar_origin) === false) {
                return redirect()->back()->withInput()->with('fails', Lang::get('lang.invalid_ip'));
            }
            $resource->cdn_hostname = $request->input('cdn_hostname');
            $resource->org_id = $request->input('org_id');
            if ($request->has('file_type')) {
                $resource->file_type = $request->input('file_type');
            } else {
                $resource->file_type = json_encode($this->default_file_type);
            }
            if ($request->has('max_age')) {
                $resource->max_age = $request->input('max_age');
            } else {
                $resource->max_age = $this->default_max_age;
            }
            $resource->origin = json_encode($ar_origin);
            $resource->status = 1;
            $resource->update_status = 0;
            // saving inputs
            if ($resource->save() == true) {
                if (\App::environment('production')) {
                    $int_id = str_pad($resource->id, 6, "0", STR_PAD_LEFT);
                    $resource->cname = "cdn-{$int_id}.allbrightnetwork.com";
                } else {
                    if ($resource->id > 100000) {
                        $int_id = $resource->id;
                    } else {
                        $int_id = 900000 + $resource->id;
                    }
                    $resource->cname = "uat-cdn-{$int_id}.allbrightnetwork.com";
                }
                $resource->save();
            }

            return redirect()->route('resource.edit', $resource->id)->with('success', Lang::get('lang.added_successfully')."; ".Lang::get('lang.wait_few_mins'));
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('fails', $e->getMessage());
        }
    }

    public function edit($id, Cdn_Resources $resources)
    {
        try {
            $resource = $resources->where('id', '=', $id)->first();
            if (Auth::user()->role == "user" && $resource->org_id != User_org::where('user_id', '=', Auth::user()->id)->first()->org_id) {
                return redirect()->route('resources')->with('fails', Lang::get('lang.not_found'));
            }
            $j_origin = json_decode($resource->origin, true);
            foreach ($j_origin as $origin) {
                $ar_origin[] = $origin['ip'];
            }
            $resource->origin = implode("\n", $ar_origin);
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
            if (Auth::user()->role == "user" && $resource->org_id != User_org::where('user_id', '=', Auth::user()->id)->first()->org_id) {
                return redirect()->route('resources')->with('fails', Lang::get('lang.not_found'));
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
            if ($resource->origin == $new_origin && $resource->cdn_hostname == $request->input('cdn_hostname') && $resource->error_msg == '') {
                return redirect()->back()->withInput()->with('fails', Lang::get('lang.error-no_change'));
            }
            $resource->cdn_hostname = $request->input('cdn_hostname');
            $resource->origin = $new_origin;
            $resource->update_status = 1;
            $resource->error_msg = null;
            // saving inputs
            $resource->save();

            return redirect()->route('resource.edit', $resource->id)->with('success', Lang::get('lang.added_successfully').'; '.Lang::get('lang.wait_few_mins'));
        } catch (Exception $e) {
            return redirect()->route('resource.edit', $resource->id)->with('fails', $e->getMessage());
        }
    }

    public function forceUpdate(Cdn_Resources $resources)
    {
        if (Auth::user()->role == "agent" or Auth::user()->role == "admin") {
            try {
                $result = $resources->where('force_update', 0)->update(['force_update' => 1]);
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
}
