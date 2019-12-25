<?php

$getCompanyName = getCompanyName();

return [
    'sidebar'              => [
        'dashboard'    => '仪表板',
        'users'        => '用户',
        'transactions' => '交易',
        'settings'     => '设置',
    ],
    'footer'               => [
        'follow-us'      => '跟着我们',
        'related-link'   => '相关链接',
        'categories'     => '分类',
        'language'       => '语言',
        'copyright'      => '版权',
        'copyright-text' => '版权所有',
    ],
    '2sa'                  => [
        'title-short-text'             => '2-fa',
        'title-text'                   => '双因素身份验证',
        'extra-step'                   => '这个额外的步骤表明它确实是您尝试登录的。',
        'extra-step-settings-verify'   => '这个额外的步骤表明它确实是你要验证的。',
        'confirm-message'              => '刚刚发送带有6位验证码的短信',
        'confirm-message-verification' => '刚刚发送带有6位验证码的短信',
        'remember-me-checkbox'         => '在这个浏览器上记住我',
        'verify'                       => '校验',
    ],
    'personal-id'          => [
        'title'                 => '身份验证',
        'identity-type'         => '身份类型',
        'select-type'           => '选择类型',
        'driving-license'       => '驾驶执照',
        'passport'              => '护照',
        'national-id'           => '国民身份证',
        'identity-number'       => '身份号码',
        'upload-identity-proof' => '上传身份证明',
    ],
    'personal-address'     => [
        'title'                => '地址验证',
        'upload-address-proof' => '上传地址证明',
    ],
    'google2fa'            => [
        'title-text'     => 'Google双因素身份验证（2FA）',
        'subheader-text' => '使用Google身份验证器应用扫描QR码。',
        'setup-a'        => '在继续之前设置您的Google身份验证器应用。',
        'setup-b'        => '您将无法另行验证。',
        'proceed'        => '继续进行验证',
        'otp-title-text' => '一次性密码（OTP）',
        'otp-input'      => '从Google身份验证器应用程序输入6位数的OTP',
    ],
    'form'                 => [
        'button'                   => [
            'sign-up' => '注册',
            'login'   => '登录',
        ],
        'forget-password-form'     => '忘记密码',
        'reset-password'           => '重设密码',
        'yes'                      => '是',
        'no'                       => '没有',
        'add'                      => '添新',
        'category'                 => '类别',
        'unit'                     => '单位',
        'category_create'          => '创建类别',
        'category_edit'            => '编辑类别',
        'location_create'          => '创建位置',
        'location_edit'            => '编辑位置',
        'location_name'            => '地点名称',
        'location_code'            => '位置代码',
        'delivery_address'         => '邮寄地址',
        'default_loc'              => '默认位置',
        'phone_one'                => '电话一',
        'phone_two'                => '电话二',
        'fax'                      => '传真',
        'email'                    => '电子邮件',
        'username'                 => '用户名',
        'contact'                  => '联系',
        'item_create'              => '创建项目',
        'unit_create'              => '创建单位',
        'unit_edit'                => '编辑单位',
        'item_id'                  => '物品ID',
        'item_name'                => '项目名称',
        'quantity'                 => '数量',
        'item_des'                 => '商品描述',
        'picture'                  => '图片',
        'location'                 => '地点',
        'add_stock'                => '添加股票',
        'select_one'               => '选择一个',
        'memo'                     => '备忘录',
        'close'                    => '关',
        'remove_stock'             => '删除股票',
        'move_stock'               => '移动股票',
        'location_from'            => '位置来自',
        'location_to'              => '位置到',
        'item_edit'                => '编辑项目',
        'copy'                     => '复制',
        'store_in'                 => '存储在',
        'order_items'              => '订单商品',
        'delivery_from'            => '从位置交货',
        'user_role_create'         => '创建用户角色',
        'permission'               => '允许',
        'section_name'             => '部分名称',
        'areas'                    => '地区',
        'Add'                      => '加',
        'Edit'                     => '编辑',
        'Delete'                   => '删除',
        'name'                     => 'name',
        'full_name'                => '全名',
        'password'                 => '密码',
        'old_password'             => '旧密码',
        'set_password'             => '设置密码',
        'new_password'             => '新密码',
        'update_password'          => '更新密码',
        'confirm_password'         => '确认密码',
        're_password'              => '重复输入密码',
        'change_password'          => '更改密码',
        'settings'                 => '设置',
        'change_password_form'     => '更改密码表格',
        'user_create_form'         => '创建用户',
        'user_update_form'         => '更新用户',
        'submit'                   => '提交',
        'update'                   => '更新',
        'cancel'                   => '取消',
        'sign_out'                 => '登出',
        'delete'                   => '删除',
        'company_create'           => '创建公司',
        'company'                  => '公司',
        'db_host'                  => '主办',
        'db_user'                  => '数据库用户',
        'db_password'              => '数据库密码',
        'db_name'                  => '数据库名称',
        'new_company_password'     => '新脚本管理员密码',
        'pdf'                      => 'PDF',
        'customer'                 => '顾客',
        'customer_branch'          => '客户分公司',
        'payment_type'             => '支付方式',
        'from_location'            => '地点',
        'add_item'                 => '新增项目',
        'sales_invoice_items'      => '销售发票项目',
        'purchase_invoice_items'   => '购买发票项目',
        'supplier'                 => '供应商',
        'order_item'               => '订单项目',
        'order_date'               => '订单',
        'item_tax_type'            => '税种',
        'currency'                 => '货币',
        'sales_type'               => '销售类型',
        'price'                    => '价钱',
        'supplier_unit_of_messure' => '供应商计量单位',
        'conversion_factor'        => '转换因子（到我们的UOM）',
        'supplier_description'     => '供应商的代码或描述',
        'next'                     => '下一个',
        'add_branch'               => '添加分支',
        'payment_term'             => '付款期限',
        'site_name'                => '网站名称',
        'site_short_name'          => '网站简称',
        'source'                   => '资源',
        'destination'              => '目的地',
        'stock_move'               => '库存转移',
        'after'                    => '后',
        'status'                   => '状态',
        'date'                     => '日期',
        'qty'                      => '数量',
        'terms'                    => '术语',
        'add_new_customer'         => '添加新客户',
        'add_new_order'            => '添加新订单',
        'add_new_invoice'          => '添加新发票',
        'group_name'               => '团队名字',
        'edit'                     => '编辑',
        'title'                    => '标题',
        'description'              => '描述',
        'reminder'                 => '提醒日期',

    ],
    'home'                 => [

        'title-bar'       => [
            'home'      => '家',
            'send'      => '发送',
            'request'   => '请求',
            'developer' => '开发人员',
            'login'     => '登录',
            'register'  => '寄存器',
            'logout'    => '登出',
            'dashboard' => '仪表板',

        ],
        'banner'          => [
            'title'      => '简单的汇款到：亲人',
            'sub-title1' => '简单：集成',
            'sub-title2' => '多个：br钱包',
            'sub-title3' => '高级：br安全',

        ],
        'choose-us'       => [
            'title'      => '为什么选择我们？',
            'sub-title1' => '我们不是银行。有了我们，您可以获得低廉的费用和实时汇率。',
            'sub-title2' => '立即获得家人和朋友的钱，您只需要一个电子邮件地址。',
            'sub-title3' => '转账，提款和兑换货币 - 我们的费用很低。',

        ],
        'payment-gateway' => [
            'title' => '付款处理器',

        ],
        'services'        => [
            't1' => '付款API',
            // 's1' => '它将通过在您的网站中集成我们的无缝API接口来管理客户的付费体验。',
            's1' => '它将管理客户'. $getCompanyName .'通过在您的网站中集成我们的无缝API接口的经验' ,
            't2' => '在线支付',
            's2' => '无论是信用卡，借记卡还是银行账户，您都可以按照自己的方式付款。',
            't3' => '货币兑换',
            's3' => '默认货币到另一个您可以轻松更改。',
            't4' => '付钱请求',
            's4' => '通过这些系统，您现在可以从任何国家/地区向任何国家/地区请求付款。',
            't5' => '凭证系统',
            's5' => '发行和管理您自己的品牌或有天赋的优惠券',
            't6' => '欺诈识别',
            's6' => '这意味着我们可以帮助您保持帐户的安全性和可靠性。享受安全的在线支付。',

        ],
        'how-work'        => [
            'title'      => '这个怎么运作',
            'sub-title1' => '首先，为您的帐户创建存款。',
            'sub-title2' => '决定您要发送多少金额并选择钱包。',
            'sub-title3' => '如果需要，请在短信中写下电子邮件地址。',
            'sub-title4' => '点击发送资金。',
            'sub-title5' => '您也可以兑换货币。',

        ],
    ],
    'send-money'           => [

        'banner'    => [
            'title'     => '汇款适合你的方式',
            'sub-title' => '快速轻松地发送和接收资金或提供优惠券作为礼物。',
            // 'sign-up'   => '注册付钱',
            'sign-up'   => '注册'.$getCompanyName,
            'login'     => '现在登录',

        ],
        'section-a' => [
            'title'         => '在几分钟内用多种货币在全球范围内汇款
                            点击。',

            'sub-section-1' => [
                'title'     => '注册账户',
                'sub-title' => '首先是注册用户，然后登录您的帐户并输入您的卡或银行
                            详细信息是您需要的。',

            ],
            'sub-section-2' => [
                'title'     => '选择您的收件人',
                'sub-title' => '输入您的收件人电子邮件地址，该地址不会与他人共享并保持安全
                            添加货币金额以安全发送。',

            ],
            'sub-section-3' => [
                'title'     => '寄钱',
                'sub-title' => '汇款后，收款人将在收款时通过电子邮件通知
                            转入他们的帐户。',

            ],
        ],
        'section-b' => [
            'title'     => '在一秒钟内汇款。',
            'sub-title' => '拥有电子邮件地址的任何人都可以发送/接收付款请求，无论他们是否拥有帐户。他们可以使用信用卡或银行账户付款',

        ],
        'section-c' => [
            // 'title'     => '随时随地向任何人汇款，即时使用付款系统',
            'title'     => '向任何人，任何地方，即时使用汇款'. $getCompanyName . '系统',
//             'sub-title' => '通过Pay Money移动应用程序银行将资金转移到全球的朋友和家人
//                             帐户或其他支付网关。资金直接进入您的帐户是否收件人
//                             有没有帐户。您可以通过不同类型的付款发送/请求资金
//                             网关与不同的货币。', 
            'sub-title' => '通过以下方式将资金转移到全球的朋友和家人'. $getCompanyName .'移动应用，银行帐户或其他支付网关。无论收件人是否有任何帐户，都会直接进入您的帐户。 您可以通过不同类型的支付网关以不同的货币发送/请求资金。',

        ],
        'section-d' => [
            'title'   => '更快，更简单，更安全 - 向今天爱的人汇款。',
            'sign-up' => '注册'.$getCompanyName,

        ],
        'section-e' => [
            'title'     => '开始汇款。',
            'sub-title' => '现在，您可以轻松获得现金。任何人都可以从银行卡中汇款
                            帐户，贝宝平衡或其他paymentgateway。您将通过简单的电子邮件通知。',

        ],
    ],
    'request-money'        => [

        'banner'    => [
            // 'title'          => '要求来自世界各地的资金：支付金钱',
            'title'          => '从世界各地请钱'. $getCompanyName,
            'sub-title'      => '提醒人们发送退款。',
            'sign-up'        => '注册'.$getCompanyName,
            'already-signed' => '已经注册？',
            'login'          => '登录',
            'request-money'  => '要钱。',

        ],
        'section-a' => [
            'title'         => '用户友好的资金申请系统。',
//             'sub-title'     => '要钱是一种高效而礼貌的方式来索取欠款。 ：br使用
//                             payMoney系统汇款，收款或从您最近的亲人转账
//                             那些。',
            'sub-title'     => '要钱是一种高效而礼貌的方式来索取欠款。 使用'. $getCompanyName. '系统汇款，收款或从您最近的和最亲爱的人转账。',

            'sub-section-1' => [
                'title'     => '注册账户',
                'sub-title' => '首先是注册用户，然后登录您的帐户并输入您的卡或银行
                            详细信息，您需要钱。',

            ],
            'sub-section-2' => [
                'title'     => '选择您的收件人',
                'sub-title' => '获取您的收件人电子邮件地址，该地址不会与他人共享并保持安全
                            添加货币金额以安全发送。',

            ],
            'sub-section-3' => [
                'title'     => '申请资金',
                'sub-title' => '在请求资金后，收款人将在钱存款时通过电子邮件通知
                            从他们的帐户转移。',

            ],
        ],
        'section-b' => [
            'title'     => '可以通过手机发送钱',
            'sub-title' => '现在，您可以轻松获得现金。任何人都可以从银行卡中汇款
                            帐户，贝宝平衡或其他paymentgateway。您将通过简单的电子邮件通知。',

        ],
        'section-c' => [
            // 'title'     => '使用payMoney移动应用程序轻松申请资金。',
            'title'     => '使用'. $getCompanyName .'移动应用程序轻松申请资金。',
            'sub-title' => '拥有电子邮件地址的任何人都可以收到付款请求，无论他们是否有帐户或
                            不。他们可以用paypal，条纹，2checkout和更多的paygateway支付给你。',

        ],
        'section-d' => [
            // 'title'     => '向任何人，任何地方，即时使用payMoney系统请求资金', 
            'title'     => '向任何人，任何地方，即时使用请求金钱' . $getCompanyName . '系统',
//             'sub-title' => '通过payMoney移动应用程序银行将资金转移到全球的朋友和家人
//                             帐户或其他支付网关。无论收款人是谁，资金都会直接进入您的帐户
//                             有没有帐户。您可以通过不同类型的付款发送/请求资金
//                             网关与不同的货币。',

            'sub-title' => '通过以下方式将资金转移到全球的朋友和家人' . $getCompanyName . '移动应用，银行帐户或其他支付网关。 无论收件人是否有任何帐户，资金都会直接进入您的帐户。 您可以通过不同类型的支付网关以不同的货币发送/请求资金。',

        ],
        'section-e' => [
            'title'   => '更快，更简单，更安全 - 向今天爱的人汇款。',
            'sign-up' => '注册'.$getCompanyName,

        ],
    ],
    'login'                => [
        'title'           => '登录',
        'form-title'      => '登入',
        'email'           => '电子邮件',
        'phone'           => '电话',
        'email_or_phone'  => '邮件或者电话',
        'password'        => '密码',
        'forget-password' => '忘记密码？',
        'no-account'      => '没有账号？',
        'sign-up-here'    => '在此注册',

    ],
    'registration'         => [
        'title'                => '注册',
        'form-title'           => '创建新用户',
        'first-name'           => '名字',
        'last-name'            => '姓',
        'email'                => '电子邮件',
        'phone'                => '电话',
        'password'             => '密码',
        'confirm-password'     => '确认密码',
        'terms'                => '点击注册，即表示您同意我们的条款，数据政策和Cookie政策。',
        'new-account-question' => '已经有账号？',
        'sign-here'            => '在这里登录',
        'type-title'           => '类型',
        'type-user'            => '用户',
        'type-merchant'        => '商人',
        'select-user-type'     => '选择用户类型',

    ],
    'dashboard'            => [

        'nav-menu'     => [
            'dashboard'    => '仪表板',
            'transactions' => '交易',
            'send-req'     => '发送请求',
            'send-to-bank' => '发送到银行',
            'vouchers'     => '优惠券',
            'merchants'    => '招商',
            'disputes'     => '争议',
            'settings'     => '设置',
            'tickets'      => '门票',
            'logout'       => '登出',
            'payout'       => '赔率',
            'exchange'     => '交换',

        ],
        'left-table'   => [
            'title'            => '近期活动',
            'date'             => '日期',
            'description'      => '描述',
            'status'           => '状态',
            'currency'         => '货币',
            'amount'           => '量',
            'view-all'         => '查看全部',
            'no-transaction'   => '没有找到交易！',
            'details'          => '细节',
            'fee'              => '费用',
            'total'            => '总',
            'transaction-id'   => '交易ID',
            'transaction-date' => '交易日期',

            'deposit'          => [
                'deposited-to'     => '存入',
                'payment-method'   => '付款方法',
                'deposited-amount' => '存款金额',
                'deposited-via'    => '存放在Via',

            ],
            'withdrawal'       => [
                'withdrawan-with'   => '支付',
                'withdrawan-amount' => '支付金额',

            ],
            'transferred'      => [
                'paid-with'          => '支付',
                'transferred-amount' => '转移金额',
                'email'              => '电子邮件',
                'note'               => '注意',

            ],
            'bank-transfer'    => [
                'bank-details'        => '银行明细',
                'bank-name'           => '银行名',
                'bank-branch-name'    => '分店名称',
                'bank-account-name'   => '用户名',
                'bank-account-number' => '帐号', //pm1.9
                'transferred-with'    => '转移',
                'transferred-amount'  => '银行转账金额',

            ],
            'received'         => [
                'paid-by'         => '由...支付',
                'received-amount' => '收到金额',

            ],
            'exchange-from'    => [
                'from-wallet'          => '来自钱包',
                'exchange-from-amount' => '交换金额',
                'exchange-from-title'  => '交换来自',
                'exchange-to-title'    => '交换到',

            ],
            'exchange-to'      => [
                'to-wallet' => '到钱包',

            ],
            'voucher-created'  => [
                'voucher-code'   => '优惠券代码',
                'voucher-amount' => '凭证金额',

            ],
            'request-to'       => [
                'accept' => '接受',

            ],
            'payment-Sent'     => [
                'payment-amount' => '支付金额',

            ],
        ],
        'right-table'  => [
            'title'                => '钱包',
            'no-wallet'            => '找不到钱包了！',
            'default-wallet-label' => '默认',
        ],
        'button'       => [
            'deposit'         => '存款',
            'withdraw'        => '赔率',
            'payout'          => '赔率',
            'exchange'        => '交换',
            'submit'          => '提交',
            'send-money'      => '寄钱',
            'send-request'    => '发送请求',
            'create'          => '创建',
            'activate'        => '启用',
            'new-merchant'    => '新商人',
            'details'         => '细节',
            'change-picture'  => '更换图片',
            'change-password' => '更改密码',
            'new-ticket'      => '新票',
            'next'            => '下一个',
            'back'            => '背部',
            'confirm'         => '确认',
            'select-one'      => '选择一个',
            'update'          => '更新',
            'filter'          => '过滤',

        ],
        'deposit'      => [
            'title'                                       => '存款',
            'deposit-via'                                 => '存款通过',
            'amount'                                      => '量',
            'currency'                                    => '货币',
            'payment-method'                              => '付款方法',
            'no-payment-method'                           => '找不到付款方式！',
            'fees-limit-payment-method-settings-inactive' => '费用限制和付款方式设置均处于非活动状态',
            'total-fee'                                   => '总计：',
            'total-fee-admin'                             => '总：',
            'fee'                                         => '费用',
            'deposit-amount'                              => '存款金额',
            'completed-success'                           => '存款成功完成',
            'success'                                     => '成功',
            'deposit-again'                               => '再次存钱',

            'deposit-stripe-form'                         => [
                'title'   => '存放条纹',
                'card-no' => '卡号',
                'mm-yy'   => 'MM / YY',
                'cvc'     => 'CVC',

            ],
            'select-bank'                                 => '选择银行', //pm1.9
            'payment-references'                          => [
                // 'merchant-payment-reference'   => '商家付款参考',
                'user-payment-reference' => '用户付款参考',
                // 'merchant-payment-number'   => '商家付款号码',
            ],
        ],
        'payout'       => [

            'menu'           => [
                'payouts'        => '赔率',
                'payout-setting' => '付款设置',
                'new-payout'     => '新的支付',

            ],
            'list'           => [
                'method'      => '方法',
                'method-info' => '方法信息',
                'charge'      => '收费',
                'amount'      => '量',
                'currency'    => '货币',
                'status'      => '状态',
                'date'        => '日期',
                'not-found'   => '找不到数据！',
                'fee'         => '费用',

            ],
            'payout-setting' => [
                'add-setting' => '添加设置',
                'payout-type' => '支付类型',
                'account'     => '帐户',
                'action'      => '行动',

                'modal'       => [
                    'title'                        => '添加付款设置',
                    'payout-type'                  => '支付类型',
                    'email'                        => '电子邮件',
                    'bank-account-holder-name'     => '银行账户持有人的姓名',
                    'branch-name'                  => '分店名称',
                    'account-number'               => '银行帐号/ IBAN',
                    'branch-city'                  => '分市',
                    'swift-code'                   => 'SWIFT代码',
                    'branch-address'               => '分支地址',
                    'bank-name'                    => '银行名',
                    'attached-file'                => '附件',
                    'country'                      => '国家',
                    'perfect-money-account-number' => '完美的货币帐号',
                    'payeer-account-number'        => 'Payeer账号',

                ],
            ],
            'new-payout'     => [
                'title'          => '赔率',
                'payment-method' => '付款方法',
                'currency'       => '货币',
                'amount'         => '量',
                'bank-info'      => '银行账户信息',
                'withdraw-via'   => '你将要通过支付款项',
                'success'        => '成功',
                'payout-success' => '付款已成功完成',
                'payout-again'   => '再次付款',

            ],
        ],
        'confirmation' => [
            'details' => '细节',
            'amount'  => '量',
            'fee'     => '费用',
            'total'   => '总',

        ],
        'transaction'  => [
            'date-range'      => '选择一个日期范围',
            'all-trans-type'  => '所有交易类型',
            'payment-sent'    => '付款已发送',
            'payment-receive' => '已收到付款',
            'payment-req'     => '付钱请求',
            'exchanges'       => '交易所',
            'all-status'      => '所有状态',
            'all-currency'    => '所有货币',
            'success'         => '成功',
            'pending'         => '有待',
            'blocked'         => '取消',
            'refund'          => '退款',
            'open-dispute'    => '公开争议',

        ],
        'exchange'     => [
            'left-top'    => [
                'title'           => '货币兑换',
                'select-wallet'   => '选择钱包',
                'amount-exchange' => '交换金额',
                'give-amount'     => '你会给',
                'get-amount'      => '你会得到',
                'balance'         => '平衡',
                'from-wallet'     => '来自钱包',
                'to-wallet'       => '到钱包',
                'base-wallet'     => '来自钱包',
                'other-wallet'    => '到钱包',
                'type'            => '交换类型',
                'type-text'       => '基础货币是：',
                'to-other'        => '其他货币',
                'to-base'         => '基础货币',
            ],
            'left-bottom' => [
                'title'            => '货币兑换（基础货币）',
                'exchange-to-base' => '交换到基地',
                'wallet'           => '钱包',
            ],
            'right'       => [
                'title' => '汇率',
            ],
            'confirm'     => [
                'title'                => '兑换外币',
                'exchanging'           => '交换',
                'of'                   => '的',
                'equivalent-to'        => '相当于',
                'exchange-rate'        => '汇率',
                'amount'               => '交换金额',
                'has-exchanged-to'     => '交换了',
                'exchange-money-again' => '再次兑换货币',

            ],
        ],
        'send-request' => [

            'menu'         => [
                'send'    => '发送',
                'request' => '请求',

            ],
            'send'         => [
                'title'        => '寄钱',

                'confirmation' => [
                    'title'              => '寄钱',
                    'send-to'            => '你要汇款给',
                    'transfer-amount'    => '转账金额',
                    'money-send'         => '资金成功转移',
                    'bank-send'          => '资金成功转入银行',
                    'send-again'         => '再次发送钱',
                    'send-to-bank-again' => '转移到银行再次',

                ],
            ],
            'send-to-bank' => [
                'title'        => '转移到银行',
                'subtitle'     => '转账到银行',

                'confirmation' => [
                    'title'           => '转账到银行',
                    'send-to'         => '你要汇款给',
                    'transfer-amount' => '转账金额',
                    'money-send'      => '资金成功转移',
                    'send-again'      => '再次汇款',

                ],
            ],
            'request'      => [
                'title'        => '申请资金',

                'confirmation' => [
                    'title'              => '申请资金',
                    'request-money-from' => '你要钱',
                    'requested-amount'   => '要求金额',
                    'success'            => '成功',
                    'success-send'       => '资金申请成功发送',
                    'request-amount'     => '申请金额',
                    'request-again'      => '再次申请金钱',

                ],
                'success'      => [
                    'title'            => '接受请求金钱',
                    'request-complete' => '请求的金钱成功获得',
                    'accept-amount'    => '接受金额',

                ],
                'accept'       => [
                    'title' => '接受请求付款',

                ],
            ],
            'common'       => [
                'recipient'   => '接受者',
                'amount'      => '量',
                'currency'    => '货币',
                'note'        => '注意',
                'anyone-else' => '我们绝不会与其他任何人分享您的电子邮件。',
                'enter-note'  => '输入注释',
                'enter-email' => '输入Email',

            ],
        ],
        'vouchers'     => [

            'left-top'            => [
                'title'    => '创建凭证',
                'amount'   => '量',
                'currency' => '货币',

            ],
            'left-bottom'         => [
                'title' => '激活优惠券',
                'code'  => '码',

            ],
            'right'               => [
                'title'     => '优惠券',
                'code'      => '码',
                'amount'    => '量',
                'status'    => '状态',
                'not-found' => '凭证未找到！',

            ],
            'confirmation'        => [
                'title'     => '创建凭证',
                'sub-title' => '您即将创建凭证',
                'amount'    => '凭证金额',

            ],
            'active-confirmation' => [
                'title'     => '激活的Vouched',
                'sub-title' => '您即将激活优惠券代码',
                'amount'    => '凭证金额',

            ],
            'success'             => [
                'title'   => '凭证',
                'success' => '成功',
                'amount'  => '凭证金额',
                'print'   => '打印',

            ],
            'ajax-response'       => [
                'voucher-code-error'         => '优惠券已经激活！',
                'voucher-code-pending-error' => '对不起，无法激活待定凭证！',
                'voucher-not-found'          => '对不起，没有找到凭证！',

            ],
        ],
        'merchant'     => [

            'menu'                => [
                'merchant'      => '招商',
                'payment'       => '支付',
                'list'          => '名单',
                'details'       => '细节',
                'edit-merchant' => '编辑商家',
                'new-merchant'  => '新商人',

            ],
            'table'               => [
                'id'            => 'ID',
                'business-name' => '商家名称',
                'site-url'      => '网站网址',
                'type'          => '类型',
                'status'        => '状态',
                'action'        => '行动',
                'not-found'     => '找不到数据！',
                'moderation'    => '适度',
                'disapproved'   => '拒登',
                'approved'      => '批准',

            ],
            'html-form-generator' => [
                'title'             => 'HTML表单生成器',
                'merchant-id'       => '商家ID',
                'item-name'         => '项目名称',
                'order-number'      => '订单号',
                'price'             => '价钱',
                'custom'            => '习惯',
                'right-form-title'  => '示例HTML表单',
                'right-form-copy'   => '复制',
                'right-form-copied' => '复制',
                'right-form-footer' => '复制表单代码并将其放在您的网站上。',
                'close'             => '关',
                'generate'          => '生成',
                'app-info'          => '应用信息',
                'client-id'         => '客户ID',
                'client-secret'     => '客户秘密',

            ],
            'payment'             => [
                'merchant'   => '商人',
                'method'     => '方法',
                'order-no'   => '订单号',
                'amount'     => '量',
                'fee'        => '费用',
                'total'      => '总',
                'currency'   => '货币',
                'status'     => '状态',
                'created-at' => '日期',
                'pending'    => '有待',
                'success'    => '成功',
                'block'      => '块',
                'refund'     => '退',

            ],
            'add'                 => [
                'title'    => '创建商家',
                'name'     => 'name',
                'site-url' => '网站网址',
                'type'     => '类型',
                'note'     => '注意',
                'logo'     => '商标',

            ],
            'details'             => [
                'merchant-id'   => '商家ID',
                'business-name' => '商家名称',
                'status'        => '状态',
                'site-url'      => '网站网址',
                'note'          => '注意',
                'date'          => '日期',

            ],
            'edit'                => [
                'comment-for-administration' => '对行政的评论',

            ],
        ],
        'dispute'      => [
            'dispute'        => '争议',
            'title'          => '标题',
            'dispute-id'     => '争议ID',
            'transaction-id' => '交易ID',
            'created-at'     => '创建于',
            'status'         => '状态',
            'no-dispute'     => '找不到数据！',
            'defendant'      => '被告',
            'claimant'       => '索赔',
            'description'    => '描述',

            'status-type'    => [
                'open'   => '打开',
                'solved' => '解决了',
                'closed' => '关闭',
                'solve'  => '解决',
                'close'  => '关',

            ],
            'discussion'     => [

                'sidebar' => [
                    'title-text'    => '争议信息',
                    'header'        => '争议信息',
                    'title'         => '标题',
                    'reason'        => '原因',
                    'change-status' => '改变状态',

                ],
                'form'    => [
                    'title'   => '查看争议',
                    'message' => '信息',
                    'file'    => '文件',

                ],
            ],
        ],
        'setting'      => [
            'title'                   => '用户资料',
            'change-avatar'           => '改变头像',
            'change-avatar-here'      => '你可以在这里更改头像',
            'change-password'         => '更改密码',
            'change-password-here'    => '您可以在此处更改密码',
            'profile-information'     => '档案信息',
            'email'                   => '电子邮件',
            'first-name'              => '名字',
            'last-name'               => '姓',
            'mobile'                  => '手机号码',
            'address1'                => '地址1',
            'address2'                => '地址2',
            'city'                    => '市',
            'state'                   => '州',
            'country'                 => '国家',
            'timezone'                => '时区',
            'old-password'            => '旧密码',
            'new-password'            => '新密码',
            'confirm-password'        => '确认密码',
            'add-phone'               => '添加电话',
            'add-phone-subhead1'      => '点击',
            'add-phone-subhead2'      => '添加手机',
            'add-phone-subheadertext' => '输入您要使用的号码',
            'get-code'                => '获取代码',
            'phone-number'            => '电话号码',
            'edit-phone'              => '编辑电话',
            'default-wallet'        => '默认钱包',
        ],
        'ticket'       => [
            'title'     => '门票',
            'ticket-no' => '机票号',
            'subject'   => '学科',
            'status'    => '状态',
            'priority'  => '优先',
            'date'      => '日期',
            'action'    => '行动',
            'no-ticket' => '数据未找到！',

            'add'       => [
                'title'    => '新票',
                'name'     => 'name',
                'message'  => '信息',
                'priority' => '优先',

            ],
            'details'   => [

                'sidebar' => [
                    'header'    => '票务信息',
                    'ticket-id' => '机票ID',
                    'subject'   => '学科',
                    'date'      => '日期',
                    'priority'  => '优先',
                    'status'    => '状态',

                ],
                'form'    => [
                    'title'   => '查看票证',
                    'message' => '信息',
                    'file'    => '文件',

                ],
            ],
        ],
    ],
    'express-payment'      => [
        'payment'           => '付款',
        'pay-with'          => '使用。。。支付',
        'about-to-make'     => '您即将通过付款',
        'test-payment-form' => '测试付款表格',
        'pay-now'           => '现在付款！',
    ],

    'express-payment-form' => [
        'merchant-not-found'   => '找不到商家！请尝试使用有效的商家。',
        'merchant-found'       => '收件人进行了特殊验证并确认了他的可靠性',
        'continue'             => '继续',
        'email'                => '电子邮件',
        'password'             => '密码',
        'cancel'               => '取消',
        'go-to-payment'        => '转到付款',
        'payment-agreement'    => '付款在安全页面上进行。付款时，您同意协议条款',
        'debit-credit-card'    => '信用卡/借记卡',
        'merchant-payment'     => '商家付款',
        'sorry'                => '抱歉!',
        'payment-unsuccessful' => '付款不成功。',
        'success'              => '成功！',             //
        'payment-successfull'  => '付款顺利完成。', //
        'back-home'            => '回家',
    ],
];
