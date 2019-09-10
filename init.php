<?php
// Kleeja Plugin
// KJPAY_ACCOUNT_CHARGER
// Version: 1.0
// Developer: Kleeja Team

// Prevent illegal run
if (! defined('IN_PLUGINS_SYSTEM'))
{
    exit();
}


// Plugin Basic Information
$kleeja_plugin['kjp_account_charger']['information'] = [
    // The casual name of this plugin, anything can a human being understands
    'plugin_title' => [
        'en' => 'KJPay Account Charger', // TOP UP :)
        'ar' => 'تعبئة حساب'
    ],
    // Who wrote this plugin?
    'plugin_developer' => 'Kleeja Team',
    // This plugin version
    'plugin_version' => '1.0',
    // Explain what is this plugin, why should I use it?
    'plugin_description' => [
        'en' => 'Charge user accounts when KJPay exists and the group have recaive profits permissions',
        'ar' => 'قم بشحن حسابات المستخدمين عند وجود اضافة مدفوعات كليجا'
    ],
    // Min version of Kleeja that's requiered to run this plugin
    'plugin_kleeja_version_min' => '3.1.5',
    // Max version of Kleeja that support this plugin, use 0 for unlimited
    'plugin_kleeja_version_max' => '3.9',
    // Should this plugin run before others?, 0 is normal, and higher number has high priority
    'plugin_priority' => 0
];

//after installation message, you can remove it, it's not requiered
$kleeja_plugin['kjp_account_charger']['first_run']['ar'] = '
باستخدام هذا المكون الإضافي ، يمكنك السماح للمستخدمين بشحن حساباتهم لشراء الملفات برصيدهم <br>
إنه لا يعمل بدون (kleeja_payment)
';
$kleeja_plugin['kjp_account_charger']['first_run']['en'] = '
With this plugin you can allow your users to charge their accounts to buy files with their balance <br>
it\'s not working without (kleeja_payment) plugin
';


// Plugin Installation function
$kleeja_plugin['kjp_account_charger']['install'] = function ($plg_id) {
    if (! defined('support_kjPay'))
    {
        // Don't install this plugin if kleeja_payment is not installed
        $ERR = 
        [
            'ar' => 'هذه عبارة عن ملحق لإضافة `kleeja_payment` ، الرجاء تثبيته ثم تثبيت هذا الملحق' ,
            'en' => 'this is a package of `kleeja_payment` plugin , Please install it then install this plugin'
        ];

        global $SQL , $dbprefix , $config;

        $SQL->query("DELETE FROM `{$dbprefix}plugins` WHERE `plg_id` = {$plg_id}");

        kleeja_admin_err(
            $ERR[$config['language']] ?? $ERR['en']
        );

        exit;
    }

    add_config_r(
        ['kjp_min_charge_amount' =>
            [
                'value'  => '10',
                'html'   => configField('kjp_min_charge_amount'),
                'plg_id' => $plg_id,
                'type'   => 'kleeja_payment',
            ]
        ]
    );

    KJP::addLang([
        'KJP_ACT_CHARGE_ACCOUNT'               => 'Charging Account %s',
        'KJP_CHRG_ACNT'                        => 'Charge Account',
        'KJP_CHRG_AMNT'                        => 'Charging Amount',
        'KJP_CHRG_MTHD'                        => 'Charging Method',
        'KJP_CHRG'                             => 'Charge',
        'KJP_ACT_ARCH_CHARGE_ACCOUNT'          => 'Charging Accounts',
        'KJP_MIN_CHARGE_AMOUNT'                => 'the minimal Amount for Charging',
        'KJP_MIN_CHARGE_IS'                    => 'the minimal Amount for Charging is %s',
        'KJP_ACC_CHARGEED'                     => 'your account have been charged with %s',
    ], 'en');


    KJP::addLang([
        'KJP_ACT_CHARGE_ACCOUNT'               => 'تعبئة حساب %s',
        'KJP_CHRG_ACNT'                        => 'تعبئة حساب',
        'KJP_CHRG_AMNT'                        => 'شحن بمبلغ',
        'KJP_CHRG_MTHD'                        => 'طريقة التعبئة',
        'KJP_CHRG'                             => 'تعبئة',
        'KJP_ACT_ARCH_CHARGE_ACCOUNT'          => 'تعبئة حساب',
        'KJP_MIN_CHARGE_AMOUNT'                => 'الحد الأدنى لمبلغ الشحن',
        'KJP_MIN_CHARGE_IS'                    => 'الحد الأدنى لمبلغ الشحن هو %s',
        'KJP_ACC_CHARGEED'                     => 'تم تعبئة حسابك ب %s',
    ]);
};


//Plugin update function, called if plugin is already installed but version is different than current
$kleeja_plugin['kjp_account_charger']['update'] = function ($old_version, $new_version) {
};


// Plugin Uninstallation, function to be called at unistalling
$kleeja_plugin['kjp_account_charger']['uninstall'] = function ($plg_id) {
    delete_config('kjp_min_charge_amount');

    // remember : Dont delete olang , becuse KJP will need it later
};


// Plugin functions
$kleeja_plugin['kjp_account_charger']['functions'] = [

    'default_usrcp_page' => function ($args) {
        if (! defined('support_kjPay') || ! user_can('recaive_profits'))
        {
            return;
        }
        global $THIS_STYLE_PATH_ABS ,$olang ,$lang, $usrcp , $config;

        if (g('go') == 'charge_account')
        {
            if (ip('start_charge'))
            {
                if (! kleeja_check_form_key('TOPUP' . $usrcp->name() . $usrcp->id())
                || ! kleeja_check_form_key_get('TOPUP' . $usrcp->name() . $usrcp->id())
                ) {
                    kleeja_err($lang['INVALID_FORM_KEY']);

                    exit;
                }
                elseif ($config['kjp_min_charge_amount'] > p('charge_amount'))
                {
                    kleeja_err(sprintf($olang['KJP_MIN_CHARGE_IS'], $config['kjp_min_charge_amount'] . ' ' . $config['iso_currency_code']));

                    exit;
                }
                elseif (! (int) p('charge_amount') || empty(p('charge_amount')))
                {
                    kleeja_err('it\'s not a vailed amount', '', true, $config['siteurl'] . 'ucp.php?go=charge_account');

                    exit;
                }

                redirect($config['siteurl'] . 'go.php?go=kj_payment&method=' . p('charge_method') . '&action=charge_account&amount=' . p('charge_amount'));

                exit;
            }
            $titlee        = $olang['KJP_CHRG_ACNT'];
            $no_request    = false;
            $stylee        = 'charge_page';
            $styleePath    = file_exists($THIS_STYLE_PATH_ABS . 'kj_payment/charge_page.html') ? $THIS_STYLE_PATH_ABS : dirname(__FILE__);
            $payMethods    = [];
            $kjFormKeyGet  = kleeja_add_form_key_get('TOPUP' . $usrcp->name() . $usrcp->id());
            $kjFormKeyPost = kleeja_add_form_key('TOPUP' . $usrcp->name() . $usrcp->id());
            $formAction    = $config['siteurl'] . 'ucp.php?go=charge_account&' . $kjFormKeyGet;


            foreach (getPaymentMethods() as $value)
            {
                // kiki , do you love me :)))
                if ($value == 'balance')
                {
                    continue;
                }
                $payMethods[] = ['name' => $olang['KJP_MTHD_NAME_' . strtoupper($value)] , 'value' => $value];
            }

            return compact('titlee', 'no_request', 'stylee', 'styleePath', 'payMethods',
                           'formAction', 'kjFormKeyPost');
        }
    },

    'KjPay:default_action' => function ($args) {
        global $usrcp , $config , $olang;

        if (! user_can('recaive_profits'))
        {
            return;
        }

        if (g('action') == 'charge_account')
        {
            $PAY = $args['PAY'];

            if ($config['kjp_min_charge_amount'] > (int) g('amount'))
            {
                kleeja_err(sprintf($olang['KJP_MIN_CHARGE_IS'], $config['kjp_min_charge_amount'] . ' ' . $config['iso_currency_code']));
            }
            $request = true;

            $itemInfo = [
                'id'    => $usrcp->id(),
                'name'  => $usrcp->name(),
                'price' => (int) g('amount'),
            ];
            $_SESSION['KJP_CHARGE'] = $itemInfo['price']; // i need this for hook 'KjPay:itemInfoExport_charge_account'
            $PAY->CreatePayment('charge_account', $itemInfo);

            foreach ($PAY->varsForCreatePayment() as $varName => $varValue)
            {
                $GLOBALS[$varName] = $varValue;
            }
            return compact('request');
        }
    },
    'KjPay:itemInfoExport_charge_account' => function($args) {
        global $usrcp;

        if (! isset($_SESSION['KJP_CHARGE']))
        {
            kleeja_err('ERROROOOOOOO');

            exit;
        }
        elseif (! user_can('recaive_profits'))
        {
            return;
        }

        $itemInfo = [
            'id'    => $usrcp->id(),
            'name'  => $usrcp->name(),
            'price' => $_SESSION['KJP_CHARGE'],
        ];
        return compact('itemInfo');
    },
    'KjPay:notFoundedAction_charge_account' => function($args) {
        global $SQL, $dbprefix, $usrcp , $olang , $config;

        if (! $usrcp->name())
        {
            return null;
        }
        elseif (! user_can('recaive_profits'))
        {
            return;
        }
        $user_id  = $usrcp->id();
        $username = $usrcp->name();
        $add_balance = $_SESSION['KJP_CHARGE'];

        $SQL->query("UPDATE {$dbprefix}users SET `balance` = balance+{$add_balance} WHERE id = {$user_id} AND `name` = '{$username}'");

        if ($SQL->affected())
        {
            $toGlobal = [];
            $olang['KJP_JUIN_SUCCESS'] = sprintf($olang['KJP_ACC_CHARGEED'], $_SESSION['KJP_CHARGE'] . ' ' . $config['iso_currency_code']);
            unset($_SESSION['KJP_CHARGE']);
            $toGlobal['olang'] = $olang;
            return compact('toGlobal');
        }
    } ,
    'Saaheader_links_func' => function($args) {
        global $olang;
        // only if payment plugin actived
        if (! defined('support_kjPay'))
        {
            return;
        }
        $side_menu = $args['side_menu'];
        $side_menu[] = ['name' => 'charge_account', 'title' => $olang['KJP_CHRG_ACNT'], 'url' => 'ucp.php?go=charge_account', 'show' => user_can('recaive_profits')];

        if ($args['user_is'])
        {
            // dont forget to do it , to put logout in the end
            $side_menu[] = $side_menu['logout'];
            unset($side_menu['logout']);
        }
        return compact('side_menu');
    },

    'KjPay:KLJ_HELP' => function ($args) {
        $KJP_HELP = $args['KJP_HELP'];
        $KJP_HELP[] = 
        [
            'ID'      => 'KJP_ACC_CHARGE' ,
            'TITLE'   => 'KJP Account Charger Info', 
            'CONTENT' => 'go to <strong>Kleeja Payment Setting </strong> and set the <strong>the minimal Amount for Charging</strong>
            this is the number of minimal amount that the user can include it ,
            & the group of user shoud to have recaive_profits permission . <br>note: the user can not charge his balance by balance method'
        ];
        return compact('KJP_HELP');
    },

    'KjPay:allTrnc_charge_account' => function($args) {
        global $olang;
        $UserById = $args['UserById'];
        $all_trnc_page_title = sprintf($olang['KJP_PAY_OF'], sprintf($olang['KJP_ACT_CHARGE_ACCOUNT'], $UserById[g('item_id')]));
        return compact('all_trnc_page_title');
    }

];
