<?php

return [
    'main' => [
        'text' => [
            'sort_menu' => 'روش مرتب‌سازی را انتخاب کنید:',
            'index' => 'مدیریت کاربران ربات'
                ."\r\n"
                ."\r\n"
                ."تعداد کاربران: :userCount\r\n"
                ."کاربران اضافه‌شده در ۲۴ ساعت گذشته: :usersJoinedLastDay\r\n",
            'show_user' => ':userFullName'
                ."\n\n"
                ."شناسه کاربر در تلگرام: :userPeerId\r\n"
                ."نام کاربری تلگرام: :userUsername\r\n"
                ."نام کوچک تلگرام: :userFirstName\r\n"
                ."نام خانوادگی تلگرام: :userLastName\r\n"
                ."شماره تلفن تلگرام: :userTel\r\n"
                ."\r\n"
                ."نقش: :userRole\r\n"
                ."وضعیت: :userSuspendStatus\r\n"
                ."تاریخ عضویت: :userCreatedAt\r\n"
                ."آخرین تعامل: :userUpdatedAt\r\n"
                ."\r\n"
                .'⚠️ این اطلاعات در تاریخ :dataReceiveTime دریافت شده است',
            'user_actions_history_header' => "📜 تاریخچه اقدامات\r\n:userName\r\nصفحه :page/:totalPages",
            'user_actions_history_empty' => "📜 تاریخچه اقدامات\r\n:userName\r\n\r\nهنوز هیچ اقدامی برای این کاربر ثبت نشده است.",
            'user_actions_history_date' => '───── :date ─────',
            'user_actions_history_enter_page' => 'شماره صفحه را وارد کنید:',
            'user_actions_history_page_loaded' => 'صفحه :page بارگذاری شد.',
            'user_actions_history_waiting_page' => 'در انتظار شماره صفحه...',
        ],
        'answers' => [
        ],
        'keys' => [
            'sort' => '🔀 مرتب‌سازی: :sort',
            'user_column_header' => 'کاربر',
            'user' => ':fullName :suspendStatus - :credit',
            'userIsActive' => '✅ کاربر فعال است',
            'userIsSuspended' => '⛔️ کاربر تعلیق شده است',
            'userRole' => '💪 نقش: :role',
            'userUpdateData' => '♻️ بروزرسانی',
            'setUserBalance' => '💵 تنظیم اعتبار',
            'addUserBalance' => '💸 افزایش اعتبار',
            'userActionsHistory' => '📜 تاریخچه اقدامات',
        ],
    ],
    'reply_key' => 'کاربران ربات 👥',
    'sorts' => [
        'last_interaction' => 'آخرین تعامل',
        'created_at' => 'تاریخ عضویت',
        'username' => 'نام کاربری',
    ],
];
