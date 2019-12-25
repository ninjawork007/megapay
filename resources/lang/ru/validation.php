<?php
return [
    'check_voucher_code'            => 'Извините, неверный код ваучера!',
    'alpha_spaces'                  => 'У :attribute должны быть пробелы и символы',
    'default_wallet_balance'        => 'Извините, недостаточно средств для выполнения операции',
    'check_wallet_balance'          => 'Извините, недостаточно средств для выполнения операции',
    'accepted'                      => ':attribute должен быть принят.',
    'active_url'                    => ':attribute не является допустимым URL.',
    'after'                         => ':attribute должен быть датой после :date.',
    'after_or_equal'                => ':attribute должен быть датой после или равной :date.',
    'alpha'                         => ':attribute могут содержать только буквы.',
    'alpha_dash'                    => ':attribute может содержать только буквы, цифры и тире.',
    'alpha_num'                     => ':attribute может содержать только буквы и цифры.',
    'array'                         => ':attribute должен быть массивом.',
    'before'                        => ':attribute должна быть датой перед :date.',
    'before_or_equal'               => ':attribute должна быть датой до или равной :date.',

    'between'                       => [
        'numeric' => ':attribute должен находиться между :min и :max.',
        'file'    => ':attribute должен находиться между :min и :max килобайтами.',
        'string'  => ':attribute должен находиться между :min и :max символами.',
        'array'   => ':attribute должен находиться между :min и :max элементами.',

    ],
    'boolean'                       => 'Поле :attribute должно быть истинным или ложным.',
    'confirmed'                     => 'Подтверждение :attribute не соответствует.',
    'date'                          => ':attribute не является допустимой датой.',
    'date_format'                   => ':attribute не соответствует формату :format.',
    'different'                     => ':attribute и :other должны быть разными.',
    'digits'                        => ':attribute должно быть :digits цифр.',
    'digits_between'                => ':attribute должен находиться между :min и :max цифрами.',
    'dimensions'                    => ':attribute имеет недопустимые размеры изображения.',
    'distinct'                      => 'Поле :attribute имеет двойное значение.',
    'email'                         => ':attribute должен быть действительным адресом электронной почты.',
    'exists'                        => 'Выбранный :attribute недействителен.',
    'file'                          => ':attribute должен быть файлом.',
    'filled'                        => 'Поле :attribute должно иметь значение.',
    'image'                         => ':attribute должен быть изображением.',
    'in'                            => 'Выбранный :attribute недействителен.',
    'in_array'                      => 'Поле :attribute не существует в :other.',
    'integer'                       => 'Значение :attribute должно быть целым числом.',
    'ip'                            => ':attribute должен быть действительным IP-адресом.',
    'ipv4'                          => ':attribute должен быть действительным адресом IPv4.',
    'ipv6'                          => ':attribute должен быть действительным адресом IPv6.',
    'json'                          => ':attribute должна быть допустимой строкой JSON.',

    'max'                           => [
        'numeric' => 'Значение :attribute не может превышать :max.',
        'file'    => 'Значение :attribute не может превышать :max килобайт.',
        'string'  => 'Значение :attribute не может превышать :max символов.',
        'array'   => 'У :attribute может быть не более :max элементов.',

    ],
    'mimes'                         => ':attribute должен быть файлом типа: :values.',
    'mimetypes'                     => ':attribute должен быть файлом типа: :values.',

    'min'                           => [
        'numeric' => 'Значение :attribute должно быть не менее :min.',
        'file'    => 'Значение :attribute должно быть не менее :min килобайт.',
        'string'  => 'Значение :attribute должно быть не менее :min символов.',
        'array'   => 'У :attribute должно быть не менее :min элементов.',

    ],
    'not_in'                        => 'Выбранный :attribute недействителен.',
    'numeric'                       => ':attribute должен быть числом.',
    'present'                       => 'Должно присутствовать поле :attribute.',
    'regex'                         => 'Формат :attribute недействителен.',
    'required'                      => 'Поле :attribute требуется.',
    'required_if'                   => 'Поле :attribute требуется, если :other является :value.',
    'required_unless'               => 'Поле :attribute требуется, если :other не находится в :values.',
    'required_with'                 => 'Поле :attribute требуется, когда :values присутствует.',
    'required_with_all'             => 'Поле :attribute требуется, когда :values присутствует.',
    'required_without'              => 'Поле :attribute требуется, если :values нет.',
    'required_without_all'          => 'Поле :attribute требуется, если ни один из :values не присутствует.',
    'same'                          => ':attribute и :other должны совпадать.',

    'size'                          => [
        'numeric' => ':attribute должен быть :size.',
        'file'    => ':attribute должен быть :size килобайт.',
        'string'  => ':attribute должен быть :size символами.',
        'array'   => ':attribute должен содержать :size элементы.',

    ],
    'string'                        => ':attribute должен быть строкой.',
    'timezone'                      => ':attribute должен быть допустимой зоной.',
    'unique'                        => ':attribute уже принято.',
    'uploaded'                      => ':attribute не удалось загрузить.',
    'url'                           => 'Формат :attribute недействителен.',
    'unique_merchant_business_name' => ':attribute должен быть уникальным',

    'custom'                        => [
        'attribute-name' => [
            'rule-name' => 'на заказ сообщения',
        ],
    ],
    'attributes'                    => [
    ],
];
