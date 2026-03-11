<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    public function authorize()
    {
        return true; // allow all users for now
    }

    public function rules()
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'amount'  => 'required|numeric|min:0.01',
            'note'    => 'nullable|string|max:255',
        ];
    }
}