<?php

/**
 * KJP Account Charger
 * languages
 */

// Prevent illegal run
if (! defined('IN_PLUGINS_SYSTEM'))
{
    exit();
}

$KJP_LANG = [
    'en' => [
        'KJP_ACT_CHARGE_ACCOUNT'                   => 'Charging Account %s',
        'KJP_CHRG_ACNT'                            => 'Charge Account',
        'KJP_CHRG_AMNT'                            => 'Charging Amount',
        'KJP_CHRG_MTHD'                            => 'Charging Method',
        'KJP_CHRG'                                 => 'Charge',
        'KJP_ACT_ARCH_CHARGE_ACCOUNT'              => 'Charging Accounts',
        'KJP_MIN_CHARGE_AMOUNT'                    => 'the minimal Amount for Charging',
        'KJP_MIN_CHARGE_IS'                        => 'the minimal Amount for Charging is %s',
        'KJP_ACC_CHARGEED'                         => 'your account have been charged with %s',
    ],
    'ar' => [
        'KJP_ACT_CHARGE_ACCOUNT'               => 'تعبئة حساب %s',
        'KJP_CHRG_ACNT'                        => 'تعبئة حساب',
        'KJP_CHRG_AMNT'                        => 'شحن بمبلغ',
        'KJP_CHRG_MTHD'                        => 'طريقة التعبئة',
        'KJP_CHRG'                             => 'تعبئة',
        'KJP_ACT_ARCH_CHARGE_ACCOUNT'          => 'تعبئة حساب',
    ]
];

return (isset($KJP_LANG[$config['language']]) ? $KJP_LANG[$config['language']] : $KJP_LANG['en']);
