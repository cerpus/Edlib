<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
    'accepted' => ':attribute 가 받아들여져야 합니다.',
    'active_url' => ':attribute 가 유효한 URL이 아닙니다.',
    'after' => ':attribute 가 :date 일 다음이어야 합니다.',
    'alpha' => ':attribute 가 단지 문자만을 포함할 수 있습니다.',
    'alpha_dash' => ':attribute 가 단지 문자, 숫자, 대시만을 포함할 수 있습니다.',
    'alpha_num' => ':attribute 가 단지 문자와 숫자만을 포함할 수 있습니다.',
    'array' => ':attribute 가 배열이여야 합니다.',
    'before' => ':attribute 가 :date 이전 날짜이어야 합니다.',
    'between' => [
        'numeric' => ':attribute 가 :min 와 :max 사이여야 합니다.',
        'file' => ':attribute 가 :min 와 :max 킬로바이트 사이여야 합니다.',
        'string' => ':attribute 가 :min 와 :max 글자 사이여야 합니다.',
        'array' => ':attribute 가 :min 와 :max 항목 사이여야 합니다.',
    ],
    'boolean' => ':attribute 영역은 참 혹은 거짓이어야 합니다.',
    'confirmed' => ':attribute 확인이 일치하지 않습니다.',
    'date' => ':attribute 는 유효한 일자가 아닙니다.',
    'date_format' => ':attribute 이 이전 형식 :format 과 일치하지 않습니다.',
    'different' => ':attribute 와 :other 가 달라야 합니다.',
    'digits' => ':attribute 가 :digits 숫자여야 합니다..',
    'digits_between' => ':attribute 가 :min 와 :max 사이의 숫자여야 합니다.',
    'email' => ':attribute 가 유효한 이메일 주소여야 합니다.',
    'exists' => '선택된 :attribute 가 유효하지 않습니다.',
    'filled' => ':attribute 영역이 필수입니다.',
    'image' => ':attribute 가 이미지이어야 합니다.',
    'in' => '선택된 :attribute 가 유효하지 않습니다.',
    'integer' => ':attribute 가 정수여야 합니다.',
    'ip' => ':attribute 가 유효한 IP 주소이어야 합니다.',
    'ipv4' => ':attribute 가 유효한 IPv4 주소이어야 합니다.',
    'ipv6' => ':attribute 가 유효한 IPv6 주소이어야 합니다.',
    'json' => ':attribute 가 유효한 JSON 문자열이어야 합니다.',
    'max' => [
        'numeric' => ':attribute 가 :max 보다 클 수 없습니다.',
        'file' => ':attribute 가 :max 킬로바이트보다 클 수 없습니다.',
        'string' => ':attribute 가 :max 글자보다 클 수 없습니다.',
        'array' => ':attribute 가 :max 항목 이상이 될 수 없습니다.',
    ],
    'mimes' => ':attribute의 파일 타입은 :values 입니다..',
    'min' => [
        'numeric' => ':attribute 는 최소한 :min 이어야 합니다.',
        'file' => ':attribute 는 최소한 :min 킬로바이트이어야 합니다.',
        'string' => ':attribute 는 최소한 :min 글자여야 합니다.',
        'array' => ':attribute 는 최소한 :min 항목이여야 합니다.',
    ],
    'not_in' => '선택된 :attribute 가 유효하지 않습니다.',
    'numeric' => ':attribute 가 숫자여야 합니다.',
    'regex' => ':attribute 포맷이 유효하지 않습니다.',
    'required' => ':attribute 영역이 필수입니다.',
    'required_if' => ':attribute 영역은 :other 가 :value 값일 때 필수입니다.',
    'required_with' => ':attribute 영역은 :value 값이 존재할 때 필수입니다.',
    'required_with_all' => ':attribute 영역은 :value 값이 존재할 때 필수입니다.',
    'required_without' => ':attribute 영역은 :value 값이 없을 때 필수입니다.',
    'required_without_all' => ':attribute 영역은 :value 값이 없을 때 필수입니다.',
    'same' => ':attribute 와 :other 은 서로 일치하여야 합니다.',
    'size' => [
        'numeric' => ':attribute 는 :size 이어야 합니다.',
        'file' => ':attribute 는 :size 킬로바이트이어야 합니다.',
        'string' => ':attribute 는 :size 글자이어야 합니다.',
        'array' => ':attribute 는 :size 항목을 포함하여야 합니다.',
    ],
    'string' => ':attribute 는 문자열이어야 합니다.',
    'timezone' => ':attribute 는 유효한 영역이어야 합니다.',
    'unique' => ':attribute 는 이미 가져가졌습니다.',
    'url' => ':attribute 포맷이 유효하지 않습니다.',
    'custom' => [
        'attribute-name' => [
            /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */
            'rule-name' => '맞춤 메시지',
        ],
    ],
];
