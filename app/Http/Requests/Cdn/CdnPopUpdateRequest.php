<?php

namespace App\Http\Requests\Cdn;

use App\Http\Requests\Request;

class CdnPopUpdateRequest extends Request
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
            'pop_hostname' => 'required|unique:cdn_pop,pop_hostname,'.$this->segment(2).',pop_hostname',
            'ip' => 'required',
        ];
    }
}
