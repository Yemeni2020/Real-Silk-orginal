<?php

namespace App\Traits;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\EmailTemplate;
use App\Models\LoginSetup;
use App\Models\Shop;
use App\Repositories\EmailTemplatesRepository;
use App\Services\EmailTemplateService;
use App\Utils\Helpers;
use App\Enums\GlobalConstant;
use App\Http\Controllers\InstallController;
use App\Models\Banner;
use App\Models\Product;
use App\Models\BusinessSetting;
use App\Models\NotificationMessage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait UpdateClass
{
    use EmailTemplateTrait;

    public function getProcessAllVersionsUpdates(): void
    {
        Artisan::call('migrate', ['--force' => true]);
        $this->getInsertDataOfVersion('13.0');
        $this->getInsertDataOfVersion('13.1');
        $this->getInsertDataOfVersion('14.0');
        $this->getInsertDataOfVersion('14.1');
        $this->getInsertDataOfVersion('14.2');
        $this->getInsertDataOfVersion('14.3');
        $this->getInsertDataOfVersion('14.3.1');
        $this->getInsertDataOfVersion('14.4');
        $this->getInsertDataOfVersion('14.5');
        $this->getInsertDataOfVersion('14.6');
        $this->getInsertDataOfVersion('14.7');
        $this->getInsertDataOfVersion('14.8');
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return mixed
     */
    public function businessSettingGetOrInsert(string $type, mixed $value): mixed
    {
        $result = BusinessSetting::where(['type' => $type])->first();
        if (!$result) {
            $result = BusinessSetting::create(['type' => $type, 'value' => $value, 'updated_at' => now()]);
        }
        return $result;
    }

    public function updateOrInsertPolicy($type): mixed
    {
        $policy = BusinessSetting::where(['type' => $type])->first();
        if ($policy) {
            $policyValue = json_decode($policy['value'], true);
            if (!isset($policyValue['status'])) {
                BusinessSetting::where(['type' => $type])->update([
                    'value' => json_encode([
                        'status' => 1,
                        'content' => $policyValue['content'] ?? $policy['value'],
                    ]),
                ]);
            }
        } else {
            $policy = $this->businessSettingGetOrInsert(type: $type, value: json_encode(['status' => 0, 'content' => '']));
        }
        return $policy;
    }

    public function getInsertDataOfVersion($versionNumber): void
    {
        if ($versionNumber == '13.0') {
            $this->businessSettingGetOrInsert(type: 'product_brand', value: 1);
            $this->businessSettingGetOrInsert(type: 'digital_product', value: 1);
        }

        if ($versionNumber == '13.1') {
            $this->updateOrInsertPolicy(type: 'refund-policy');
            $this->updateOrInsertPolicy(type: 'return-policy');
            $this->updateOrInsertPolicy(type: 'cancellation-policy');
            $this->businessSettingGetOrInsert(type: 'offline_payment', value: json_encode(['status' => 0]));
            $this->businessSettingGetOrInsert(type: 'temporary_close', value: json_encode(['status' => 0]));
            $this->businessSettingGetOrInsert(type: 'vacation_add', value: json_encode([
                'status' => 0,
                'vacation_start_date' => null,
                'vacation_end_date' => null,
                'vacation_note' => null
            ]));
            $this->businessSettingGetOrInsert(type: 'cookie_setting', value: json_encode([
                'status' => 0,
                'cookie_text' => null
            ]));
            DB::table('colors')->whereIn('id', [16, 38, 93])->delete();
        }

        if ($versionNumber == '14.0') {
            $getColors = BusinessSetting::where(['type' => 'colors'])->first();
            if ($getColors) {
                $colors = json_decode($getColors->value, true);
                BusinessSetting::where('type', 'colors')->update([
                    'value' => json_encode([
                        'primary' => $colors['primary'],
                        'secondary' => $colors['secondary'],
                        'primary_light' => $colors['primary_light'] ?? '#CFDFFB',
                    ]),
                ]);
            }

            $this->businessSettingGetOrInsert(type: 'maximum_otp_hit', value: 0);
            $this->businessSettingGetOrInsert(type: 'otp_resend_time', value: 0);
            $this->businessSettingGetOrInsert(type: 'temporary_block_time', value: 0);
            $this->businessSettingGetOrInsert(type: 'maximum_login_hit', value: 0);
            $this->businessSettingGetOrInsert(type: 'temporary_login_block_time', value: 0);

            // Product category id update start
            $products = Product::all();
            foreach ($products as $product) {
                $categories = json_decode($product->category_ids, true);
                $i = 0;
                foreach ($categories as $category) {
                    if ($i == 0) {
                        $product->category_id = $category['id'];
                    } elseif ($i == 1) {
                        $product->sub_category_id = $category['id'];
                    } elseif ($i == 2) {
                        $product->sub_sub_category_id = $category['id'];
                    }

                    $product->save();
                    $i++;
                }
            }
            //product category id update end
        }

        if ($versionNumber == '14.1') {
            // default theme folder delete from resources/views folder start
            $folder = base_path('resources/views');
            $directories = glob($folder . '/*', GLOB_ONLYDIR);
            foreach ($directories as $directory) {
                $array = explode('/', $directory);
                if (File::isDirectory($directory) && in_array(end($array), ['web-views', 'customer-view'])) {
                    File::deleteDirectory($directory);
                }
            }
            $front_end_dir = $folder . "/layouts/front-end";
            if (File::isDirectory($front_end_dir)) {
                File::deleteDirectory($front_end_dir);
            }

            foreach (['home.blade.php', 'welcome.blade.php'] as $file) {
                if (File::exists($folder . '/' . $file)) {
                    unlink($folder . '/' . $file);
                }
            }
            // default theme folder delete from resources/views folder end

            // Apple Login Information Insert
            $this->businessSettingGetOrInsert(type: 'apple_login', value: json_encode([[
                'login_medium' => 'apple',
                'client_id' => '',
                'client_secret' => '',
                'status' => 0,
                'team_id' => '',
                'key_id' => '',
                'service_file' => '',
                'redirect_url' => '',
            ]]));

            //referral code update for existing user
            $customers = User::whereNull('referral_code')->where('id', '!=', 0)->get();
            foreach ($customers as $customer) {
                $customer->referral_code = Helpers::generate_referer_code();
                $customer->save();
            }

            $this->businessSettingGetOrInsert(type: 'ref_earning_status', value: 0);
            $this->businessSettingGetOrInsert(type: 'ref_earning_exchange_rate', value: 0);

            // New payment module necessary table insert
            try {
                if (!Schema::hasTable('addon_settings')) {
                    $sql = File::get(base_path('database/migrations/addon_settings.sql'));
                    DB::unprepared($sql);
                }

                if (!Schema::hasTable('payment_requests')) {
                    $sql = File::get(base_path('database/migrations/payment_requests.sql'));
                    DB::unprepared($sql);
                }
            } catch (\Exception $exception) {
                //
            }

            // Existing payment gateway data import from business setting table
            $this->payment_gateway_data_update();
            $this->sms_gateway_data_update();

            $this->businessSettingGetOrInsert(type: 'guest_checkout', value: 0);
            $this->businessSettingGetOrInsert(type: 'minimum_order_amount', value: 0);
            $this->businessSettingGetOrInsert(type: 'minimum_order_amount_by_seller', value: 0);
            $this->businessSettingGetOrInsert(type: 'minimum_order_amount_status', value: 0);
            $this->businessSettingGetOrInsert(type: 'admin_login_url', value: 'admin');
            $this->businessSettingGetOrInsert(type: 'employee_login_url', value: 'employee');
            $this->businessSettingGetOrInsert(type: 'free_delivery_status', value: 0);
            $this->businessSettingGetOrInsert(type: 'free_delivery_responsibility', value: 'admin');
            $this->businessSettingGetOrInsert(type: 'free_delivery_over_amount', value: 0);
            $this->businessSettingGetOrInsert(type: 'free_delivery_over_amount_seller', value: 0);
            $this->businessSettingGetOrInsert(type: 'add_funds_to_wallet', value: 0);
            $this->businessSettingGetOrInsert(type: 'minimum_add_fund_amount', value: 0);
            $this->businessSettingGetOrInsert(type: 'user_app_version_control', value: json_encode([
                "for_android" => [
                    "status" => 1,
                    "version" => "14.1",
                    "link" => ""
                ],
                "for_ios" => [
                    "status" => 1,
                    "version" => "14.1",
                    "link" => ""
                ]
            ]));
            $this->businessSettingGetOrInsert(type: 'seller_app_version_control', value: json_encode([
                "for_android" => [
                    "status" => 1,
                    "version" => "14.1",
                    "link" => ""
                ],
                "for_ios" => [
                    "status" => 1,
                    "version" => "14.1",
                    "link" => ""
                ]
            ]));
            $this->businessSettingGetOrInsert(type: 'delivery_man_app_version_control', value: json_encode([
                "for_android" => [
                    "status" => 1,
                    "version" => "14.1",
                    "link" => ""
                ],
                "for_ios" => [
                    "status" => 1,
                    "version" => "14.1",
                    "link" => ""
                ]
            ]));

            // Script for theme setup for existing banner
            $themeName = theme_root_path();
            $banners = Banner::get();
            if ($banners) {
                foreach ($banners as $banner) {
                    $banner->theme = $themeName;
                    $banner->save();
                }
            }

            // Current shipping responsibility add to orders table
            Order::query()->update(['shipping_responsibility' => getWebConfig(name: 'shipping_method')]);

            $this->businessSettingGetOrInsert(type: 'whatsapp', value: json_encode(['status' => 1, 'phone' => '00000000000']));
            $this->businessSettingGetOrInsert(type: 'currency_symbol_position', value: 'left');
        }

        if ($versionNumber == '14.2') {
            // Notification message import process
            InstallController::notification_message_import();

            // Business table notification message data import in notification message table
            self::notification_message_processing();

            // Company reliability import process
            InstallController::company_riliability_import();
        }

        if ($versionNumber == '14.3') {
            $this->businessSettingGetOrInsert(type: 'app_activation', value: json_encode(['software_id' => '', 'is_active' => 0]));
        }

        if ($versionNumber == '14.3.1') {
            // Shop slug
            $shops = Shop::where('slug', 'en')->get();
            if ($shops) {
                foreach ($shops as $shop) {
                    $shop->slug = Str::slug($shop->name, '-') . '-' . Str::random(6);
                    $shop->save();
                }
            }

            // Translation table data update
            DB::table('translations')->where('translationable_type', 'LIKE', "%Product%")->update(['translationable_type' => 'App\Models\Product']);
            DB::table('translations')->where('translationable_type', 'LIKE', "%Brand%")->update(['translationable_type' => 'App\Models\Brand']);
            DB::table('translations')->where('translationable_type', 'LIKE', "%Category%")->update(['translationable_type' => 'App\Models\Category']);
            DB::table('translations')->where('translationable_type', 'LIKE', "%NotificationMessage%")->update(['translationable_type' => 'App\Models\NotificationMessage']);
        }

        if ($versionNumber == '14.4') {
            if (!NotificationMessage::where(['key' => 'product_request_approved_message'])->first()) {
                DB::table('notification_messages')->updateOrInsert([
                    'key' => 'product_request_approved_message'
                ],
                    [
                        'user_type' => 'seller',
                        'key' => 'product_request_approved_message',
                        'message' => 'customize your product request approved message message',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }

            if (!NotificationMessage::where(['key' => 'product_request_rejected_message'])->first()) {
                DB::table('notification_messages')->updateOrInsert([
                    'key' => 'product_request_rejected_message'
                ],
                    [
                        'user_type' => 'seller',
                        'key' => 'product_request_rejected_message',
                        'message' => 'customize your product request rejected message message',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }

        if ($versionNumber == '14.5') {
            $this->businessSettingGetOrInsert(type: 'map_api_status', value: 1);
        }

        if ($versionNumber == '14.6') {
            Product::where(['product_type' => 'digital'])->update(['current_stock' => 999999999]);

            //priority setup and vendor registration data process
            InstallController::getPrioritySetupAndVendorRegistrationData();

            if (Admin::count() > 0 && EmailTemplate::count() < 1) {
                $emailTemplateUserData = [
                    'admin',
                    'customer',
                    'vendor',
                    'delivery-man',
                ];
                foreach ($emailTemplateUserData as $key => $value) {
                    $this->getEmailTemplateDataForUpdate($value);
                }
            }
        }

        if ($versionNumber == '14.7') {
            $this->businessSettingGetOrInsert(type: 'storage_connection_type', value: 'public');
            $this->businessSettingGetOrInsert(type: 'google_search_console_code', value: '');
            $this->businessSettingGetOrInsert(type: 'bing_webmaster_code', value: '');
            $this->businessSettingGetOrInsert(type: 'baidu_webmaster_code', value: '');
            $this->businessSettingGetOrInsert(type: 'yandex_webmaster_code', value: '');
            InstallController::updateRobotTexFile();
        }

        if ($versionNumber == '14.8') {
            $this->businessSettingGetOrInsert(type: 'firebase_otp_verification', value: json_encode(['status' => 0, 'web_api_key' => '']));
            if (!LoginSetup::where(['key' => 'login_options'])->first()) {
                DB::table('login_setups')->updateOrInsert(['key' => 'login_options'], ['value' => json_encode([
                    'manual_login' => 1,
                    'otp_login' => 0,
                    'social_login' => 1,
                ])]);
            }
            if (!LoginSetup::where(['key' => 'social_media_for_login'])->first() || true) {
                $socialMediaForLogin = [
                    'google' => 0,
                    'facebook' => 0,
                    'apple' => 0,
                ];
                $socialLogin = BusinessSetting::where(['type' => 'social_login'])->first();
                $appleLogin = BusinessSetting::where(['type' => 'apple_login'])->first();
                if ($socialLogin) {
                    $socialLoginUpdate = [];
                    foreach (json_decode($socialLogin?->value, true) as $socialLoginService) {
                        $socialMediaForLogin[$socialLoginService['login_medium']] = (int)$socialLoginService['status'];
                        $socialLoginService['status'] = 1;
                        $socialLoginUpdate[] = $socialLoginService;
                    }
                    BusinessSetting::where(['type' => 'social_login'])->update(['value' => json_encode($socialLoginUpdate)]);
                }

                if ($appleLogin) {
                    $appleLoginUpdate = [];
                    foreach (json_decode($appleLogin?->value, true) as $appleLoginService) {
                        $socialMediaForLogin[$appleLoginService['login_medium']] = (int)$appleLoginService['status'];
                        $appleLoginService['status'] = 1;
                        $appleLoginUpdate[] = $appleLoginService;
                    }
                    BusinessSetting::where(['type' => 'apple_login'])->update(['value' => json_encode($appleLoginUpdate)]);
                }

                DB::table('login_setups')->updateOrInsert(['key' => 'login_options'], ['value' => json_encode([
                    'manual_login' => 1,
                    'otp_login' => 0,
                    'social_login' => $socialMediaForLogin['google'] || $socialMediaForLogin['facebook'] || $socialMediaForLogin['apple'] ? 1 : 0,
                ])]);
                DB::table('login_setups')->updateOrInsert(['key' => 'social_media_for_login'], ['value' => json_encode($socialMediaForLogin)]);
            }

            if (!LoginSetup::where(['key' => 'email_verification'])->first()) {
                $emailVerification = BusinessSetting::where(['type' => 'email_verification'])->first()?->value ?? 0;
                DB::table('login_setups')->updateOrInsert(['key' => 'email_verification'], [
                    'value' => $emailVerification ? 1 : 0
                ]);
            }
            if (!LoginSetup::where(['key' => 'phone_verification'])->first()) {
                $otpVerification = BusinessSetting::where(['type' => 'otp_verification'])->first()?->value ?? 0;
                DB::table('login_setups')->updateOrInsert(['key' => 'phone_verification'], [
                    'value' => $otpVerification ? 1 : 0
                ]);
            }

            $this->businessSettingGetOrInsert(type: 'maintenance_system_setup', value: json_encode([
                'user_app' => 0,
                'user_website' => 0,
                'vendor_app' => 0,
                'deliveryman_app' => 0,
                'vendor_panel' => 0,
            ]));

            if (!BusinessSetting::where(['type' => 'maintenance_duration_setup'])->first()) {
                DB::table('business_settings')->updateOrInsert(['type' => 'maintenance_duration_setup'], [
                    'value' => json_encode([
                        'maintenance_duration' => "until_change",
                        'start_date' => null,
                        'end_date' => null,
                    ]),
                ]);
            }
            if (!BusinessSetting::where(['type' => 'maintenance_message_setup'])->first()) {
                DB::table('business_settings')->updateOrInsert(['type' => 'maintenance_message_setup'], [
                    'value' => json_encode([
                        'business_number' => 1,
                        'business_email' => 1,
                        'maintenance_message' => "We are Working On Something Special",
                        'message_body' => "We apologize for any inconvenience. For immediate assistance, please contact with our support team",
                    ]),
                ]);
            }
            $this->updateOrInsertPolicy(type: 'shipping-policy');
        }

        Artisan::call('file:permission');
        if (DOMAIN_POINTED_DIRECTORY == 'public' && function_exists('shell_exec')) {
            shell_exec('ln -s ../resources/themes themes');
            Artisan::call('storage:link');
        }
    }

    public static function notification_message_processing(): bool
    {
        $businessNotificationMessage = [
            'order_pending_message',
            'order_confirmation_msg',
            'order_processing_message',
            'out_for_delivery_message',
            'order_delivered_message',
            'order_returned_message',
            'order_failed_message',
            'order_canceled',
            'delivery_boy_assign_message',
            'delivery_boy_expected_delivery_date_message',
        ];

        $messages = BusinessSetting::whereIn('type', $businessNotificationMessage)->get()->toArray();

        $currentNotificationMessage = [
            'order_pending_message',
            'order_confirmation_message',
            'order_processing_message',
            'out_for_delivery_message',
            'order_delivered_message',
            'order_returned_message',
            'order_failed_message',
            'order_canceled',
            'new_order_assigned_message',
            'expected_delivery_date',
        ];

        foreach ($messages as $message) {
            $data = $message['type'];
            if ($data == 'order_confirmation_msg') {
                $data = 'order_confirmation_message';

            } elseif ($data == 'delivery_boy_assign_message') {
                $data = 'new_order_assigned_message';

            } elseif ($data == 'delivery_boy_expected_delivery_date_message') {
                $data = 'expected_delivery_date';
            }

            $isTrue = in_array($data, $currentNotificationMessage);
            $value = json_decode($message['value'], true);

            $notification = NotificationMessage::where('key', $data)->first();
            if ($isTrue && $notification && isset($value['message'])) {
                $notification->message = $value['message'];
                $notification->status = $value['status'];
                $notification->save();
            }
        }

        return true;
    }

    private function sms_gateway_data_update()
    {
        try {
            $gateway = array_merge(Helpers::getDefaultSMSGateways(), [
                'twilio_sms',
                'nexmo_sms',
                '2factor_sms',
                'msg91_sms',
                'releans_sms',
            ]);

            $data = BusinessSetting::whereIn('type', $gateway)->pluck('value', 'type')->toArray();

            if ($data) {
                foreach ($data as $key => $value) {

                    $decoded_value = json_decode($value, true);

                    $gateway = $key;
                    if ($key == 'twilio_sms') {
                        $gateway = 'twilio';
                        $additional_data = [
                            'sid' => $decoded_value['sid'],
                            'messaging_service_sid' => $decoded_value['messaging_service_sid'],
                            'token' => $decoded_value['token'],
                            'from' => $decoded_value['from'],
                            'otp_template' => $decoded_value['otp_template'],
                        ];
                    } elseif ($key == 'nexmo_sms') {
                        $gateway = 'nexmo';
                        $additional_data = [
                            'api_key' => $decoded_value['api_key'],
                            'api_secret' => $decoded_value['api_secret'],
                            'from' => $decoded_value['from'],
                            'otp_template' => $decoded_value['otp_template'],
                        ];
                    } elseif ($key == '2factor_sms') {
                        $gateway = '2factor';
                        $additional_data = [
                            'api_key' => $decoded_value['api_key'],
                        ];
                    } elseif ($key == 'msg91_sms') {
                        $gateway = 'msg91';
                        $additional_data = [
                            'template_id' => $decoded_value['template_id'],
                            'authkey' => $decoded_value['authkey'] ?? '',
                        ];
                    } elseif ($key == 'releans_sms') {
                        $gateway = 'releans';
                        $additional_data = [
                            'api_key' => $decoded_value['api_key'],
                            'from' => $decoded_value['from'],
                            'otp_template' => $decoded_value['otp_template'],
                        ];
                    }

                    $default_data = [
                        'gateway' => $gateway,
                        'mode' => 'live',
                        'status' => $decoded_value['status'] ?? 0
                    ];

                    $credentials = json_encode(array_merge($default_data, $additional_data));

                    $payment_additional_data = [
                        'gateway_title' => ucfirst(str_replace('_', ' ', $gateway)),
                        'gateway_image' => null
                    ];

                    DB::table('addon_settings')->updateOrInsert(['key_name' => $gateway, 'settings_type' => 'sms_config'], [
                        'key_name' => $gateway,
                        'live_values' => $credentials,
                        'test_values' => $credentials,
                        'settings_type' => 'sms_config',
                        'mode' => isset($decoded_value['status']) == 1 ? 'live' : 'test',
                        'is_active' => isset($decoded_value['status']) == 1 ? 1 : 0,
                        'additional_data' => json_encode($payment_additional_data),
                    ]);


                }
                BusinessSetting::whereIn('type', $gateway)->delete();
            }
        } catch (\Exception $exception) {
            dd($exception);
        }
        return true;
    }

    private function payment_gateway_data_update()
    {
        try {
            $gateway[] = ['ssl_commerz_payment'];

            $data = BusinessSetting::whereIn('type', GlobalConstant::DEFAULT_PAYMENT_GATEWAYS)->pluck('value', 'type')->toArray();

            if ($data) {
                foreach ($data as $key => $value) {
                    $gateway = $key;
                    if ($key == 'ssl_commerz_payment') {
                        $gateway = 'ssl_commerz';
                    }

                    $decoded_value = json_decode($value, true);
                    $data = [
                        'gateway' => $gateway,
                        'mode' => isset($decoded_value['status']) == 1 ? 'live' : 'test'
                    ];

                    if ($gateway == 'ssl_commerz') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'store_id' => $decoded_value['store_id'],
                            'store_password' => $decoded_value['store_password'],
                        ];
                    } elseif ($gateway == 'paypal') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'client_id' => $decoded_value['paypal_client_id'],
                            'client_secret' => $decoded_value['paypal_secret'],
                        ];
                    } elseif ($gateway == 'stripe') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'api_key' => $decoded_value['api_key'],
                            'published_key' => $decoded_value['published_key'],
                        ];
                    } elseif ($gateway == 'razor_pay') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'api_key' => $decoded_value['razor_key'],
                            'api_secret' => $decoded_value['razor_secret'],
                        ];
                    } elseif ($gateway == 'senang_pay') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'callback_url' => null,
                            'secret_key' => $decoded_value['secret_key'],
                            'merchant_id' => $decoded_value['merchant_id'],
                        ];
                    } elseif ($gateway == 'paytabs') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'profile_id' => $decoded_value['profile_id'],
                            'server_key' => $decoded_value['server_key'],
                            'base_url' => $decoded_value['base_url'],
                        ];
                    } elseif ($gateway == 'paystack') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'callback_url' => $decoded_value['paymentUrl'],
                            'public_key' => $decoded_value['publicKey'],
                            'secret_key' => $decoded_value['secretKey'],
                            'merchant_email' => $decoded_value['merchantEmail'],
                        ];
                    } elseif ($gateway == 'paymob_accept') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'callback_url' => null,
                            'api_key' => $decoded_value['api_key'],
                            'iframe_id' => $decoded_value['iframe_id'],
                            'integration_id' => $decoded_value['integration_id'],
                            'hmac' => $decoded_value['hmac'],
                        ];
                    } elseif ($gateway == 'mercadopago') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'access_token' => $decoded_value['access_token'],
                            'public_key' => $decoded_value['public_key'],
                        ];
                    } elseif ($gateway == 'liqpay') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'private_key' => $decoded_value['public_key'],
                            'public_key' => $decoded_value['private_key'],
                        ];
                    } elseif ($gateway == 'flutterwave') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'secret_key' => $decoded_value['secret_key'],
                            'public_key' => $decoded_value['public_key'],
                            'hash' => $decoded_value['hash'],
                        ];
                    } elseif ($gateway == 'paytm') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'merchant_key' => $decoded_value['paytm_merchant_key'],
                            'merchant_id' => $decoded_value['paytm_merchant_mid'],
                            'merchant_website_link' => $decoded_value['paytm_merchant_website'],
                        ];
                    } elseif ($gateway == 'bkash') {
                        $additional_data = [
                            'status' => $decoded_value['status'],
                            'app_key' => $decoded_value['api_key'],
                            'app_secret' => $decoded_value['api_secret'],
                            'username' => $decoded_value['username'],
                            'password' => $decoded_value['password'],
                        ];
                    }

                    $credentials = json_encode(array_merge($data, $additional_data));

                    $payment_additional_data = ['gateway_title' => ucfirst(str_replace('_', ' ', $gateway)),
                        'gateway_image' => null];


                    DB::table('addon_settings')->updateOrInsert(['key_name' => $gateway, 'settings_type' => 'payment_config'], [
                        'key_name' => $gateway,
                        'live_values' => $credentials,
                        'test_values' => $credentials,
                        'settings_type' => 'payment_config',
                        'mode' => isset($decoded_value['status']) && $decoded_value['status'] == '1' ? 'live' : 'test',
                        'is_active' => isset($decoded_value['status']) && $decoded_value['status'] == '1' ? 1 : 0,
                        'additional_data' => json_encode($payment_additional_data),
                    ]);
                }
                BusinessSetting::whereIn('type', GlobalConstant::DEFAULT_PAYMENT_GATEWAYS)->delete();
            }
        } catch (\Exception $exception) {

        }
        return true;
    }

}
