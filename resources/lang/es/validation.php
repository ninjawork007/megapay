<?php
return [
    'check_voucher_code'            => 'Lo sentimos, código de cupón incorrecto!',
    'alpha_spaces'                  => 'El :attribute debe tener espacios y caracteres',
    'default_wallet_balance'        => 'Lo sentimos, no hay fondos suficientes para realizar la operación',
    'check_wallet_balance'          => 'Lo sentimos, no hay fondos suficientes para realizar la operación',
    'accepted'                      => 'El :attribute debe ser aceptado.',
    'active_url'                    => 'El :attribute no es una URL válida.',
    'after'                         => 'El :attribute debe ser una fecha después de :date.',
    'after_or_equal'                => 'El :attribute debe ser una fecha posterior o igual a :date.',
    'alpha'                         => 'El :attribute solo puede contener letras.',
    'alpha_dash'                    => 'El :attribute solo puede contener letras, números y guiones.',
    'alpha_num'                     => 'El :attribute solo puede contener letras y números.',
    'array'                         => 'El :attribute debe ser una matriz.',
    'before'                        => 'El :attribute debe ser una fecha anterior a :date.',
    'before_or_equal'               => 'El :attribute debe ser una fecha anterior o igual a :date.',

    'between'                       => [
        'numeric' => 'El :attribute debe estar entre :min y :max.',
        'file'    => 'El :attribute debe estar entre :min y :max kilobytes.',
        'string'  => 'El :attribute debe tener entre :min y :max caracteres.',
        'array'   => 'El :attribute debe tener entre :min y :max elementos.',

    ],
    'boolean'                       => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed'                     => 'La confirmación :attribute no coincide.',
    'date'                          => 'El :attribute no es una fecha válida.',
    'date_format'                   => 'El :attribute no coincide con el formato :format.',
    'different'                     => 'El :attribute y :other deben ser diferentes.',
    'digits'                        => 'El :attribute debe ser :digits dígitos.',
    'digits_between'                => 'El :attribute debe tener entre :min y :max dígitos.',
    'dimensions'                    => 'El :attribute tiene dimensiones de imagen no válidas.',
    'distinct'                      => 'El campo :attribute tiene un valor duplicado.',
    'email'                         => 'El :attribute debe ser una dirección de correo electrónico válida.',
    'exists'                        => 'El :attribute seleccionado no es válido.',
    'file'                          => 'El :attribute debe ser un archivo.',
    'filled'                        => 'El campo :attribute debe tener un valor.',
    'image'                         => 'El :attribute debe ser una imagen.',
    'in'                            => 'El :attribute seleccionado no es válido.',
    'in_array'                      => 'El campo :attribute no existe en :other.',
    'integer'                       => 'El :attribute debe ser un número entero.',
    'ip'                            => 'El :attribute debe ser una dirección IP válida.',
    'ipv4'                          => 'El :attribute debe ser una dirección IPv4 válida.',
    'ipv6'                          => 'El :attribute debe ser una dirección IPv6 válida.',
    'json'                          => 'El :attribute debe ser una cadena JSON válida.',

    'max'                           => [
        'numeric' => 'El :attribute no puede ser mayor que :max.',
        'file'    => 'El :attribute no puede ser mayor que :max kilobytes.',
        'string'  => 'El :attribute no puede ser mayor que :max caracteres.',
        'array'   => 'El :attribute no puede tener más de :max elementos.',

    ],
    'mimes'                         => 'El :attribute debe ser un archivo de tipo: :values.',
    'mimetypes'                     => 'El :attribute debe ser un archivo de tipo: :values.',

    'min'                           => [
        'numeric' => 'El :attribute debe ser al menos :min.',
        'file'    => 'El :attribute debe ser al menos :min kilobytes.',
        'string'  => 'El :attribute debe tener al menos :min caracteres.',
        'array'   => 'El :attribute debe tener al menos :min elementos.',

    ],
    'not_in'                        => 'El :attribute seleccionado no es válido.',
    'numeric'                       => 'El :attribute debe ser un número.',
    'present'                       => 'El campo :attribute debe estar presente.',
    'regex'                         => 'El formato :attribute no es válido.',
    'required'                      => 'El campo es obligatorio.',
    'required_if'                   => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_unless'               => 'El campo :attribute es obligatorio a menos que :other esté en :value.',
    'required_with'                 => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all'             => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_without'              => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all'          => 'El campo :attribute es obligatorio cuando ninguno de :values está presente.',
    'same'                          => 'El :attribute y :other deben coincidir.',

    'size'                          => [
        'numeric' => 'El :attribute debe ser :size.',
        'file'    => 'El :attribute debe ser :size kilobytes.',
        'string'  => 'Los :attribute deben ser :size caracteres.',
        'array'   => 'El :attribute debe contener :size elementos.',

    ], 'string' => 'El :attribute debe ser una cadena.',
    'timezone'                      => 'El :attribute debe ser una zona válida.',
    'unique'                        => 'El :attribute ya ha sido tomado.',
    'uploaded'                      => 'El :attribute no pudo cargarse.',
    'url'                           => 'El formato :attribute no es válido.',
    'unique_merchant_business_name' => 'El :attribute debe ser único',

    'custom'                        => [

        'attribute-name' => [
            'rule-name' => 'mensaje personalizado',

        ],
    ],
    'attributes'                    => [

    ]];
