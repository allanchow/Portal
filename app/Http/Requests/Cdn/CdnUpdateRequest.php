<?php

namespace App\Http\Requests\Cdn;

use App\Http\Requests\Request;

class CdnUpdateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cdn_hostname' => 'required|unique:cdn_resources,cdn_hostname,'.$this->segment(2),
            'org_id' => 'required',
            'origin' => 'required',
        ];
    }
}
