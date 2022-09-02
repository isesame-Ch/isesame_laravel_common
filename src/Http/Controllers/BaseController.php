<?php

namespace Isesame\IsesameLaravelCommon\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BaseController extends \Illuminate\Routing\Controller
{
    protected $validateMessages = [
        'required' => ':attribute 不能为空',
        'max' => ':attribute 超出允许最大值',
        'min' => ':attribute 超出允许最小值',
        'in' => ':attribute 无效',
        'numeric' => ':attribute 须要为数值',
        'array' => ':attribute 期望值为数组',
        'date_format' => ':attribute 时间格式为 2022-07-12',
    ];

    private $statusCode = 200;
    private $code = 0;
    private $message = '';
    private $content;
    private $contentEncrypt;


    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode = 0)
    {
        $this->statusCode = (int)$statusCode;
        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code = 0)
    {
        $this->code = (int)$code;
        return $this;
    }

    public function getMessage()
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'content' => $this->content ? $this->content : '',
            'contentEncrypt' => $this->contentEncrypt ? $this->contentEncrypt : ''
        ];
    }

    public function setMessage($message = '')
    {
        $this->message = trim($message);
        return $this;
    }

    public function setError($message = '', $code = '')
    {
        $this->setMessage($message)->setCode($code);
        return $this;
    }

    public function setKeyContent( $value = '')
    {
        $this->content = $value;
        return $this;
    }

    public function response()
    {
        return response()
            ->json($this->getMessage())
            ->setEncodingOptions(
                env('APP_ENV') == 'prod'
                    ? JSON_FORCE_OBJECT
                    : JSON_FORCE_OBJECT
                    | JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_PRETTY_PRINT
            )/*
			->withHeaders([
				'Access-Control-Allow-Origin' => '*'
			])*/;
    }

    public function responseJson()
    {
        return response()
            ->json($this->getMessage())
            ->setEncodingOptions(
                env('APP_ENV') == 'prod'
                    ? JSON_OBJECT_AS_ARRAY
                    : JSON_OBJECT_AS_ARRAY
                    | JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_PRETTY_PRINT
            )/*
			->withHeaders([
				'Access-Control-Allow-Origin' => '*'
			])*/;
    }

    /**
     * @param Request $request
     * @param $rules
     * @param bool $special_treatment
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function filterParams(Request $request, $rules, $attribute = [],$special_treatment = true)
    {
        $data = $request->all();
        $validator = Validator::make(
            $data,
            $rules,
            $attribute
        );
        $vali = $validator->fails();
        if($vali) {
            throw new \Exception(json_encode($validator->errors()->messages()),'301');

        }

        $params = $request->only(array_keys($rules));
        $params = array_filter($params, function($value) use ($special_treatment) {
            return is_array($value) || $special_treatment && (string)$value === '0' || $value == true;
        }, ARRAY_FILTER_USE_BOTH);

        return $params;
    }
}