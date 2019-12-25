<?php
return [
    'check_voucher_code'            => 'عذرًا ، رمز القسيمة غير صحيح!',
    'alpha_spaces'                  => 'يجب أن تحتوي  :attribute على فراغات وأحرف',
    'default_wallet_balance'        => 'عذرًا ، لا توجد أموال كافية لإجراء العملية',
    'check_wallet_balance'          => 'عذرًا ، لا توجد أموال كافية لإجراء العملية',
    'accepted'                      => 'يجب قبول  :attribute.',
    'active_url'                    => ':attribute ليس عنوان  URL الحًا.',
    'after'                         => 'يجب أن يكون تاريخ  :attribute بعد  :date.',
    'after_or_equal'                => 'يجب أن يكون  :attribute تاريخًا بعد أو يساوي  :date.',
    'alpha'                         => 'قد يحتوي  :attribute على أحرف فقط.',
    'alpha_dash'                    => 'قد تحتوي  :attribute على أحرف وأرقام وشرطات فقط.',
    'alpha_num'                     => 'لا يجوز أن تحتوي  :attribute إلا على أحرف وأرقام.',
    'array'                         => 'يجب أن يكون  :attribute صفيف.',
    'before'                        => 'يجب أن يكون  :attribute تاريخًا قبل  :date.',
    'before_or_equal'               => 'يجب أن يكون  :attribute تاريخًا قبل أو يساوي  :date.',

    'between'                       =>
    [
        'numeric' => 'يجب أن يكون  :attribute بين  :min و  :max.',
        'file'    => 'يجب أن يكون  :attribute بين  :min و  :max كيلوبايت.',
        'string'  => 'يجب أن يكون  :attribute بين  :min و  :max حرفًا.',
        'array'   => 'يجب أن يكون  :attribute بين  :min و  :max عناصر.',

    ],
    'boolean'                       => 'يجب أن يكون حقل  :attribute صحيحًا أو خطأ.',
    'confirmed'                     => 'تأكيد  :attribute غير متطابق.',
    'date'                          => 'ال :attribute ليس تاريخًا صالحًا.',
    'date_format'                   => 'لا يتطابق  :attribute مع التنسيق  :format.',
    'different'                     => 'يجب أن يكون  :attribute و  :other مختلفين.',
    'digits'                        => 'يجب أن تكون  :attribute أرقام.',
    'digits_between'                => 'يجب أن يكون  :attribute بين :min و :max أرقام.',
    'dimensions'                    => 'يحتوي  :attribute على أبعاد صور غير صالحة.',
    'distinct'                      => 'يحتوي الحقل  :attribute على قيمة مكررة.',
    'email'                         => 'يجب أن يكون  :attribute عنوان بريد إلكتروني صالحًا.',
    'exists'                        => 'المحدد  :attribute غير صالح.',
    'file'                          => 'يجب أن يكون  :attribute ملفًا.',
    'filled'                        => 'يجب أن يحتوي الحقل  :attribute على قيمة.',
    'image'                         => 'يجب أن تكون  :attribute صورة.',
    'in'                            => 'المحدد  :attribute غير صالح.',
    'in_array'                      => 'الحقل  :attribute غير موجود في  :other.',
    'integer'                       => 'يجب أن يكون  :attribute عددًا صحيحًا.',
    'ip'                            => 'يجب أن يكون  :attribute عنوان  IP صالحًا.',
    'ipv4'                          => 'يجب أن يكون  :attribute عنوان  IPv4 صالحًا.',
    'ipv6'                          => 'يجب أن يكون  :attribute عنوان  IPv6 صالحًا.',
    'json'                          => 'يجب أن تكون  :attribute عبارة عن سلسلة JSON صالحة.',

    'max'                           => [
        'numeric' => 'قد لا يكون  :attribute أكبر من :max.',
        'file'    => 'قد لا يكون  :attribute أكبر من :max كيلوبايت.',
        'string'  => 'قد لا يكون  :attribute أكبر من :max حرف.',
        'array'   => 'قد لا تحتوي  :attribute على أكثر من :max عناصر.',

    ], 'mimes'  => 'يجب أن يكون  :attribute ملف من نوع: :values.',
    'mimetypes'                     => 'يجب أن يكون  :attribute ملف من نوع: :values.',

    'min'                           => [
        'numeric' => 'يجب أن يكون  :attribute على الأقل  :min.',
        'file'    => 'يجب أن يكون  :attribute على الأقل  :min كيلوبايت.',
        'string'  => 'يجب أن يكون  :attribute على الأقل  :min حرفًا.',
        'array'   => 'يجب أن يكون  :attribute على الأقل  :min عناصر.',

    ], 'not_in' => 'المحدد  :attribute غير صالح.',
    'numeric'                       => 'يجب أن يكون  :attribute رقمًا.',
    'present'                       => 'يجب أن يكون الحقل  :attribute موجودًا.',
    'regex'                         => 'التنسيق  :attribute غير صالح.',
    'required'                      => 'مطلوب هذا المجال.',
    'required_if'                   => 'الحقل  :attribute مطلوب عندما تكون  :other هي: :value.',
    'required_unless'               => 'الحقل  :attribute مطلوب ما لم يكن  :other في  :values.',
    'required_with'                 => 'الحقل  :attribute مطلوب عند وجود :values.',
    'required_with_all'             => 'الحقل  :attribute مطلوب عند وجود :values.',
    'required_without'              => 'الحقل  :attribute مطلوب عندما يكون  :valuesير موجود.',
    'required_without_all'          => 'الحقل  :attribute مطلوب عندما لا يكون أي من  :values موجودًا.',
    'same'                          => 'يجب أن تطابق  :attribute و  :other.',

    'size'                          => [
        'numeric' => 'يجب أن يكون  :attribute :size.',
        'file'    => 'جب أن يكون  :size :attribute كيلوبايت.',
        'string'  => 'يجب أن تكون  :attribute الأحرف  :size.',
        'array'   => 'يجب أن تحتوي  :attribute على عناصر  :size.',

    ],
    'string'                        => 'يجب أن يكون  :attribute سلسلة.',
    'timezone'                      => 'يجب أن تكون  :attribute منطقة صالحة.',
    'unique'                        => 'لقد تم بالفعل اتخاذ  :attribute.',
    'uploaded'                      => 'فشل  :attribute في التحميل.',
    'url'                           => 'التنسيق  :attribute غير صالح.',
    'unique_merchant_business_name' => 'يجب أن تكون  :attribute فريدة',

    'custom'                        => [
        'attribute-name' => [
            'rule-name' => 'رسالة مخصصة',
        ],
    ],
    'attributes'                    => [

    ]];
