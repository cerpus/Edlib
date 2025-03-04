<?php
return [
    'size' => [
        'string' => ':attribute må vere :size bokstavar.',
        'numeric' => ':attribute må vere :size.',
        'file' => ':attribute må vere :size kilobytar.',
        'array' => ':attribute må innehalde :sixe gjenstandar.',
    ],
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
            'rule-name' => 'tilpassa melding',
        ],
    ],
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
    'accepted' => ':attribute må bli godkjend.',
    'active_url' => ':attribute er ikkje ein godkjend URL.',
    'after' => ':attribute må vere ein dato seinare enn :date.',
    'alpha' => ':attribute kan berre innehalde bokstavar.',
    'alpha_dash' => ':attribute kan berre innehalde bokstavar, tal og bindestrekar.',
    'alpha_num' => ':attribute kan berre innehalde bokstavar og tal.',
    'array' => ':attribute må vere ein tabell.',
    'before' => ':attribute må vere ein dato tidligare enn :date.',
    'between' => [
        'numeric' => ':attribute må vere mellom :min og :max.',
        'file' => ':attribute må vere mellom :min og :max kilobytar.',
        'string' => ':attribute må vere mellom :min og :max bokstavar.',
        'array' => ':attribute må har vore mellom :min og :max gjenstandar.',
    ],
    'boolean' => ':attribute -feltet må vere sant eller usant.',
    'confirmed' => ':attribute bekreftinga stemmer ikkje.',
    'date' => ':attribute er ikkje ein gyldig dato.',
    'date_format' => ':attribute har ikkje formatet :format.',
    'different' => ':attribute og :other må vere ulik.',
    'digits_between' => ':attribute må vere mellom :min og :max siffer.',
    'digits' => ':attribute må vere :digits siffer.',
    'email' => ':attribute må vere ei gyldig e-postadresse.',
    'exists' => 'Den valde :attribute er ikkje gyldig.',
    'filled' => ':attribute -feltet er påkravd.',
    'image' => ':attribute må vere eit bilete.',
    'in' => 'Valde :attribute er ikkje gyldig.',
    'integer' => ':attribute må vere eit heiltal.',
    'ip' => ':attribute må ha ein gyldig IP-adresse.',
    'ipv4' => ':attribute må vere ein gyldig IPv4-adresse.',
    'ipv6' => ':attribute må vere ein gyldig IPv6-adresse.',
    'json' => ':attribute må vere ein gyldig JSON-streng.',
    'max' => [
        'numeric' => ':attribute kan ikkje vere større enn :max.',
        'file' => ':attribute kan ikkje vere større enn :max kilobytar.',
        'string' => ':attribute kan ikkje vere fleir enn :max bokstavar.',
        'array' => ':attribute kan ikkje innehalde meir enn :max gjenstandar.',
    ],
    'mimes' => ':attribute må vere ei fil av typen :values.',
    'min' => [
        'numeric' => ':attribute må vere minst :min.',
        'file' => ':attribute må vere minst :min kilobytar.',
        'array' => ':attribute må ha minst :min gjenstandar.',
        'string' => ':attribute må vere minst :min bokstavar.',
    ],
    'not_in' => 'Valde :attribute er ikkje gyldig.',
    'numeric' => ':attribute må vere eit tal.',
    'regex' => ':attribute er eit ugyldig format.',
    'required' => ':attribute er eit påkravd felt.',
    'required_if' => 'Feltet :attribute er påkravd når :other er :value.',
    'required_with' => 'Feltet :attribute er påkravd når :values finst.',
    'required_with_all' => 'Feltet :attribute er påkravd når :values finst.',
    'required_without' => 'Feltet :attribute er påkravd når :values ikkje finst.',
    'required_without_all' => 'Feltet :attribute er påkravd når nokon av :values finst.',
    'same' => ':attribute og :other må samsvare.',
    'string' => ':attribute må vere ein streng.',
    'timezone' => ':attribute må vere ein gyldig sone.',
    'unique' => ':attribute er allerede i bruk.',
    'url' => 'Formatet :attribute er gyldig.',
];
