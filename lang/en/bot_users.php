<?php

return [
    'main' => [
        'text' => [
            'sort_menu' => 'Choose a sort:',
            'index' => 'Manage bot users'
            ."\r\n"
            ."\r\n"
            ."User count: :userCount\r\n"
            ."Users joined in the last 24 hours: :usersJoinedLastDay\r\n",
            'show_user' => ':userFullName'
                ."\n\n"
                ."Telegram User ID: :userPeerId\r\n"
                ."Telegram User Name: :userUsername\r\n"
                ."Telegram First Name: :userFirstName\r\n"
                ."Telegram Last Name: :userLastName\r\n"
                ."Telegram phone number: :userTel\r\n"
                ."\r\n"
                ."Role: :userRole\r\n"
                ."Status: :userSuspendStatus\r\n"
                ."Joined At: :userCreatedAt\r\n"
                ."Last Interaction: :userUpdatedAt\r\n"
                ."\r\n"
                .'⚠️ This data received at: :dataReceiveTime',
            'user_actions_history_header' => "📜 Action history\r\n:userName\r\nPage :page/:totalPages",
            'user_actions_history_empty' => "📜 Action history\r\n:userName\r\n\r\nNo actions recorded for this user yet.",
            'user_actions_history_date' => '───── :date ─────',
            'user_actions_history_enter_page' => 'Enter the page number:',
            'user_actions_history_page_loaded' => 'Page :page loaded.',
            'user_actions_history_waiting_page' => 'Waiting for page number...',
        ],
        'answers' => [
        ],
        'keys' => [
            'sort' => '🔀 Sort: :sort',
            'user_column_header' => 'User',
            'user' => ':fullName :suspendStatus - :credit',
            'userIsActive' => '✅ User is active',
            'userIsSuspended' => '⛔️ User is suspended',
            'userRole' => '💪 Role: :role',
            'userUpdateData' => '♻️ Update',
            'setUserBalance' => '💵 Set Balance',
            'addUserBalance' => '💸 Add Balance',
            'userActionsHistory' => '📜 Action history',
        ],
    ],
    'reply_key' => 'Bot Users 👥',
    'sorts' => [
        'last_interaction' => 'Last interaction',
        'created_at' => 'Join date',
        'username' => 'Username',
    ],
];
