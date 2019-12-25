<?php
return [
    'check_voucher_code'            => '对不起，不正确的优惠券代码！',
    'alpha_spaces'                  => ':attribute 必须有空格和字符',
    'default_wallet_balance'        => '对不起，没有足够的资金来执行操作',
    'check_wallet_balance'          => '对不起，没有足够的资金来执行操作',
    'accepted'                      => ':attribute 必须被接受。',
    'active_url'                    => ':attribute 不是有效的URL。',
    'after'                         => ':attribute 必须是 :date 之后的日期。',
    'after_or_equal'                => ':attribute 必须是 :date 之后的日期。',
    'alpha'                         => ':attribute 可能只包含字母。',
    'alpha_dash'                    => ':attribute 可能只包含字母，数字和短划线。',
    'alpha_num'                     => ':attribute 可能只包含字母和数字。',
    'array'                         => ':attribute 必须是 一个数组。', //check
    'before'                        => ':attribute 必须是 :date之前的日期。',
    'before_or_equal'               => ':attribute 必须是 :date之前或之前的日期。',

    'between'                       => [
        'numeric' => ':attribute 必须介于 :min 和 :max 之间。',
        'file'    => ':attribute 必须介于 :min 和 :max 千字节之间。',
        'string'  => ':attribute 必须在 :min 和 :max 字符之间。',
        'array'   => ':attribute 必须介于 :min 和 :max 之间。',

    ],

    'boolean'                       => ':attribute 字段必须为true或false。',
    'confirmed'                     => ':attribute 确认不匹配。',
    'date'                          => ':attribute 不是有效日期。',
    'date_format'                   => ':attribute 与 :format 格式不匹配。',
    'different'                     => ':attribute 和 :other 必须不同。',
    'digits'                        => ':attribute 必须是 :digits 位数。',
    'digits_between'                => ':attribute 必须在 :min 和 :max 位之间。',
    'dimensions'                    => ':attribute 具有无效的图像尺寸。',
    'distinct'                      => ':attribute 字段具有重复值。',
    'email'                         => ':attribute 必须是有效的电子邮件地址。',
    'exists'                        => '选定的 :attribute 无效。',
    'file'                          => ':attribute 必须是文件。',
    'filled'                        => ':attribute 字段必须具有值。',
    'image'                         => ':attribute 必须是图像。',
    'in'                            => '选定的 :attribute 无效。',
    'in_array'                      => ':attribute 中不存在 :other 字段。',
    'integer'                       => ':attribute 必须是整数。',
    'ip'                            => ':attribute 必须是有效的IP地址。',
    'ipv4'                          => ':attribute 必须是有效的IPv4地址。',
    'ipv6'                          => ':attribute 必须是有效的IPv6地址。',
    'json'                          => ':attribute 必须是有效的JSON字符串。',

    'max'                           => [
        'numeric' => ':attribute 可能不大于 :max 。',
        'file'    => ':attribute 可能不大于 :max 千字节。',
        'string'  => ':attribute 可能不大于 :max 个字符。',
        'array'   => ':attribute 可能没有超过 :max 项。',
    ],
    'mimes'                         => ':attribute 必须是类型为 :values 的文件。',
    'mimetypes'                     => ':attribute 必须是类型为 :values 的文件。',

    'min'                           => [
        'numeric' => ':attribute 必须至少 :min 。',
        'file'    => ':attribute 必须至少为 :min 千字节。',
        'string'  => ':attribute 必须至少为 :min 个字符。',
        'array'   => ':attribute 必须至少有 :min 项。',

    ],
    'not_in'                        => '选定的 :attribute 无效。',
    'numeric'                       => ':attribute 必须是数字。',
    'present'                       => '必须存在 :attribute 字段。',
    'regex'                         => ':attribute 格式无效。',
    'required'                      => ':attribute 字段是必需的。',
    'required_if'                   => '当 :attribute 为 :value 时，:other 字段是必需的。',
    'required_unless'               => '除非 :attribute 在 :values 中，否则 :other 字段是必需的。',
    'required_with'                 => '当 :attribute 存在时，:values 字段是必需的。',
    'required_with_all'             => '当 :attribute 存在时，:values 字段是必需的。',
    'required_without'              => '当 :attribute 不存在时，:values 字段是必需的。',
    'required_without_all'          => '当 :attribute 都不存在时，:values 字段是必需的。',
    'same'                          => ':attribute 和 :other 必须匹配。',

    'size'                          => [
        'numeric' => ':attribute 必须是 :size 。',
        'file'    => ':attribute 必须是 :size千字节。',
        'string'  => ':attribute 必须是 :size字符。',
        'array'   => ':attribute 必须包含:size项。',

    ],
    'string'                        => ':attribute 必须是一个字符串。',
    'timezone'                      => ':attribute 必须是有效区域。',
    'unique'                        => ':attribute 已经被采取。',
    'uploaded'                      => ':attribute 无法上传。',
    'url'                           => ':attribute 格式无效。',
    'unique_merchant_business_name' => ':attribute 必须是唯一的',

    'custom'                        => [
        'attribute-name' => [
            'rule-name' => '定制消息',
        ],
    ],
    'attributes'                    => [

    ]];
