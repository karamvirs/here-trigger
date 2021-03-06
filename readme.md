# HereTrigger

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

The package provides a convenient way to define complex rules to conditionally process certain actions triggered from any part of your Laravel application.

Examples: 
* evaluate certain rules everytime an order is processed on an ecommerce site and if true, send promotion emails, discount coupons etc.
* Whenever a new credit report is available on a credit management service, evaluate if customer should be moved to a different group.

## Installation

Via Composer

``` bash
$ composer require karamvirs/here-trigger

$ php artisan vendor:publish --provider="Karamvirs\HereTrigger\HereTriggerServiceProvider"
```


## Overview
Anywhere in your application, you disptach the HereTriggerProcessor job with a trigger name and a data array.
```
...
...
use App\Jobs\HereTriggerProcessor;
...
...
HereTriggerProcessor::disptach('new_order', ['user' => <User Object>, 'order' => <Order Object>])
...
...
```
NOTE: data array must contain all data objects that will be used in the filters defined in the config. The key names must match the key names defined under the filters section in the here-trigger config 

When the job is processed, the code looks at the config file (here-trigger.php) and retrieves the actions for this trigger. In our example config they are:
    ['high_value_customer', 'young_international_customer']

The rule expression for each action (under the 'actions' key in the config) is evaluated, and if true, jobs specified under processors are dispatched.

## How it works
When you run the vendor:publish command, following 3 files are published:

* config/here-trigger.php
* app/Helpers/HereTriggerHelper.php
* app/Jobs/HereTriggerProcessor.php

Let us discuss them one by one.

### config/here-trigger.php

_**helper_class**_

sets the path to the helper class that defines all filter value functions. Feel free to change it in case you want to move the HereTriggerHelper file to elsewhere.

_**filters**_

contain the entities or data objects whose properties will be evaluated for conditions defined under the action rules.
In the default Example, you will see there are 3 entries under filters: user, order, wishlist

Each entity in under the filters has a filtername, the actual filter and the function name that evaluates the value for that filter.

Ex: 
```
'total_spent_till_date_more_than_1500' => ['filter' => ['total_spent_till_date', operators::GREATER_THAN, 1500], 'value_function' => 'userTotalSpendTillDate'],
```
*total_spent_till_date_more_than_1500* - is the filter name which is referred to in the rule expression under the actions.

*'filter' => ['total_spent_till_date', operators::GREATER_THAN, 1500]* - defines the actual filter. Here it says, the total amount spent by the user should be greater than 1500.

*'value_function' => 'userTotalSpendTillDate'* - tells the function name which you will define in the helper file (HereTriggerHelper.php) and will be used to calculate the value of this property (total_spent_till_date, in this case.) If a value function is not specified, then it is assumed that the property is directly accessible on the passed object.

_**actions**_

Actions contain a rule and an array of processors

rule is an expression that must evaluate to true for the processors to run. Expression can have brackets but same types of brackets cannot be nested. That means you can use :
    [ xyz && { abc OR (mno && pqr)}]
but you cannot use:
    {xyz {abc }} 

The terms in the expression are actual filter names defined in the filters section.

'processors' is an array of Jobs you will define, which will contain the code to be executed when the rule evaluates to true.

Example config mentions 2 such jobs, but doesnt impplement them:
App\Jobs\Send15PercentDiscount
App\Jobs\HighValueNotification

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author@email.com instead of using the issue tracker.


## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/karamvirs/here-trigger.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/karamvirs/here-trigger.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/karamvirs/here-trigger/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/karamvirs/here-trigger
[link-downloads]: https://packagist.org/packages/karamvirs/here-trigger
[link-travis]: https://travis-ci.org/karamvirs/here-trigger
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/karamvirs
[link-contributors]: ../../contributors
