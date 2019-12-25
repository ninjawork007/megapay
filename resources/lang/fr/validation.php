<?php
return [
    'check_voucher_code'            => 'Désolé, code de bon de commande incorrect!',
    'alpha_spaces'                  => 'Le :attribute doit avoir des espaces et des caractères',
    'default_wallet_balance'        => 'Désolé, pas assez de fonds pour effectuer l\'opération',
    'check_wallet_balance'          => 'Désolé, pas assez de fonds pour effectuer l\'opération',
    'accepted'                      => 'Le :attribute doit être accepté.',
    'active_url'                    => 'Le :attribute n\'est pas une URL valide.',
    'after'                         => 'Le :attribute doit être une date après :date.',
    'after_or_equal'                => 'Le :attribute doit être une date après ou égale à :date.',
    'alpha'                         => 'Le :attribute ne peut contenir que des lettres.',
    'alpha_dash'                    => 'Le :attribute ne peut contenir que des lettres, des chiffres et des tirets.',
    'alpha_num'                     => 'Le :attribute ne peut contenir que des lettres et des chiffres.',
    'array'                         => 'Le :attribute doit être un tableau.',
    'before'                        => 'Le :attribute doit être une date antérieure à :date.',
    'before_or_equal'               => 'Le :attribute doit être une date antérieure ou égale à :date.',

    'between'                       => [
        'numeric' => 'Le :attribute doit être entre :min et :max.',
        'file'    => 'Le :attribute doit être compris entre :min et :max kilo-octets.',
        'string'  => 'Le :attribute doit être entre :min et :max caractères.',
        'array'   => 'Le :attribute doit contenir entre :min et :max éléments.',

    ],
    'boolean'                       => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed'                     => 'La confirmation :attribute ne correspond pas.',
    'date'                          => 'Le :attribute n\'est pas une date valide.',
    'date_format'                   => 'Le :attribute ne correspond pas au format :format.',
    'different'                     => 'Les :attribute et :other doivent être différents.',
    'digits'                        => 'Le :attribute doit être :digits chiffres.',
    'digits_between'                => 'Le :attribute doit être entre :min et :max chiffres.',
    'dimensions'                    => 'Le :attribute a des dimensions d\'image invalides.',
    'distinct'                      => 'Le champ :attribute a une valeur en double.',
    'email'                         => 'Le :attribute doit être une adresse e-mail valide.',
    'exists'                        => 'Le :attribute sélectionné n\'est pas valide.',
    'file'                          => 'Le :attribute doit être un fichier.',
    'filled'                        => 'Le champ :attribute doit avoir une valeur.',
    'image'                         => 'Le :attribute doit être une image.',
    'in'                            => 'Le :attribute sélectionné n\'est pas valide.',
    'in_array'                      => 'Le champ :attribute n\'existe pas dans :other.',
    'integer'                       => 'Le :attribute doit être un nombre entier.',
    'ip'                            => 'Le :attribute doit être une adresse IP valide.',
    'ipv4'                          => 'Le :attribute doit être une adresse IPv4 valide.',
    'ipv6'                          => 'Le :attribute doit être une adresse IPv6 valide.',
    'json'                          => 'Le :attribute doit être une chaîne JSON valide.',

    'max'                           => [
        'numeric' => 'Le :attribute ne peut pas être supérieur à :max.',
        'file'    => 'Le :attribute ne peut pas être supérieur à :max kilo-octets.',
        'string'  => 'Le :attribute ne peut pas être supérieur à :max caractères.',
        'array'   => 'Le :attribute ne doit pas contenir plus de :max éléments.',

    ],
    'mimes'                         => 'Le :attribute doit être un fichier de type: :values.',
    'mimetypes'                     => 'Le :attribute doit être un fichier de type: :values.',

    'min'                           => [
        'numeric' => 'Le :attribute doit être au moins :min.',
        'file'    => 'Le :attribute doit avoir au moins :min kilo-octets.',
        'string'  => 'Le :attribute doit avoir au moins :min caractères.',
        'array'   => 'Le :attribute doit avoir au moins :min éléments.',

    ],
    'not_in'                        => 'Le :attribute sélectionné n\'est pas valide.',
    'numeric'                       => 'Le :attribute doit être un nombre.',
    'present'                       => 'Le champ :attribute doit être présent.',
    'regex'                         => 'Le format :attribute est invalide.',
    'required'                      => 'Ce champ est requis.',
    'required_if'                   => 'Le champ :attribute est requis lorsque :other est :value.',
    'required_unless'               => 'Le champ :attribute est requis à moins que :other soit dans :values.',
    'required_with'                 => 'Le champ :attribute est requis lorsque :values est présent.',
    'required_with_all'             => 'Le champ :attribute est requis lorsque :values est présent.',
    'required_without'              => 'Le champ :attribute est requis lorsque :values n\'est pas présent.',
    'required_without_all'          => 'Le champ :attribute est requis quand aucun des :values n\'est présent.',
    'same'                          => 'Les :attribute et :other doivent correspondre.',

    'size'                          => [
        'numeric' => 'Le :attribute doit être :size.',
        'file'    => 'Le :attribute doit être de :size kilo-octets.',
        'string'  => 'Le :attribute doit être :size caractères.',
        'array'   => 'Le :attribute doit contenir :size items.',

    ],
    'string'                        => 'Le :attribute doit être une chaîne.',
    'timezone'                      => 'Le :attribute doit être une zone valide.',
    'unique'                        => 'Le :attribute a déjà été pris.',
    'uploaded'                      => 'Le :attribute n\'a pas pu être téléchargé.',
    'url'                           => 'Le format :attribute est invalide.',
    'unique_merchant_business_name' => 'Le :attribute doit être unique',

    'custom'                        => [
        'attribute-name' => [
            'rule-name' => 'message personnalisé',
        ],
    ],
    'attributes'                    => [
    ]];
