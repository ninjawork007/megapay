<?php
return [
    'check_voucher_code'            => 'Üzgünüz, yanlış kupon kodu!',
    'alpha_spaces'                  => ':attribute Boşluk ve karakter içermelidir',
    'default_wallet_balance'        => 'Maalesef işlemi gerçekleştirmek için yeterli para yok',
    'check_wallet_balance'          => 'Maalesef işlemi gerçekleştirmek için yeterli para yok',
    'accepted'                      => ':attribute Kabul edilmeli.',
    'active_url'                    => ':attribute Geçerli bir URL değil.',
    'after'                         => ':attribute :date sonra bir tarih olmalı.',
    'after_or_equal'                => ':attribute, :date sonra veya ona eşit bir tarih olmalı.',
    'alpha'                         => ':attribute Sadece harf içerebilir.',
    'alpha_dash'                    => ':attribute Sadece harf, sayı ve tire içerebilir.',
    'alpha_num'                     => ':attribute Sadece harf ve rakam içerebilir.',
    'array'                         => ':attribute Bir dizi olmalı.',
    'before'                        => ':attribute :date önce bir tarih olmalı.',
    'before_or_equal'               => ':attribute, Daha önce veya ona eşit bir tarih olmalı.',

    'between'                       => [
        'numeric' => ':attribute, :min ile :max arasında olmalıdır.',
        'file'    => ':attribute, :min ile :max kilobayt arasında olmalıdır.',
        'string'  => ':attribute, :min ile :max karakter arasında olmalıdır.',
        'array'   => ':attribute, :min ile :max eşya arasında olmalıdır.',

    ],
    'boolean'                       => ':attribute Alanı doğru veya yanlış olmalı.',
    'confirmed'                     => ':attribute Onayı uyuşmuyor.',
    'date'                          => ':attribute Geçerli bir tarih değil.',
    'date_format'                   => ':attribute, :format Biçimine uymuyor.',
    'different'                     => ':attribute ve :other farklı olmalı.',
    'digits'                        => ':attribute :digits basamaklı olmalı.',
    'digits_between'                => ':attribute :min ile :max rakam arasında olmalıdır.',
    'dimensions'                    => ':attribute geçersiz resim boyutları var.',
    'distinct'                      => ':attribute alanında yinelenen bir değer var.',
    'email'                         => ':attribute geçerli bir e-posta adresi olmalı.',
    'exists'                        => 'Seçilen :attribute geçersiz.',
    'file'                          => ':attribute bir dosya olmalı.',
    'filled'                        => ':attribute alanının bir değeri olmalı.',
    'image'                         => ':attribute bir görüntü olmalı.',
    'in'                            => 'Seçilen :attribute geçersiz.',
    'in_array'                      => ':attribute alanı :other \'de mevcut değil.',
    'integer'                       => ':attribute bir tamsayı olmalı.',
    'ip'                            => ':attribute geçerli bir IP adresi olmalı.',
    'ipv4'                          => ':attribute geçerli bir IPv4 adresi olmalı.',
    'ipv6'                          => ':attribute geçerli bir IPv6 adresi olmalı.',
    'json'                          => ':attribute geçerli bir JSON dizesi olmalı.',

    'max'                           => [
        'numeric' => ':attribute, :max büyük olamaz.',
        'file'    => ':attribute :max kilobayttan daha büyük olmayabilir.',
        'string'  => ':attribute, :max karakterden büyük olamaz.',
        'array'   => ':attribute \'den fazla :max eşya olmayabilir.',

    ],
    'mimes'                         => ':attribute bir dosya türü olmalıdır: :values.',
    'mimetypes'                     => ':attribute bir dosya türü olmalıdır: :values.',

    'min'                           => [
        'numeric' => ':attribute en azından :min olmalı.',
        'file'    => ':attribute en az kilo kilo olmalı.',//
        'string'  => ':attribute en az :min karakter olmalı.',
        'array'   => ':attribute en azından :min öğelere sahip olmalıdır.',

    ],
    'not_in'                        => 'Seçilen :attribute geçersiz.',
    'numeric'                       => ':attribute bir sayı olmalı.',
    'present'                       => ':attribute alanı mevcut olmalı.',
    'regex'                         => ':attribute biçimi geçersiz.',
    'required'                      => 'Alan zorunludur.',
    'required_if'                   => ':other, :value olduğunda :attribute alanı gereklidir.',
    'required_unless'               => ':other, :value olmadığı sürece :attribute alanı gereklidir.',
    'required_with'                 => ':values olduğunda, :attribute alanı gereklidir.',
    'required_with_all'             => ':values olduğunda, :attribute alanı gereklidir.',
    'required_without'              => ':values olmadığında :attribute alanı gereklidir.',
    'required_without_all'          => ':values = yokken :attribute alanı gereklidir.',
    'same'                          => ':attribute ve :other eşleşmeli.',

    'size'                          => [
        'numeric' => ':attribute :size olmalı.',
        'file'    => ':attribute :size kilobayt olmalı.',
        'string'  => ':attribute :size karakter olmalı.',
        'array'   => ':attribute, :size öğeleri içermelidir.',

    ],
    'string'                        => ':attribute bir dize olmalı.',
    'timezone'                      => ':attribute geçerli bir bölge olmalı.',
    'unique'                        => ':attribute çoktan alınmış.',
    'uploaded'                      => ':attribute yüklenemedi.',
    'url'                           => ':attribute biçimi geçersiz.',
    'unique_merchant_business_name' => ':attribute benzersiz olmalı',

    'custom'                        => [
        'attribute-name' => [
            'rule-name' => 'özel mesaj',
        ],
    ],
    'attributes'                    => [
    ],
];
