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

    'accepted'   => '此 :attribute 必須接受',
    'active_url' => '此 :attribute 不是有效的網址',
    'after'      => '此日期 :attribute 必須是 :date 以後',
    'alpha'      => '此 :attribute 只能包含字母',
    'alpha_dash' => '此 :attribute 只能包含字母，數字和破折號',
    'alpha_num'  => '此 :attribute 只能包含字母和數字',
    'array'      => '此 :attribute 必須是數組',
    'before'     => '此日期 :attribute 必須是 :date. 之前',
    'between'    => [
        'numeric' => '此數字 :attribute必須在 :min 和 :max 之間',
        'file'    => '此文件 :attribute 必須在 :min 和 :max 千字節之間',
        'string'  => '此字串 :attribute 必須在 :min 和 :max 字符之間.',
        'array'   => '此數組 :attribute 必須在 :min 和 :max 項目之間',
    ],
    'boolean'        => '此 :attribute 字段必須為true或false.',
    'confirmed'      => '此 :attribute 已確認不匹配',
    'date'           => '此 :attribute 不是有效的日期',
    'date_format'    => '此 :attribute 與此格式 :format 不匹配',
    'different'      => '此 :attribute 和 :other 必須不同',
    'digits'         => '此 :attribute 必須是 :digits 位數字',
    'digits_between' => '此 :attribute 必須在 :min 和 :max 位數字之間',
    'email'          => '此 :attribute 必須是一個有效的電郵地址。',
    'filled'         => '此 :attribute 是必填欄',
    'exists'         => '已選 :attribute 是無效的',
    'image'          => '此 :attribute 必須是圖像',
    'in'             => '此已選 :attribute 是無效的',
    'integer'        => '此 :attribute 必須是整數',
    'ip'             => '此 :attribute 必須是有效的IP地址',
    'max'            => [
        'numeric' => '此 :attribute 不可能大於 :max',
        'file'    => '此 :attribute 不可能大於 :max 千字節',
        'string'  => '此 :attribute 不可能大於 :max 字符',
        'array'   => '此 :attribute 不可能超過 :max 項目',
    ],
    'mimes' => '此 :attribute 必須是以下類型的文件： :values.',
    'min'   => [
        'numeric' => '此 :attribute 必須至少 :min',
        'file'    => '此 :attribute 必須至少 :min 千字節.',
        'string'  => '此 :attribute 必須至少 :min 字符',
        'array'   => '此 :attribute 必須至少有 :min 項目',
    ],
    'not_in'               => '已選 :attribute 是無效的',
    'numeric'              => '此 :attribute 必須是數字',
    'regex'                => '此 :attribute 格式無效。',
    'required'             => '需要此 :attribute 格式.',
    'required_if'          => '此 :attribute 是必填欄，因爲 :other 等於 :value.',
    'required_with'        => '當 :values 存在，此 :attribute 屬必填欄',
    'required_with_all'    => '當 :values 存在，此 :attribute 屬必填欄',
    'required_without'     => '當 :values 不存在，此 :attribute field 屬必填欄',
    'required_without_all' => '當這些 :values 都不存在，此 :attribute field 屬必填欄 ',
    'same'                 => '此 :attribute 和 :other 必須匹配',
    'size'                 => [
        'numeric' => '此數字大小 :attribute 必須等於 :size.',
        'file'    => '此文件大小 :attribute 必須等於 :size 千字節',
        'string'  => '此字串 :attribute 必須等於 :size 字符',
        'array'   => '此數組 :attribute 必須包含 :size 項目 ',
    ],
    'unique'   => '此 :attribute 已經被拿走了',
    'url'      => '此 :attribute 格式無效',
    'timezone' => '此 :attribute 必須是有效區域',
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
    'custom' => [
        'attribute-name' => [
            'rule-name' => '自定義信息',
        ],
    ],
    /*
      |--------------------------------------------------------------------------
      | Custom Validation Attributes
      |--------------------------------------------------------------------------
      |
      | The following language lines are used to swap attribute place-holders
      | with something more reader friendly such as E-Mail Address instead
      | of "email". This simply helps us make messages a little cleaner.
      |
     */
    'attributes' => [],
];
