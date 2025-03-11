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

    'accepted'             => ':attribute måste accepteras.',
    'active_url'           => ':attribute är inte en giltig URL.',
    'after'                => ':attribute måste vara ett senare datum än :date.',
    'alpha'                => ':attribute får enbart innehålla bokstäver.',
    'alpha_dash'           => ':attribute får bara innehålla bokstäver, siffror och bindestreck.',
    'alpha_num'            => ':attribute får bara innehålla bokstäver och siffror.',
    'array'                => ':attribute måste vara en matris.',
    'before'               => ':attribute måste vara ett datum före :date.',
    'between'              => [
        'numeric' => ':attribute måste vara mellan :min och :max.',
        'file'    => ':attribute måste vara mellan :min och :max kilobyte.',
        'string'  => ':attribute måste vara mellan :min och :max tecken.',
        'array'   => ':attribute måste ha mellan :min and :max objekt.',
    ],
    'boolean'              => ':attribute fältet måste vara sant eller falskt.',
    'confirmed'            => ':attribute bekräftelsen matchar inte.',
    'date'                 => ':attribute är inte ett giltigt datum.',
    'date_format'          => ':attribute matchar inte :format.',
    'different'            => ':attribute och :other får inte vara samma.',
    'digits'               => ':attribute måste vara :digits siffror.',
    'digits_between'       => ':attribute måste vara mellan :min och :max digits.',
    'email'                => ':attribute måste vara en giltig e-postadress.',
    'exists'               => 'Det valda :attribute är ogiltig.',
    'filled'               => ':attribute fältet är obligatoriskt.',
    'image'                => ':attribute måste vara en bild.',
    'in'                   => 'Det valda :attribute är ogilitig.',
    'integer'              => ':attribute måste vara ett heltal.',
    'ip'                   => ':attribute måste vara en giltig IP-adress.',
    'json'                 => ':attribute måste vare en giltig JSON-sträng.',
    'max'                  => [
        'numeric' => ':attribute får inte vara större än :max.',
        'file'    => ':attribute får inte vara större än :max kilobyte.',
        'string'  => ':attribute får inte vara större än :max tecken.',
        'array'   => ':attribute får inte ha fler än :max objekt.',
    ],
    'mimes'                => ':attribute måste vara av filtypen: :values.',
    'min'                  => [
        'numeric' => ':attribute måste vara minst :min.',
        'file'    => ':attribute måste vara minst :min kilobytes.',
        'string'  => ':attribute måste vara minst :min characters.',
        'array'   => ':attribute måste ha minst :min objekt.',
    ],
    'not_in'               => 'Det valda :attribute är ogilitigt.',
    'numeric'              => ':attribute måste vara ett tal.',
    'regex'                => ':attribute formatet är ogiltigt.',
    'required'             => ':attribute fältet är obligatoriskt.',
    'required_if'          => ':attribute fältet är obligatoriskt när :other är :value.',
    'required_with'        => ':attribute fältet är obligatoriskt när :values finns.',
    'required_with_all'    => ':attribute fältet är obligatoriskt när :values finns.',
    'required_without'     => ':attribute fältet är obligatoriskt när :values inte finns.',
    'required_without_all' => ':attribute fältet är obligatoriskt när ingen av :values finns.',
    'same'                 => ':attribute och :other måste vara lika.',
    'size'                 => [
        'numeric' => ':attribute måste vara :size.',
        'file'    => ':attribute måste vara :size kilobytes.',
        'string'  => ':attribute måste vara :size tecken.',
        'array'   => ':attribute måste innehålla :size objekt.',
    ],
    'string'               => ':attribute måste vara en sträng.',
    'timezone'             => ':attribute måste vara en giltig tidszon.',
    'unique'               => ':attribute är redan upptaget.',
    'url'                  => ':attribute format är ogilitigt.',

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

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
