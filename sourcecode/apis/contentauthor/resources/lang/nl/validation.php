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
    'accepted' => 'Het veld :attribute moet geaccepteerd worden',
    'active_url' => 'De URL is niet geldig',
    'after' => 'De datum moet na :date liggen. ',
    'alpha' => 'Het veld :attribute mag alleen letters bevatten',
    'alpha_dash' => 'Het veld :attribute mag alleen letters, cijfers en streepjes bevatten..',
    'alpha_num' => 'Het veld :attribute mag alleen letters en cijfers bevatten..',
    'array' => 'Het veld :attribute moet een reeks zijn',
    'before' => 'De datum moet voor :date liggen.',
    'between' => [
        'numeric' => 'Het veld :attribute moet tussen :min en :max liggen.',
        'file' => 'Het veld :attribute moet tussen :min en :max kilobytes liggen.',
        'string' => 'Het veld :attribute moet tussen :min en :max karakters liggen.',
        'array' => 'Het veld :attribute moet tussen :min en :max items bevatten.',
    ],
    'boolean' => 'Het veld :attribute moet waar of onwaar zijn',
    'confirmed' => 'De bevestiging van Het veld :attribute komt niet overeen.',
    'date' => 'Het veld :attribute is geen geldige datum.',
    'date_format' => 'Het veld :attribute komt niet overeen met het formaat :format.',
    'different' => 'De velden :attribute en :other moeten verschillend zijn.',
    'digits' => 'Het veld :attribute moet :digits cijfers zijn.',
    'digits_between' => 'Het veld :attribute moet tussen :min en :max cijfers zijn.',
    'email' => 'Het veld :attribute moet een geldig e-mailadres zijn.',
    'exists' => 'Het geselecteerde Het veld :attribute is ongeldig.',
    'filled' => 'Het veld :attribute is verplicht.',
    'image' => 'Het veld :attribute moet een afbeelding zijn.',
    'in' => 'Het geselecteerde veld :attribute is ongeldig.',
    'integer' => 'Het veld :attribute moet een geheel getal zijn.',
    'ip' => 'Het veld :attribute moet een geldige IP-adres zijn.',
    'ipv4' => 'Het veld :attribute moet een geldig IPv4-adres zijn.',
    'ipv6' => 'Het veld :attribute moet een geldig IPv6-adres zijn.',
    'json' => 'Het veld :attribute moet een geldige JSON-string zijn.',
    'max' => [
        'numeric' => 'Het veld :attribute mag niet groter zijn dan :max',
        'file' => 'Het veld :attributemag niet groter zijn dan :max kilobytes.',
        'string' => 'Het veld :attribute mag niet groter zijn dan :max karakters.',
        'array' => 'Het veld :attribute mag niet meer dan :max items bevatten.',
    ],
    'mimes' => 'Het veld :attribute moet een bestand zijn van het type: :values.',
    'min' => [
        'numeric' => 'Het veld :attribute moet minimaal :min zijn.',
        'file' => 'Het veld :attributemoet minimaal :min kilobytes zijn.',
        'string' => 'Het veld :attribute moet minimaal :min karakters zijn.',
        'array' => 'Het veld :attribute moet minimaal :min items bevatten.',
    ],
    'not_in' => 'Het geselecteerde veld :attribute is ongeldig.',
    'numeric' => 'Het veld :attribute moet een nummer zijn.',
    'regex' => 'Het formaat van het veld :attribute is ongeldig.',
    'required' => 'Het veld :attribute is verplicht.',
    'required_if' => 'Het veld :attribute is verplicht wanneer :other :value is.',
    'required_with' => 'Het veld :attribute is verplicht wanneer :values aanwezig is.',
    'required_with_all' => 'Het veld :attribute is verplicht wanneer :values aanwezig is.',
    'required_without' => 'Het veld :attribute is verplicht wanneer :values niet aanwezig is.',
    'required_without_all' => 'Het veld :attribute is verplicht wanneer geen van :values aanwezig is.',
    'same' => 'De velden :attribute en :other moeten overeenkomen.',
    'size' => [
        'numeric' => 'Het veld :attribute moet :size zijn.',
        'file' => 'Het veld :attribute moet :size kilobytes zijn.',
        'string' => 'Het veld :attribute moet :size karakters zijn.',
        'array' => 'Het veld :attribute moet :size items bevatten.',
    ],
    'string' => 'Het veld :attribute moet een string zijn.',
    'timezone' => 'Het veld :attribute moet een geldige tijdzone zijn.',
    'unique' => 'Het veld :attribute is al in gebruik.',
    'url' => 'Het formaat van het veld :attribute is ongeldig.',
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
            'rule-name' => 'custom-message',
        ],
    ],
];
