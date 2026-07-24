<?php

return [
    'menu' => [
        'label' => 'Profile',
    ],

    'widget' => [
        'profile' => [
            'label' => 'Profile',
        ],
    ],

    'fields' => [
        'current_password' => 'Current password',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
    ],

    'sections' => [
        'profile' => [
            'title' => 'Profile information',
            'description' => "Update your account's profile information and email address.",
            'actions' => [
                'save' => 'Save',
            ],
        ],
        'password' => [
            'title' => 'Update password',
            'description' => 'Ensure your account is using a long, random password to stay secure.',
            'actions' => [
                'save' => 'Save',
            ],
        ],
        'sessions' => [
            'title' => 'Browser sessions',
            'description' => 'Manage and log out your active sessions on other browsers and devices.',
            'intro' => 'If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.',
            'empty' => 'No other browser sessions found. This list is only available when sessions are stored in the database.',
            'unknown' => 'Unknown',
            'this_device' => 'This device',
            'last_active' => 'Last active',
            'confirm_logout_session' => 'Are you sure you want to log out of this browser session?',
            'columns' => [
                'device' => 'Device',
                'ip_address' => 'IP address',
                'last_active' => 'Last active',
                'actions' => 'Actions',
            ],
            'actions' => [
                'logout' => 'Log out',
                'logout_others' => 'Log out',
            ],
            'modal' => [
                'heading' => 'Log out other browser sessions',
                'description' => 'Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.',
            ],
        ],
        'delete' => [
            'title' => 'Delete account',
            'description' => 'Permanently delete your account.',
            'warning' => [
                'heading' => 'This action is permanent',
                'description' => 'Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.',
            ],
            'actions' => [
                'delete' => 'Delete account',
            ],
            'modal' => [
                'heading' => 'Delete account',
                'description' => 'Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted.',
            ],
        ],
    ],

    'notifications' => [
        'profile_updated' => 'Profile updated successfully.',
        'password_updated' => 'Password updated successfully.',
        'other_browser_sessions_logged_out' => 'Other browser sessions have been logged out.',
        'browser_session_logged_out' => 'Browser session logged out.',
    ],
];
