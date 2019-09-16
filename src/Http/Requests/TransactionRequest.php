<?php

namespace Adams\Przelewy24\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            'p24_merchant_id' => 'required|numeric',
            'p24_pos_id' => 'required|numeric',
            'p24_session_id' => 'required',
            'p24_amount' => 'required|numeric',
            'p24_currency' => 'required',
            'p24_order_id' => 'required|numeric',
            'p24_method' => 'required|numeric',
            'p24_statement' => 'required',
            'p24_sign' => 'required'
        ];
    }
}