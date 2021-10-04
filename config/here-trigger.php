<?php

use Karamvirs\HereTrigger\Constants\Operators;
use Illuminate\Support\Carbon;


return [
    'helper_class' => 'App\Helpers\HereTriggerHelper',
    'filters' => [
        'report' => [
            'score_less_than_800' => ['filter' => ['score', Operators::LESS_THAN, 800], 'value_function' => 'reportScore'],
            'total_balances_greater_than_1000' => ['filter' => ['total_balances', Operators::GREATER_THAN, 1000], 'value_function' => 'reportTotalBalances'],
            'total_balances_less_than_9000' => ['filter' => ['total_balances', Operators::LESS_THAN, 9000], 'value_function' => 'reportTotalBalances'],
            'no_negative_items' => ['filter' => ['new_negative_items', Operators::EQUAL_TO, 0], 'value_function' => 'reportNewNegativeItems'],
            'average_score_less_than_560' => ['filter' => ['average_score', Operators::LESS_THAN, 560], 'value_function' => 'reportAverageScore'],
            '5_or_more_new_negative_items' => ['filter' => ['new_negative_items', Operators::GREATER_THAN, 5], 'value_function' => 'reportNewNegativeItems'],
        ],
        'user' => [
            'signup_in_last_30_days' => ['filter' => ['created_at', Operators::DATE_GREATER_THAN_OR_EQUAL_TO, Carbon::now()->subDays(30)], 'value_function' => 'userCreatedAt'],
            'signup_in_last_60_days' => ['filter' => ['created_at', Operators::DATE_GREATER_THAN_OR_EQUAL_TO, Carbon::now()->subDays(60)], 'value_function' => 'userCreatedAt'],
            'signup_in_last_90_days' => ['filter' => ['created_at', Operators::DATE_GREATER_THAN_OR_EQUAL_TO, Carbon::now()->subDays(90)], 'value_function' => 'userCreatedAt'],
            'has_paid' => ['filter' => ['overridden_decline', Operators::EQUAL_TO, 0], 'value_function' => 'userOverriddenDecline'],
            
        ]
    ],
    'actions' => [
        'group_change' => [
            'rule'=> '{ report.score_less_than_800 && (report.total_balances_greater_than_1000 OR report.no_negative_items) } OR user.signup_in_last_30_days', 
            'processors' =>['App\Jobs\ProcessUserGroupChange'] 
        ],
        'high_value' => [
            'rule' => 'report.5_or_more_new_negative_items',
            'processors' => ['App\Jobs\HighValueNotification']
        ]
    ],
    'triggers' => [
        'new_report_pull' =>['group_change', 'high_value'],
    ]
];