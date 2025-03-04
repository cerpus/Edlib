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
    'accepted' => ':attribute må aksepteres.',
    'active_url' => ':attribute er ikke en gyldig URL.',
    'after' => ':attribute må være en dato etter :date.',
    'alpha' => ':attribute kan bare inneholde bokstaver.',
    'alpha_dash' => ':attribute kan bare inneholder bokstaver, tall og bindestrek.',
    'alpha_num' => ':attribute kan bare inneholde bokstaver og tall.',
    'array' => ':attribute må være en liste.',
    'before' => ':attribute må være en dato før :date.',
    'between' => [
        'numeric' => ':attribute må være mellom :min og :max.',
        'file' => ':attribute må være mellom :min og :max kilobytes.',
        'string' => ':attribute må være mellom :min og :max bokstaver.',
        'array' => ':attribute må være mellom :min og :max elementer.',
    ],
    'boolean' => ':attribute feltet må være true eller false.',
    'confirmed' => ':attribute bekreftelse passer ikke.',
    'date' => ':attribute er ikke en gyldig dato.',
    'date_format' => ':attribute passer ikke formatet :format.',
    'different' => ':attribute og :other må være forskjellige.',
    'digits' => ':attribute må være :digits tall.',
    'digits_between' => ':attribute må være mellom :min og :max digits.',
    'email' => ':attribute må være en gyldig epostadresse.',
    'exists' => 'Den valgte :attribute er ugyldig.',
    'filled' => ':attribute feltet er påkrevd.',
    'image' => ':attribute må være et bilde.',
    'in' => 'Den valgte :attribute er ugyldig.',
    'integer' => ':attribute må være et heltall.',
    'ip' => ':attribute må være en gyldig IP adresse.',
    'ipv4' => ':attribute må være en gyldig IPv4 adresse.',
    'ipv6' => ':attribute må være en gyldig IPv6 adresse.',
    'json' => ':attribute må være en gyldig JSON streng.',
    'max' => [
        'numeric' => ':attribute kan ikke være større enn :max.',
        'file' => ':attribute kan ikke være større enn :max kilobytes.',
        'string' => ':attribute kan ikke være større enn :max bokstaver.',
        'array' => ':attribute kan ikke ha mer enn :max enheter.',
    ],
    'mimes' => ':attribute må være en fil av typen: :values.',
    'min' => [
        'numeric' => ':attribute må være minst :min.',
        'file' => ':attribute må være minst :min kilobytes.',
        'string' => ':attribute må være minst :min bokstaver.',
        'array' => ':attribute må være minst :min elementer.',
    ],
    'not_in' => 'Den valgte :attribute er ugyldig.',
    'numeric' => ':attribute må være et tall.',
    'regex' => ':attribute formatet er ugyldig.',
    'required' => ':attribute er påkrevd.',
    'required_if' => ':attribute feltet er påkrevd når :other er :value.',
    'required_with' => ':attribute feltet er påkrevd når :values er tilstede.',
    'required_with_all' => ':attribute feltet er påkrevd når :values er tilstede.',
    'required_without' => ':attribute feltet er påkrevd når :values ikke er tilstede.',
    'required_without_all' => ':attribute feltet er påkrevd når ingen av :values er tilstede.',
    'same' => ':attribute og :other må være like.',
    'size' => [
        'numeric' => ':attribute må være :size.',
        'file' => ':attribute må være :size kilobytes.',
        'string' => ':attribute må være :size bokstaver.',
        'array' => ':attribute må inneholde :size enheter.',
    ],
    'string' => ':attribute må være en streng.',
    'timezone' => ':attribute må være en gyldig tidssone.',
    'unique' => ':attribute er allerede opptatt.',
    'url' => ':attribute formatet er ugyldig.',
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
            'rule-name' => 'tilpasset melding',
        ],
    ],
    'attributes' => [
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
        'title' => 'Tittel',
        'content' => 'innhold',
    ],
];
