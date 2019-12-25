<?php

$getCompanyName = getCompanyName();

return [
    'sidebar'              => [
        'dashboard'    => 'لوحة القيادة',
        'users'        => 'المستخدمين',
        'transactions' => 'المعاملات',
        'settings'     => 'الإعدادات',

    ],
    'footer'               => [
        'follow-us'      => 'تابعنا',
        'related-link'   => 'روابط ذات علاقة',
        'categories'     => 'الاقسام',
        'language'       => 'لغة',
        'copyright'      => 'حقوق النشر',
        'copyright-text' => 'كل الحقوق محفوظة',

    ],
    '2sa'                  => [
        'title-short-text'             => 'قفا',
        'title-text'                   => '2-Faktör Kimlik Doğrulaması',
        'extra-step'                   => 'توضح هذه الخطوة الإضافية أنك تحاول تسجيل الدخول حقًا.',
        'extra-step-settings-verify'   => 'توضح هذه الخطوة الإضافية أنك تحاول بالفعل التحقق.',
        'confirm-message'              => 'تم إرسال رسالة نصية برمز مصادقة مكون من 6 أرقام إلى',
        'confirm-message-verification' => 'تم إرسال رسالة نصية تحتوي على رمز تحقق مكون من 6 أرقام إلى',
        'remember-me-checkbox'         => 'تذكرني على هذا المتصفح',
        'verify'                       => 'التحقق',

    ],
    'personal-id'          => [
        'title'                 => 'التحقق من الهوية',
        'identity-type'         => 'نوع الهوية',
        'select-type'           => 'اختر صنف',
        'driving-license'       => 'رخصة قيادة',
        'passport'              => 'جواز سفر',
        'national-id'           => 'الهوية الوطنية',
        'identity-number'       => 'رقم الهوية',
        'upload-identity-proof' => 'تحميل إثبات الهوية',

    ],
    'personal-address'     => [
        'title'                => 'التحقق من العنوان',
        'upload-address-proof' => 'تحميل إثبات العنوان',

    ],
    'google2fa'            => [
        'title-text'     => 'Google İki Faktörlü Kimlik Doğrulama (2FA)',
        'subheader-text' => 'مسح QR Code باستخدام تطبيق Google Şifrematik.',
        'setup-a'        => 'قم بإعداد تطبيق Google Şifrematik قبل المتابعة.',
        'setup-b'        => 'لن تتمكن من التحقق من خلاف ذلك.',
        'proceed'        => 'الشروع في التحقق',
        'otp-title-text' => 'كلمة مرور واحدة (OTP)',
        'otp-input'      => 'أدخل OTP المكون من 6 أرقام من تطبيق Google Şifrematik',

    ],
    'form'                 => [

        'button'                   => [
            'sign-up' => 'سجل',
            'login'   => 'تسجيل الدخول',

        ],
        'forget-password-form'     => 'هل نسيت كلمة المرور',
        'reset-password'           => 'إعادة ضبط كلمة المرور',
        'yes'                      => 'نعم فعلا',
        'no'                       => 'لا',
        'add'                      => 'اضف جديد',
        'category'                 => 'الفئة',
        'unit'                     => 'وحدات',
        'category_create'          => 'إنشاء فئة',
        'category_edit'            => 'تحرير الفئة',
        'location_create'          => 'إنشاء الموقع',
        'location_edit'            => 'عدل الموقع',
        'location_name'            => 'اسم الموقع',
        'location_code'            => 'كود الموقع',
        'delivery_address'         => 'عنوان التسليم',
        'default_loc'              => 'الموقع الافتراضي',
        'phone_one'                => 'هاتف واحد',
        'phone_two'                => 'الهاتف الثاني',
        'fax'                      => 'فاكس',
        'email'                    => 'البريد الإلكتروني',
        'username'                 => 'اسم المستخدم',
        'contact'                  => 'اتصل',
        'item_create'              => 'خلق البند',
        'unit_create'              => 'إنشاء وحدة',
        'unit_edit'                => 'تحرير الوحدة',
        'item_id'                  => 'معرف العنصر',
        'item_name'                => 'اسم العنصر',
        'quantity'                 => 'كمية',
        'item_des'                 => 'وصف السلعة',
        'picture'                  => 'صورة',
        'location'                 => 'موقعك',
        'add_stock'                => 'أضف سهم',
        'select_one'               => 'اختر واحدة',
        'memo'                     => 'مذكرة',
        'close'                    => 'قريب',
        'remove_stock'             => 'قم بإزالة المخزون',
        'move_stock'               => 'تحريك الأسهم',
        'location_from'            => 'الموقع من',
        'location_to'              => 'الموقع الى',
        'item_edit'                => 'تعديل عنصر',
        'copy'                     => 'نسخ',
        'store_in'                 => 'خزن في',
        'order_items'              => 'طلب بضاعة',
        'delivery_from'            => 'التسليم من الموقع',
        'user_role_create'         => 'إنشاء دور المستخدم',
        'permission'               => 'الإذن',
        'section_name'             => 'اسم القسم',
        'areas'                    => 'المناطق',
        'Add'                      => 'إضافة',
        'Edit'                     => 'تصحيح',
        'Delete'                   => 'حذف',
        'name'                     => 'اسم',
        'full_name'                => 'الاسم الكامل',
        'password'                 => 'كلمه السر',
        'old_password'             => 'كلمة المرور القديمة',
        'set_password'             => 'ضبط كلمة السر',
        'new_password'             => 'كلمة السر الجديدة',
        'update_password'          => 'تطوير كلمة السر',
        'confirm_password'         => 'تأكيد كلمة المرور',
        're_password'              => 'اعد كلمة السر',
        'change_password'          => 'غير كلمة السر',
        'settings'                 => 'الإعدادات',
        'change_password_form'     => 'تغيير نموذج كلمة المرور',
        'user_create_form'         => 'انشاء المستخدم',
        'user_update_form'         => 'تحديث المستخدم',
        'submit'                   => 'خضع',
        'update'                   => 'تحديث',
        'cancel'                   => 'إلغاء',
        'sign_out'                 => 'خروج',
        'delete'                   => 'حذف',
        'company_create'           => 'إنشاء شركة',
        'company'                  => 'شركة',
        'db_host'                  => 'مضيف',
        'db_user'                  => 'مستخدم قاعدة البيانات',
        'db_password'              => 'كلمة مرور قاعدة البيانات',
        'db_name'                  => 'اسم قاعدة البيانات',
        'new_company_password'     => 'النصي الجديد كلمة مرور المسؤول',
        'pdf'                      => 'PDF',
        'customer'                 => 'زبون',
        'customer_branch'          => 'فرع العميل',
        'payment_type'             => 'نوع الدفع',
        'from_location'            => 'موقعك',
        'add_item'                 => 'اضافة عنصر',
        'sales_invoice_items'      => 'بنود فاتورة المبيعات',
        'purchase_invoice_items'   => 'شراء بنود الفاتورة',
        'supplier'                 => 'المورد',
        'order_item'               => 'قائمة الطلبات',
        'order_date'               => 'الطلب على',
        'item_tax_type'            => 'نوع الضريبة',
        'currency'                 => 'دقة',
        'sales_type'               => 'نوع المبيعات',
        'price'                    => 'السعر',
        'supplier_unit_of_messure' => 'الموردين وحدة القياس',
        'conversion_factor'        => 'عامل التحويل (إلى UOM لدينا)',
        'supplier_description'     => 'كود المورد أو الوصف',
        'next'                     => 'التالى',
        'add_branch'               => 'إضافة فرع',
        'payment_term'             => 'مصطلح الدفع',
        'site_name'                => 'اسم الموقع',
        'site_short_name'          => 'اسم الموقع القصير',
        'source'                   => 'مصدر',
        'destination'              => 'المكان المقصود',
        'stock_move'               => 'تحويل المخزن',
        'after'                    => 'بعد',
        'status'                   => 'الحالة',
        'date'                     => 'تاريخ',
        'qty'                      => 'الكمية',
        'terms'                    => 'مصطلح',
        'add_new_customer'         => 'أضف زبون جديد',
        'add_new_order'            => 'إضافة طلب جديد',
        'add_new_invoice'          => 'إضافة فاتورة جديدة',
        'group_name'               => 'أسم المجموعة',
        'edit'                     => 'تصحيح',
        'title'                    => 'عنوان',
        'description'              => 'وصف',
        'reminder'                 => 'تاريخ التذكير',

    ],
    'home'                 => [

        'title-bar'       => [
            'home'      => 'الصفحة الرئيسية',
            'send'      => 'إرسال',
            'request'   => 'طلب',
            'developer' => 'مطور',
            'login'     => 'تسجيل الدخول',
            'register'  => 'تسجيل',
            'logout'    => 'الخروج',
            'dashboard' => 'لوحة القيادة',

        ],
        'banner'          => [
            'title'      => 'تحويل الأموال البسيط إلى: br أحبائك',
            'sub-title1' => 'بسيطة ل: أن تكون متكاملة',
            'sub-title2' => 'متعددة: br المحفظة',
            'sub-title3' => 'متقدم: br الأمن',

        ],
        'choose-us'       => [
            'title'      => 'لماذا أخترتنا؟',
            'sub-title1' => 'نحن لسنا بنك. معنا تحصل على رسوم منخفضة وأسعار الصرف في الوقت الحقيقي.',
            'sub-title2' => 'احصل على المال إلى العائلة والأصدقاء على الفور ، فأنت تحتاج فقط إلى عنوان بريد إلكتروني.',
            'sub-title3' => 'لتحويل الأموال والانسحاب وتبادل العملات - رسومنا منخفضة التكلفة.',

        ],
        'payment-gateway' => [
            'title' => 'معالجات الدفع',

        ],
        'services'        => [
            't1' => 'واجهة برمجة تطبيقات الدفع',
            's1' => 'سوف تدير العملاء '.$getCompanyName.'وتجربة من خلال دمج واجهة API سلس لدينا في موقع الويب الخاص بك.',
            't2' => 'المدفوعات عبر الإنترنت',
            's2' => 'أيًا كان الحساب الائتماني أو الخصم أو الحساب المصرفي الذي يمكنك دفعه بطريقتك.',
            't3' => 'تحويل العملات',
            's3' => 'العملة الافتراضية لآخر يمكنك تغييرها بسهولة.',
            't4' => 'طلب الدفع',
            's4' => 'من خلال هذه الأنظمة الآن يمكنك طلب أموال الدفع من أي بلد إلى أي بلد.',
            't5' => 'نظام القسيمة',
            's5' => 'إصدار وإدارة القسائم الخاصة بك أو الموهوبين الخاصة بك',
            't6' => 'الكشف عن الغش',
            's6' => 'وهذا يعني أننا نساعد في الحفاظ على حسابك أكثر أمانًا وموثوقًا. استمتع بالدفع الآمن عبر الإنترنت.',

        ],
        'how-work'        => [
            'title'      => 'كيف تعمل',
            'sub-title1' => 'أولاً ، قم بإنشاء إيداع إلى حسابك.',
            'sub-title2' => 'حدد مقدار المبلغ الذي تريد إرساله واختيار المحفظة.',
            'sub-title3' => 'اكتب عنوان البريد الإلكتروني مع ملاحظة قصيرة إذا كنت تريد.',
            'sub-title4' => 'انقر على إرسال الأموال.',
            'sub-title5' => 'يمكنك تبادل عملتك أيضا.',

        ],
    ],
    'send-money'           => [

        'banner'    => [
            'title'     => 'إرسال الأموال الطريقة التي تناسبك',
            'sub-title' => 'بسرعة وسهولة إرسال واستقبال الأموال أو إعطاء قسيمة كهدية.',
            // 'sign-up'   => 'قم بالتسجيل لدفع المال',
            'sign-up'   => $getCompanyName.'  التوقيع على ',
            'login'     => 'تسجيل الدخول الآن',

        ],
        'section-a' => [
            'title'         => 'إرسال الأموال على الصعيد العالمي في غضون دقائق قليلة مع العملات المتعددة فقط في عدد قليل
                            النقرات.',

            'sub-section-1' => [
                'title'     => 'تسجيل حساب',
                'sub-title' => 'في البداية يكون مستخدم التسجيل ، ثم تسجيل الدخول إلى حسابك وإدخال البطاقة أو البنك
                            تفاصيل المعلومات المطلوبة لك.',

            ],
            'sub-section-2' => [
                'title'     => 'اختر المستلم الخاص بك',
                'sub-title' => 'أدخل عنوان البريد الإلكتروني الخاص بالمستلم والذي لن يتم مشاركته مع الآخرين وسيظل مؤمّنًا ، ثم
                            إضافة مبلغ مع عملة لإرسالها بأمان.',

            ],
            'sub-section-3' => [
                'title'     => 'إرسال الأموال',
                'sub-title' => 'بعد إرسال الأموال ، سيتم إشعار المستلم عبر البريد الإلكتروني عندما يكون المال
                            نقل إلى حسابهم.',

            ],
        ],
        'section-b' => [
            'title'     => 'إرسال الأموال في غضون ثانية.',
            'sub-title' => 'يمكن لأي شخص لديه عنوان بريد إلكتروني إرسال / تلقي طلب الدفع سواء كان لديه حساب أم لا. يمكنهم الدفع باستخدام بطاقة ائتمان أو حساب مصرفي',

        ],
        'section-c' => [
            // 'title'     => 'إرسال الأموال إلى أي شخص ، في أي مكان ، على الفور باستخدام نظام الدفع باي بال',
            'title'     => 'النظام '. $getCompanyName .' إرسال الأموال إلى أي شخص ، في أي مكان ، على الفور باستخدام',
//             'sub-title' => 'تحويل الأموال إلى أصدقائك وعائلتك على الصعيد العالمي من خلال التطبيق المحمول Pay Money ، البنك
//                             حساب أو بوابة دفع الآخرين. الأموال تذهب مباشرة إلى حسابك سواء المستلم
//                             لديك أي حساب أم لا. يمكنك إرسال / طلب المال عن طريق نوع مختلف من الدفع
//                             بوابة بعملات مختلفة.',
            'sub-title' => 'تطبيق الجوال أو الحساب المصرفي أو بوابة الدفع الأخرى. الأموال تذهب مباشرة إلى حسابك سواء كان لدى المستلم أي حساب أم لا. يمكنك إرسال / طلب المال عبر نوع مختلف من بوابة الدفع بعملات مختلفة. ' . $getCompanyName . ' تحويل الأموال إلى أصدقائك وعائلتك على مستوى العالم من خلال',

        ],
        'section-d' => [
            'title'   => 'أسرع وأبسط وأكثر أمانا - إرسال الأموال لمن تحب اليوم.',
            'sign-up' => $getCompanyName.'  التوقيع على ',

        ],
        'section-e' => [
            'title'     => 'بدء إرسال الأموال.',
            'sub-title' => 'الآن ، ليس لديك مشكلة في الحصول على المال النقدي. يمكن لأي شخص إرسال الأموال من بطاقتهم ، البنك
                            حساب أو رصيد paypal أو بوابات الدفع الأخرى. سوف تقوم بإعلامك عبر بريد إلكتروني بسيط.',

        ],
    ],
    'request-money'        => [

        'banner'    => [
            // 'title'          => 'طلب المال من جميع أنحاء العالم: br مع دفع المال',
            'title'          =>  $getCompanyName . ' طلب المال من جميع أنحاء العالم مع',
            'sub-title'      => 'جعل تذكير الناس لإرسال الأموال.',
            // 'sign-up'        => 'قم بالتسجيل لدفع المال',
            'sign-up'        => $getCompanyName.'  التوقيع على ',
            'already-signed' => 'وقعت بالفعل؟',
            'login'          => 'تسجيل الدخول',
            'request-money'  => 'لطلب المال.',

        ],
        'section-a' => [
            'title'         => 'مستخدم نظام طلب المال الودية.',
//             'sub-title'     => 'طلب المال هو وسيلة فعالة ومهذبة لطلب المال الذي تدين به. : br الاستخدام
//                             نظام payMoney لإرسال الأموال أو تلقي الأموال أو تحويل الأموال من أقرب وأعز
//                             منها.',
            'sub-title'     => 'نظام لإرسال الأموال أو تلقي الأموال أو تحويل الأموال من أقرب وأعز إليك. ' . $getCompanyName . ' طلب المال هو وسيلة فعالة ومهذبة لطلب المال الذي تستحقه. استعمال',

            'sub-section-1' => [
                'title'     => 'تسجيل حساب',
                'sub-title' => 'في البداية يكون مستخدم التسجيل ، ثم تسجيل الدخول إلى حسابك وإدخال البطاقة أو البنك
                            تفاصيل المعلومات المطلوبة لك لطلب المال.',

            ],
            'sub-section-2' => [
                'title'     => 'اختر المستلم الخاص بك',
                'sub-title' => 'قم بإدخال عنوان البريد الإلكتروني الخاص بالمستلم والذي لن يتم مشاركته مع الآخرين وسيظل مؤمّنًا ، بعد ذلك
                            إضافة مبلغ مع عملة لإرسالها بأمان.',

            ],
            'sub-section-3' => [
                'title'     => 'طلب المال',
                'sub-title' => 'بعد طلب المال سيتم إخطار المتلقي عبر البريد الإلكتروني عندما يكون المال
                            نقل من حسابهم.',

            ],
        ],
        'section-b' => [
            'title'     => 'يمكن إرسال الأموال عن طريق الهاتف المحمول',
            'sub-title' => 'الآن ، ليس لديك مشكلة في الحصول على المال النقدي. يمكن لأي شخص إرسال الأموال من بطاقتهم ، البنك
                            حساب أو رصيد paypal أو بوابات الدفع الأخرى. سوف تقوم بإعلامك عبر بريد إلكتروني بسيط.',

        ],
        'section-c' => [
            // 'title'     => 'استخدام payMoney موبايل التطبيق لطلب المال بسهولة.',
            'title'     => 'تطبيق جوال لطلب المال بسهولة. ' . $getCompanyName . ' استخدم ال',
            'sub-title' => 'يمكن لأي شخص لديه عنوان بريد إلكتروني تلقي طلب دفع ، سواء كان لديه حساب أو
                            ليس. يمكن أن تدفع لك مع paypal ، شريط ، 2checkout والعديد من paymentgateway أكثر.',

        ],
        'section-d' => [
            // 'title'     => 'طلب المال إلى أي شخص ، في أي مكان ، على الفور باستخدام نظام payMoney',
            'title'     => 'النظام ' . $getCompanyName . ' طلب المال لأي شخص ، في أي مكان ، على الفور باستخدام',
//             'sub-title' => 'تحويل الأموال إلى أصدقائك وعائلتك على الصعيد العالمي من خلال التطبيق payMoney المحمول ، البنك
//                             حساب أو بوابة دفع الآخرين. الأموال تذهب مباشرة إلى حسابك سواء المستلم
//                             لديك أي حساب أم لا. يمكنك إرسال / طلب المال عن طريق نوع مختلف من الدفع
//                             بوابة بعملات مختلفة.',
            'sub-title' => 'تطبيق جوال أو حساب مصرفي أو بوابة دفع أخرى. تذهب الأموال مباشرة إلى حسابك سواء كان لدى المستلم أي حساب أم لا. يمكنك إرسال / طلب المال عبر نوع مختلف من بوابة الدفع بعملات مختلفة. ' . $getCompanyName . ' تحويل الأموال إلى أصدقائك وعائلتك على مستوى العالم من خلال ' ,

        ],
        'section-e' => [
            'title'   => 'أسرع وأبسط وأكثر أمانا - إرسال الأموال لمن تحب اليوم.',
            'sign-up' => $getCompanyName.'  التوقيع على ',

        ],
    ],
    'login'                => [
        'title'           => 'تسجيل الدخول',
        'form-title'      => 'تسجيل الدخول',
        'email'           => 'البريد الإلكتروني',
        'phone'           => 'هاتف',
        'email_or_phone'  => 'بريد الكتروني او هاتف',
        'password'        => 'كلمه السر',
        'forget-password' => 'نسيت كلمة المرور؟',
        'no-account'      => 'ليس لديك حساب؟',
        'sign-up-here'    => 'سجل هنا',

    ],
    'registration'         => [
        'title'                => 'التسجيل',
        'form-title'           => 'إنشاء مستخدم جديد',
        'first-name'           => 'الاسم الاول',
        'last-name'            => 'الكنية',
        'email'                => 'البريد الإلكتروني',
        'phone'                => 'هاتف',
        'password'             => 'كلمه السر',
        'confirm-password'     => 'تأكيد كلمة المرور',
        'terms'                => 'يعني النقر على "اشتراك" أنك توافق على البنود وسياسة البيانات وسياسة ملفات تعريف الارتباط.',
        'new-account-question' => 'هل لديك حساب؟',
        'sign-here'            => 'تسجيل الدخول هنا',
        'type-title'           => 'نوع',
        'type-user'            => 'المستعمل',
        'type-merchant'        => 'تاجر',
        'select-user-type'     => 'حدد نوع المستخدم',

    ],
    'dashboard'            => [

        'nav-menu'     => [
            'dashboard'    => 'لوحة القيادة',
            'transactions' => 'المعاملات',
            'send-req'     => 'ارسل طلب',
            'send-to-bank' => 'ارسل الى البنك',
            'vouchers'     => 'قسائم',
            'merchants'    => 'التجار',
            'disputes'     => 'النزاعات',
            'settings'     => 'الإعدادات',
            'tickets'      => 'تذاكر',
            'logout'       => 'الخروج',
            'payout'       => 'سيصرف',
            'exchange'     => 'تبادل',

        ],
        'left-table'   => [
            'title'            => 'النشاط الأخير',
            'date'             => 'تاريخ',
            'description'      => 'وصف',
            'status'           => 'الحالة',
            'currency'         => 'دقة',
            'amount'           => 'كمية',
            'view-all'         => 'عرض الكل',
            'no-transaction'   => 'لم يتم العثور على معاملة!',
            'details'          => 'تفاصيل',
            'fee'              => 'في',
            'total'            => 'مجموع',
            'transaction-id'   => 'معرف المعاملة',
            'transaction-date' => 'تاريخ الصفقة',

            'deposit'          => [
                'deposited-to'     => 'أودعت ل',
                'payment-method'   => 'طريقة الدفع او السداد',
                'deposited-amount' => 'المبلغ المودع',
                'deposited-via'    => 'أودعت عبر',

            ],
            'withdrawal'       => [
                'withdrawan-with'   => 'دفع مع',
                'withdrawan-amount' => 'مبلغ الدفع',

            ],
            'transferred'      => [
                'paid-with'          => 'دفعت مع',
                'transferred-amount' => 'المبلغ المحول',
                'email'              => 'البريد الإلكتروني',
                'note'               => 'ملحوظة',

            ],
            'bank-transfer'    => [
                'bank-details'        => 'تفاصيل البنك',
                'bank-name'           => 'اسم البنك',
                'bank-branch-name'    => 'اسم الفرع',
                'bank-account-name'   => 'أسم الحساب',
                'bank-account-number' => 'رقم حساب', //pm1.9
                'transferred-with'    => 'المنقولة مع',
                'transferred-amount'  => 'البنك المحول المبلغ',

            ],
            'received'         => [
                'paid-by'         => 'دفعت بواسطة',
                'received-amount' => 'المبلغ الذي تسلمه',

            ],
            'exchange-from'    => [
                'from-wallet'          => 'من المحفظة',
                'exchange-from-amount' => 'مبلغ التبادل',
                'exchange-from-title'  => 'تبادل من',
                'exchange-to-title'    => 'تبادل ل',

            ],
            'exchange-to'      => [
                'to-wallet' => 'إلى المحفظة',

            ],
            'voucher-created'  => [
                'voucher-code'   => 'رمز القسيمة',
                'voucher-amount' => 'المبلغ قسيمة',

            ],
            'request-to'       => [
                'accept' => 'قبول',

            ],
            'payment-Sent'     => [
                'payment-amount' => 'مبلغ الدفعة',

            ],
        ],
        'right-table'  => [
            'title'                => 'محافظ',
            'no-wallet'            => 'لا توجد محفظة!',
            'default-wallet-label' => 'افتراضي',

        ],
        'button'       => [
            'deposit'         => 'الوديعة',
            'withdraw'        => 'سيصرف',
            'payout'          => 'سيصرف',
            'exchange'        => 'تبادل',
            'submit'          => 'خضع',
            'send-money'      => 'إرسال الأموال',
            'send-request'    => 'ارسل طلب',
            'create'          => 'خلق',
            'activate'        => 'تفعيل',
            'new-merchant'    => 'تاجر جديد',
            'details'         => 'تفاصيل',
            'change-picture'  => 'تغيير الصورة',
            'change-password' => 'غير كلمة السر',
            'new-ticket'      => 'تذكرة جديدة',
            'next'            => 'التالى',
            'back'            => 'الى الخلف',
            'confirm'         => 'تؤكد',
            'select-one'      => 'اختر واحدة',
            'update'          => 'تحديث',
            'filter'          => 'منقي',

        ],
        'deposit'      => [
            'title'                                       => 'الوديعة',
            'deposit-via'                                 => 'إيداع الأموال عن طريق',
            'amount'                                      => 'كمية',
            'currency'                                    => 'دقة',
            'payment-method'                              => 'طريقة الدفع او السداد',
            'no-payment-method'                           => 'طريقة الدفع غير موجودة!',
            'fees-limit-payment-method-settings-inactive' => 'حدود كل من رسوم و طريقة الدفع غير نشطة',
            'total-fee'                                   => 'توتال في:',
            'total-fee-admin'                             => 'مجموع:',
            'fee'                                         => 'في',
            'deposit-amount'                              => 'قيمة الايداع',
            'completed-success'                           => 'اكتملت الوديعة بنجاح',
            'success'                                     => 'نجاح',
            'deposit-again'                               => 'إيداع الأموال مرة أخرى',

            'deposit-stripe-form'                         => [
                'title'   => 'إيداع مع شريط',
                'card-no' => 'رقم البطاقة',
                'mm-yy'   => 'MM / YY',
                'cvc'     => 'CVC',

            ],
            'select-bank'                                 => 'حدد البنك', //pm1.9
            'payment-references'                          => [
                // 'merchant-payment-reference'   => 'مرجع دفع التاجر',
                'user-payment-reference' => 'مرجع الدفع المستخدم',
                // 'merchant-payment-number'   => 'رقم دفع التاجر',
            ],
        ],
        'payout'       => [

            'menu'           => [
                'payouts'        => 'دفعات',
                'payout-setting' => 'إعداد العائد',
                'new-payout'     => 'العائد الجديد',

            ],
            'list'           => [
                'method'      => 'طريقة',
                'method-info' => 'معلومات الطريقة',
                'charge'      => 'الشحنة',
                'amount'      => 'كمية',
                'currency'    => 'دقة',
                'status'      => 'الحالة',
                'date'        => 'تاريخ',
                'not-found'   => 'لم يتم العثور على بيانات !',
                'fee'         => 'في',

            ],
            'payout-setting' => [
                'add-setting' => 'إضافة الإعداد',
                'payout-type' => 'نوع العائد',
                'account'     => 'الحساب',
                'action'      => 'عمل',

                'modal'       => [
                    'title'                        => 'إضافة إعداد العائد',
                    'payout-type'                  => 'نوع العائد',
                    'email'                        => 'البريد الإلكتروني',
                    'bank-account-holder-name'     => 'اسم صاحب حساب البنك',
                    'branch-name'                  => 'اسم الفرع',
                    'account-number'               => 'رقم الحساب البنكي / IBAN',
                    'branch-city'                  => 'فرع المدينة',
                    'swift-code'                   => 'رمز السرعة',
                    'branch-address'               => 'عنوان فرع',
                    'bank-name'                    => 'اسم البنك',
                    'attached-file'                => 'ملف مرفق',
                    'country'                      => 'بلد',
                    'perfect-money-account-number' => 'رقم حساب المال المثالي',
                    'payeer-account-number'        => 'رقم حساب Payeer',

                ],
            ],
            'new-payout'     => [
                'title'          => 'سيصرف',
                'payment-method' => 'طريقة الدفع او السداد',
                'currency'       => 'دقة',
                'amount'         => 'كمية',
                'bank-info'      => 'معلومات الحساب البنكي',
                'withdraw-via'   => 'أنت على وشك دفع المال عبر',
                'success'        => 'نجاح',
                'payout-success' => 'دفع تعويضات تم بنجاح',
                'payout-again'   => 'دفع تعويضات مرة أخرى',

            ],
        ],
        'confirmation' => [
            'details' => 'تفاصيل',
            'amount'  => 'كمية',
            'fee'     => 'في',
            'total'   => 'مجموع',

        ],
        'transaction'  => [
            'date-range'      => 'اختر نطاقًا زمنيًا',
            'all-trans-type'  => 'كل نوع المعاملة',
            'payment-sent'    => 'ارسلت الدفعه',
            'payment-receive' => 'الدفعة المستلمة',
            'payment-req'     => 'طلب الدفع',
            'exchanges'       => 'التبادل',
            'all-status'      => 'كل الحالة',
            'all-currency'    => 'كل العملات',
            'success'         => 'نجاح',
            'pending'         => 'قيد الانتظار',
            'blocked'         => 'ألغيت',
            'refund'          => 'ردها',
            'open-dispute'    => 'نزاع مفتوح',

        ],
        'exchange'     => [

            'left-top'    => [
                'title'           => 'صرف العملات',
                'select-wallet'   => 'حدد المحفظة',
                'amount-exchange' => 'مبلغ التبادل',
                'give-amount'     => 'سوف تعطي',
                'get-amount'      => 'ستحصل',
                'balance'         => 'توازن',
                'from-wallet'     => 'من المحفظة',
                'to-wallet'       => 'إلى المحفظة',
                'base-wallet'     => 'من المحفظة',
                'other-wallet'    => 'إلى المحفظة',
                'type'            => 'نوع التبادل',
                'type-text'       => 'العملة الأساسية هي:',
                'to-other'        => 'إلى عملة أخرى',
                'to-base'         => 'إلى العملة الأساسية',

            ],
            'left-bottom' => [
                'title'            => 'صرف العملات (إلى العملة الأساسية)',
                'exchange-to-base' => 'التبادل إلى القاعدة',
                'wallet'           => 'محفظة نقود',

            ],
            'right'       => [
                'title' => 'سعر الصرف',

            ],
            'confirm'     => [
                'title'                => 'الصرافة',
                'exchanging'           => 'تبادل',
                'of'                   => 'من',
                'equivalent-to'        => 'أي ما يعادل',
                'exchange-rate'        => 'سعر الصرف',
                'amount'               => 'مبلغ التبادل',
                'has-exchanged-to'     => 'قد تبادلت',
                'exchange-money-again' => 'تبادل الأموال مرة أخرى',

            ],
        ],
        'send-request' => [

            'menu'         => [
                'send'    => 'إرسال',
                'request' => 'طلب',

            ],
            'send'         => [
                'title'        => 'إرسال الأموال',

                'confirmation' => [
                    'title'              => 'إرسال الأموال',
                    'send-to'            => 'أنت ترسل الأموال إلى',
                    'transfer-amount'    => 'مبلغ التحويل',
                    'money-send'         => 'تم تحويل الأموال بنجاح',
                    'bank-send'          => 'تحويل الأموال إلى بنك بنجاح',
                    'send-again'         => 'إرسال الأموال مرة أخرى',
                    'send-to-bank-again' => 'نقل إلى البنك مرة أخرى',

                ],
            ],
            'send-to-bank' => [
                'title'        => 'نقل إلى البنك',
                'subtitle'     => 'تحويل الأموال إلى البنك',

                'confirmation' => [
                    'title'           => 'تحويل الأموال إلى البنك',
                    'send-to'         => 'أنت ترسل الأموال إلى',
                    'transfer-amount' => 'مبلغ التحويل',
                    'money-send'      => 'تم تحويل الأموال بنجاح',
                    'send-again'      => 'إرسال الأموال مرة أخرى',

                ],
            ],
            'request'      => [
                'title'        => 'طلب المال',

                'confirmation' => [
                    'title'              => 'طلب المال',
                    'request-money-from' => 'أنت تطلب المال من',
                    'requested-amount'   => 'الكمية المطلوبة',
                    'success'            => 'نجاح',
                    'success-send'       => 'تم إرسال طلب المال بنجاح',
                    'request-amount'     => 'طلب المبلغ',
                    'request-again'      => 'طلب المال مرة أخرى',

                ],
                'success'      => [
                    'title'            => 'قبول طلب المال',
                    'request-complete' => 'تم قبول الأموال المطلوبة بنجاح',
                    'accept-amount'    => 'المبلغ المقبول',

                ],
                'accept'       => [
                    'title' => 'قبول طلب الدفع',

                ],
            ],
            'common'       => [
                'recipient'   => 'مستلم',
                'amount'      => 'كمية',
                'currency'    => 'دقة',
                'note'        => 'ملحوظة',
                'anyone-else' => 'لن نشارك بريدك الإلكتروني مطلقًا مع أي شخص آخر.',
                'enter-note'  => 'أدخل ملاحظة',
                'enter-email' => 'أدخل البريد الإلكتروني',

            ],
        ],
        'vouchers'     => [

            'left-top'            => [
                'title'    => 'إنشاء قسيمة',
                'amount'   => 'كمية',
                'currency' => 'دقة',

            ],
            'left-bottom'         => [
                'title' => 'تفعيل القسيمة',
                'code'  => 'الشفرة',

            ],
            'right'               => [
                'title'     => 'قسائم',
                'code'      => 'الشفرة',
                'amount'    => 'كمية',
                'status'    => 'الحالة',
                'not-found' => 'القسيمة غير موجودة!',

            ],
            'confirmation'        => [
                'title'     => 'إنشاء قسيمة',
                'sub-title' => 'أنت على وشك إنشاء قسيمة',
                'amount'    => 'المبلغ قسيمة',

            ],
            'active-confirmation' => [
                'title'     => 'تم التنشيط',
                'sub-title' => 'أنت على وشك تفعيل رمز القسيمة',
                'amount'    => 'المبلغ قسيمة',

            ],
            'success'             => [
                'title'   => 'إيصال',
                'success' => 'نجاح',
                'amount'  => 'المبلغ قسيمة',
                'print'   => 'طباعة',

            ],
            'ajax-response'       => [
                'voucher-code-error'         => 'قسيمة تفعيلها بالفعل!',
                'voucher-code-pending-error' => 'عذرًا ، لا يمكن تنشيط القسيمة المعلقة!',
                'voucher-not-found'          => 'عذرا ، لم يتم العثور على قسيمة!',

            ],
        ],
        'merchant'     => [

            'menu'                => [
                'merchant'      => 'التجار',
                'payment'       => 'المدفوعات',
                'list'          => 'قائمة',
                'details'       => 'تفاصيل',
                'edit-merchant' => 'تعديل التاجر',
                'new-merchant'  => 'تاجر جديد',

            ],
            'table'               => [
                'id'            => 'إذ',
                'business-name' => 'الاسم التجاري',
                'site-url'      => 'URL الموقع',
                'type'          => 'نوع',
                'status'        => 'الحالة',
                'action'        => 'عمل',
                'not-found'     => 'لم يتم العثور على بيانات !',
                'moderation'    => 'الاعتدال',
                'disapproved'   => 'مرفوض',
                'approved'      => 'وافق',

            ],
            'html-form-generator' => [
                'title'             => 'مولد نموذج HTML',
                'merchant-id'       => 'معرف التاجر',
                'item-name'         => 'اسم العنصر',
                'order-number'      => 'رقم الطلب',
                'price'             => 'السعر',
                'custom'            => 'العادة',
                'right-form-title'  => 'مثال على شكل HTML',
                'right-form-copy'   => 'نسخ',
                'right-form-copied' => 'نسخ',
                'right-form-footer' => 'انسخ رمز النموذج وضعه على موقع الويب الخاص بك.',
                'close'             => 'قريب',
                'generate'          => 'توفير',
                'app-info'          => 'معلومات التطبيق',
                'client-id'         => 'معرف العميل',
                'client-secret'     => 'سر العميل',

            ],
            'payment'             => [
                'merchant'   => 'تاجر',
                'method'     => 'طريقة',
                'order-no'   => 'أجل لا',
                'amount'     => 'كمية',
                'fee'        => 'في',
                'total'      => 'مجموع',
                'currency'   => 'دقة',
                'status'     => 'الحالة',
                'created-at' => 'تاريخ',
                'pending'    => 'قيد الانتظار',
                'success'    => 'نجاح',
                'block'      => 'منع',
                'refund'     => 'إعادة مال',

            ],
            'add'                 => [
                'title'    => 'انشاء تاجر',
                'name'     => 'اسم',
                'site-url' => 'URL الموقع',
                'type'     => 'نوع',
                'note'     => 'ملحوظة',
                'logo'     => 'شعار',

            ],
            'details'             => [
                'merchant-id'   => 'معرف التاجر',
                'business-name' => 'الاسم التجاري',
                'status'        => 'الحالة',
                'site-url'      => 'URL الموقع',
                'note'          => 'ملحوظة',
                'date'          => 'تاريخ',

            ],
            'edit'                => [
                'comment-for-administration' => 'تعليق على الادارة',

            ],
        ],
        'dispute'      => [
            'dispute'        => 'النزاعات',
            'title'          => 'عنوان',
            'dispute-id'     => 'معرف النزاع',
            'transaction-id' => 'معرف المعاملة',
            'created-at'     => 'أنشئت في',
            'status'         => 'الحالة',
            'no-dispute'     => 'لم يتم العثور على بيانات!',
            'defendant'      => 'المدعى عليه',
            'claimant'       => 'المدعي',
            'description'    => 'وصف',

            'status-type'    => [
                'open'   => 'افتح',
                'solved' => 'تم حلها',
                'closed' => 'مغلق',
                'solve'  => 'حل',
                'close'  => 'قريب',

            ],
            'discussion'     => [

                'sidebar' => [
                    'title-text'    => 'معلومات النزاع',
                    'header'        => 'معلومات النزاع',
                    'title'         => 'عنوان',
                    'reason'        => 'السبب',
                    'change-status' => 'تغيير الوضع',

                ],
                'form'    => [
                    'title'   => 'عرض النزاع',
                    'message' => 'رسالة',
                    'file'    => 'ملف',

                ],
            ],
        ],
        'setting'      => [
            'title'                   => 'ملف تعريفي للمستخدم',
            'change-avatar'           => 'تغيير الصورة الرمزية',
            'change-avatar-here'      => 'يمكنك تغيير الصورة الرمزية هنا',
            'change-password'         => 'غير كلمة السر',
            'change-password-here'    => 'يمكنك تغيير كلمة المرور هنا',
            'profile-information'     => 'معلومات الملف الشخصي',
            'email'                   => 'البريد الإلكتروني',
            'first-name'              => 'الاسم الاول',
            'last-name'               => 'الكنية',
            'mobile'                  => 'رقم الموبايل',
            'address1'                => 'العنوان 1',
            'address2'                => 'العنوان 2',
            'city'                    => 'مدينة',
            'state'                   => 'حالة',
            'country'                 => 'بلد',
            'timezone'                => 'وحدة زمنية',
            'old-password'            => 'كلمة المرور القديمة',
            'new-password'            => 'كلمة السر الجديدة',
            'confirm-password'        => 'تأكيد كلمة المرور',
            'add-phone'               => 'أضف هاتف',
            'add-phone-subhead1'      => 'انقر فوق',
            'add-phone-subhead2'      => 'لإضافة هاتف',
            'add-phone-subheadertext' => 'أدخل الرقم الذي ترغب في استخدامه',
            'get-code'                => 'الحصول على رمز',
            'phone-number'            => 'رقم الهاتف',
            'edit-phone'              => 'تحرير الهاتف',
            'default-wallet'        => 'المحفظة الافتراضية',

        ],
        'ticket'       => [
            'title'     => 'تذاكر',
            'ticket-no' => 'تذكرة رقم',
            'subject'   => 'موضوع',
            'status'    => 'الحالة',
            'priority'  => 'أفضلية',
            'date'      => 'تاريخ',
            'action'    => 'عمل',
            'no-ticket' => 'لم يتم العثور على بيانات!',

            'add'       => [
                'title'    => 'تذكرة جديدة',
                'name'     => 'اسم',
                'message'  => 'رسالة',
                'priority' => 'أفضلية',

            ],
            'details'   => [

                'sidebar' => [
                    'header'    => 'معلومات البطاقة',
                    'ticket-id' => 'معرف تذكرة',
                    'subject'   => 'موضوع',
                    'date'      => 'تاريخ',
                    'priority'  => 'أفضلية',
                    'status'    => 'الحالة',

                ],
                'form'    => [
                    'title'   => 'عرض التذاكر',
                    'message' => 'رسالة',
                    'file'    => 'ملف',

                ],
            ],
        ],
    ],
    'express-payment'      => [
        'payment'           => 'دفع',
        'pay-with'          => 'ادفع عن طريق',
        'about-to-make'     => 'أنت على وشك إجراء الدفع عبر',
        'test-payment-form' => 'اختبار طريقة الدفع',
        'pay-now'           => 'ادفع الآن!',
    ],

    'express-payment-form' => [
        'merchant-not-found'   => 'التاجر غير موجود! يرجى المحاولة مع التاجر صالح.',
        'merchant-found'       => 'خضع المستلم للتحقق خاص وأكد موثوقيته',
        'continue'             => 'استمر',
        'email'                => 'البريد الإلكتروني',
        'password'             => 'كلمه السر',
        'cancel'               => 'إلغاء',
        'go-to-payment'        => 'الذهاب إلى الدفع',
        'payment-agreement'    => 'يتم الدفع على صفحة آمنة. عند إجراء الدفع ، أنت توافق على شروط الاتفاقية',
        'debit-credit-card'    => 'بطاقة الائتمان / الخصم',
        'merchant-payment'     => 'دفع التاجر',
        'sorry'                => 'آسف!',
        'payment-unsuccessful' => 'الدفع غير ناجح.',
        'success'              => 'نجاح!',//
        'payment-successfull'  => 'تم الدفع بنجاح',//
        'back-home'            => 'العودة الرئيسية',
    ],
];
