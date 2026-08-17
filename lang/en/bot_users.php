<?php

return [
    'main' => [
        'text' => [
            'sort_menu' => 'Choose a sort:',
            'filter_menu' => 'Choose a filter:',
            'index' => 'Manage bot users'
            ."\r\n"
            ."\r\n"
            ."👥 Total: :total | Suspended: :suspended | Admins: :admins | Moderators: :moderators\r\n"
            ."📈 Joined: 24h :joinedDay · 7d :joinedWeek · 30d :joinedMonth\r\n"
            ."📡 Reachable: :reachable | Blocked: :blocked | Unreachable: :unreachable | Deleted: :deactivated\r\n"
            .'⚡️ Active: 24h :activeDay · 7d :activeWeek · 30d :activeMonth',
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
                ."Reachability: :userReachability\r\n"
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
            'all_actions_history_header' => "📜 Bot action history\r\nPage :page/:totalPages",
            'all_actions_history_empty' => "📜 Bot action history\r\n\r\nNo actions recorded yet.",
        ],
        'answers' => [
        ],
        'keys' => [
            'sort' => '🔀 Sort: :sort',
            'filter' => '🔎 Filter: :filter',
            'user_column_header' => 'User',
            'user' => ':fullName :suspendStatus - :credit',
            'userIsActive' => '✅ User is active',
            'userIsSuspended' => '⛔️ User is suspended',
            'userRole' => '💪 Role: :role',
            'userUpdateData' => '♻️ Update',
            'setUserBalance' => '💵 Set Balance',
            'addUserBalance' => '💸 Add Balance',
            'userActionsHistory' => '📜 Action history',
            'allActionsHistory' => '📜 All actions history',
        ],
    ],
    'reply_key' => 'Bot Users 👥',
    'sorts' => [
        'last_interaction' => 'Last interaction',
        'created_at' => 'Join date',
        'username' => 'Username',
    ],
    'filters' => [
        'all' => 'All users',
        'reachable' => 'Reachable',
        'blocked' => 'Blocked the bot',
        'unreachable' => 'Unreachable',
        'deactivated' => 'Deleted account',
    ],
    'reachability' => [
        'active' => 'Reachable',
        'blocked' => 'Blocked the bot',
        'unreachable' => 'Unreachable',
        'deactivated' => 'Deleted account',
    ],
];
