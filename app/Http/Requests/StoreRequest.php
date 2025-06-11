<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255|',
            'last_name' => 'required|string|max:255|',
            'email' => 'required|email|max:255|',
            'phone' => 'required|string|max:255|',
            'age' => 'required|integer|between:1,100',
            'male' => 'required|string',
        ];
    }

}
