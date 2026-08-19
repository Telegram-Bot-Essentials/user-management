<?php

return [
    'main' => [
        'text' => [
            'sort_menu' => 'روش مرتب‌سازی را انتخاب کنید:',
            'filter_menu' => 'فیلتر را انتخاب کنید:',
            'index' => 'مدیریت کاربران ربات'
                ."\r\n"
                ."\r\n"
                ."👥 کاربران\r\n"
                ."کل: :total\r\n"
                ."معلق: :suspended\r\n"
                ."ادمین: :admins\r\n"
                ."ناظر: :moderators\r\n"
                ."\r\n"
                ."📈 عضویت\r\n"
                ."24 ساعت گذشته: :joinedDay\r\n"
                ."7 روز گذشته: :joinedWeek\r\n"
                ."30 روز گذشته: :joinedMonth\r\n"
                ."\r\n"
                ."📡 دسترسی‌پذیری\r\n"
                ."در دسترس: :reachable\r\n"
                ."بلاک کرده: :blocked\r\n"
                ."ناموجود: :unreachable\r\n"
                ."حذف‌شده: :deactivated\r\n"
                ."\r\n"
                ."⚡️ فعالیت\r\n"
                ."24 ساعت گذشته: :activeDay\r\n"
                ."7 روز گذشته: :activeWeek\r\n"
                .'30 روز گذشته: :activeMonth',
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
                ."دسترسی‌پذیری: :userReachability\r\n"
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
            'all_actions_history_header' => "📜 تاریخچه اقدامات ربات\r\nصفحه :page/:totalPages",
            'all_actions_history_empty' => "📜 تاریخچه اقدامات ربات\r\n\r\nهنوز هیچ اقدامی ثبت نشده است.",
        ],
        'answers' => [
        ],
        'keys' => [
            'sort' => '🔀 مرتب‌سازی: :sort',
            'filter' => '🔎 فیلتر: :filter',
            'user_column_header' => 'کاربر',
            'user' => ':fullName :suspendStatus - :credit',
            'userIsActive' => '✅ کاربر فعال است',
            'userIsSuspended' => '⛔️ کاربر تعلیق شده است',
            'userRole' => '💪 نقش: :role',
            'userUpdateData' => '♻️ بروزرسانی',
            'setUserBalance' => '💵 تنظیم اعتبار',
            'addUserBalance' => '💸 افزایش اعتبار',
            'userActionsHistory' => '📜 تاریخچه اقدامات',
            'allActionsHistory' => '📜 تاریخچه همه اقدامات',
        ],
    ],
    'reply_key' => 'کاربران ربات 👥',
    'sorts' => [
        'last_interaction' => 'آخرین تعامل',
        'created_at' => 'تاریخ عضویت',
        'username' => 'نام کاربری',
    ],
    'filters' => [
        'all' => 'همه کاربران',
        'reachable' => 'در دسترس',
        'blocked' => 'ربات را بلاک کرده',
        'unreachable' => 'در دسترس نیست',
        'deactivated' => 'اکانت حذف‌شده',
    ],
    'reachability' => [
        'active' => 'در دسترس',
        'blocked' => 'ربات را بلاک کرده',
        'unreachable' => 'در دسترس نیست',
        'deactivated' => 'اکانت حذف‌شده',
    ],
];
