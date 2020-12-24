<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class OauthRequest extends FormRequest
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
        $name = Request::route()->getName();
        if ($name == "api.login" || $name == "api.register") {
            return [
                "account" => 'required|mobile',
                "password" => 'required'
            ];
        }

        return [];
    }

    public function messages()
    {
        return [
            'account.mobile' => '无效的手机号'
        ];
    }

    public function attributes()
    {
        return [
            'account' => '手机号'
        ];
    }
}
