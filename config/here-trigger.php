<?php

use Karamvirs\HereTrigger\Constants\Operators;
use Illuminate\Support\Carbon;


return [
    'filters' => [
        'helper_class' => 'App\Helpers\HereTriggerHelper',
        'user' => [
            'age_bet_30_and_40' => ['filter' => ['age', Operators::RANGE_INCLUSIVE, [30, 40]], 'value_function' => 'userAge'],
            'next_birthday_in_2_months' => ['filter' => ['next_birthday', Operators::LESS_THAN_EQUAL_TO, Carbon::now()->addMonths(2)], 'value_function' => 'userNextBirthday'],
            'prev_order_within_a_month' => ['filter' => ['prev_order_date', operators::DATE_GREATER_THAN_OR_EQUAL_TO, Carbon::now()->subMonth()], 'value_function' => 'userPrevOrderDate'],
            'prev_order_within_10_days' => ['filter' => ['prev_order_date', operators::DATE_GREATER_THAN_OR_EQUAL_TO, Carbon::now()->subDays(10)], 'value_function' => 'userPrevOrderDate'],
            'total_spent_till_date_more_than_1500' => ['filter' => ['total_spent_till_date', operators::GREATER_THAN, 1500], 'value_function' => 'userTotalSpendTillDate'],
            'more_than_5_orders' => ['filter' => ['total_orders', operators::GREATER_THAN, 5], 'value_function' => 'userTotalOrderCount'],
            'at_least_2_orders' => ['filter' => ['total_orders', operators::GREATER_THAN_EQUAL_TO, 2], 'value_function' => 'userTotalOrderCount'],
        ],
        'order' => [
            'order_total_creater_than_200' => ['filter' => ['order_total', operators::GREATER_THAN_EQUAL_TO, 200], 'value_function' => 'orderTotal'],
            'order_total_bet_80_and_100' => ['filter' => ['order_total', operators::RANGE_INCLUSIVE, [80, 100]], 'value_function' => 'orderTotal'],
        ],
        'wishlist' => [
            'items_total_greater_than_3' => ['filter' => ['created_at', operators::GREATER_THAN, 3], 'value_function' => 'wishlistItemsCount'],
            'items_total_1' => ['filter' => ['created_at', operators::NUMBER_EQUAL_TO, 1], 'value_function' => 'wishlistItemsCount'],
            
        ]
    ],
    'actions' => [
        'high_value_customer' => [
            'rule' => '[ user.age_bet_30_and_40 && { user.total_spent_till_date_more_than_1500 OR (user.prev_order_within_10_days && user.at_least_2_orders) } ] OR user.more_than_5_orders',
            'processors' => ['App\Jobs\HighValueNotification',  'App\Jobs\Send15PercentDiscount']
        ],
        'young_international_customer' => [
            'rule' => '[ user.age_bet_30_and_40 && { user.total_spent_till_date_more_than_1500 OR (user.prev_order_within_10_days && user.at_least_2_orders) } ] OR user.more_than_5_orders',
            'processors' => ['App\Jobs\5PCDiscountToInternational']
        ],

    ],
    'triggers' => [
        'new_order' =>['high_value_customer', 'young_international_customer'],
    ]
];