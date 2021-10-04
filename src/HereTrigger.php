<?php

namespace Karamvirs\HereTrigger;

use Illuminate\Support\Carbon;
use Karamvirs\HereTrigger\Constants\Operators;

class HereTrigger
{

    const BRACES_AND_WORDS = "/\[.*?\]|\{.*\}|\(.*?\)|\S+/";

    const LOGICAL_OPERATORS = ['&&', 'AND', 'OR', '||'];
    
    const ARITHMATIC_OPERATORS = ['+', '-', '/', '*'];

    private $config;

    private $helper;

    public function __construct()
    {
        $this->config = config('here-trigger');

        $this->helper = $this->config['helper_class'];
    }

    public function process($triggerName, $payload)
    {
        $actions = $this->getTriggerActions($triggerName);

        foreach ($actions as $action) {
            $actionDetails = $this->getAction($action);
            if($this->evaluateRule($actionDetails['rule'], $payload)){
                $processors = $actionDetails['processors'];
                foreach ($processors as $jobClass) {
                    $jobClass::dispatch($payload);    
                }
            }
        }
    }

    public function getTriggerActions($trigger)
    {
        return $this->config['triggers'][$trigger];
    }

    public function getAction($action)
    {
        return $this->config['actions'][$action];
    }

    public function evaluateRule($rule, $payload)
    {
        
        $result = null;
        $result = $this->evaluateExpression($rule, $payload);

        return $result;
        
    }

    public function evaluateExpression($expression, $payload)
    {
        $expression = trim($expression);
        preg_match_all(self::BRACES_AND_WORDS, $expression, $terms); // \[.*?\]|\(.*\)|\S+

        $finalResult = null;
        $result = null;
        $operator = null;

        foreach ($terms[0] as $term) {

            if($this->isLogicalOperator($term)){
                $operator = $term;
            }else{
                if(preg_match_all('/[\[\{\(]/', $term, $matches)){
                    $term = trim($term, "[{()}]");                    
                }

                if($this->hasMoreThanOneTerms($term)){
                    $result = $this->evaluateExpression($term, $payload);
                }else{
                    $result = $this->evaluateFilter($term, $payload);                
                }
                
                if($operator !== null){
                    switch ($operator) {
                        case '&&':
                        case 'AND':
                            $finalResult = $finalResult && $result;
                            break;
                        
                        case '||':
                        case 'OR':
                            $finalResult = $finalResult || $result;
                            break;
                    }
                }else{
                    $finalResult = $result;
                }
            }
                
        }

        return $finalResult;

    }

    public function hasMoreThanOneTerms($expression)
    {
        return preg_match('/&&|\|\||AND|OR/', $expression);
    }

    public function evaluateFilter($term, $data)
    {
        // $itemType = explode('.',$term)[0];
        $splits = explode('.',$term);

        $itemType = $splits[0];

        $filterInfo = $this->config['filters'][$splits[0]][$splits[1]];

        $filter = $filterInfo['filter'];
        $valueFunction = isset($filterInfo['value_function']) && !empty($filterInfo['value_function']) ? $filterInfo['value_function'] : null;

        $property = $filter[0];

        // If this property is already set, dont re-evaluate
        $uniquePropertyName = 'prop_' . $itemType[0] . '_' . $property;

        if(!isset($this->$uniquePropertyName)){
            $this->$uniquePropertyName = $valueFunction ? $this->getValue($valueFunction, $data) : $data[$itemType]->$property;
        }

        $operator = $filter[1];
        $comparisonValue = $filter[2];
        
        switch ($operator) {
            case Operators::LESS_THAN:
                return $this->$uniquePropertyName < $comparisonValue;
                break;
            case Operators::LESS_THAN_EQUAL_TO:
                return$this->$uniquePropertyName <= $comparisonValue;
                break;
            case Operators::GREATER_THAN:
                return $this->$uniquePropertyName > $comparisonValue;
                break;
            case Operators::GREATER_THAN_EQUAL_TO:
                return $this->$uniquePropertyName >= $comparisonValue;
                break;
            case Operators::NUMBER_EQUAL_TO:
                return $this->$uniquePropertyName == $comparisonValue;
                break;
            case Operators::NUMBER_NOT_EQUAL_TO:
                return $this->$uniquePropertyName != $comparisonValue;
                break;
            case Operators::RANGE_INCLUSIVE:
                return $this->$uniquePropertyName >= $comparisonValue[0] && $this->$uniquePropertyName <= $comparisonValue[1];
                break;
            case Operators::RANGE_EXCLUSIVE:
                return $this->$uniquePropertyName > $comparisonValue[0] && $this->$uniquePropertyName < $comparisonValue[1];
                break;
            case Operators::TEXT_CONTAINS:
                return strpos($this->$uniquePropertyName, $comparisonValue) !== false;
                break;
            case Operators::DATE_GREATER_THAN_OR_EQUAL_TO:
                return Carbon::parse($this->$uniquePropertyName)->greaterThanOrEqualTo($comparisonValue);
                break;
            case Operators::DATE_LESS_THAN_OR_EQUAL_TO:
                return Carbon::parse($this->$uniquePropertyName)->lessThanOrEqualTo($comparisonValue);
                break;
            case Operators::DATE_EQUAL_TO:
                return Carbon::parse($this->$uniquePropertyName)->equalTo($comparisonValue);
                break;
            case Operators::DATE_NOT_EQUAL_TO:
                return Carbon::parse($this->$uniquePropertyName)->notEqualTo($comparisonValue);
                break;
            case Operators::DATE_BETWEEN_INCLUSIVE:
                return Carbon::parse($this->$uniquePropertyName)->betweenIncluded($comparisonValue[0], $comparisonValue[1]);
                break;
            case Operators::DATE_BETWEEN_EXCLUSIVE:
                return Carbon::parse($this->$uniquePropertyName)->betweenExcluded($comparisonValue[0], $comparisonValue[1]);
                break;
            
            default:
                return null;
                break;
        }
    }

    public function getValue($function, $data){
        return $this->helper::$function($data);
    }

    public function isLogicalOperator($match)
    {
        return in_array(trim($match), self::LOGICAL_OPERATORS);
    }

    public function isArithmeticOperator($match)
    {
        return in_array(trim($match), self::ARITHMATIC_OPERATORS);
    }
    
}