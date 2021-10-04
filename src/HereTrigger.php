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

    public function __construct()
    {
        $this->config = config('here-trigger');
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

        $filterInfo = $this->config['filters'][$splits[0]][$splits[1]];

        $filter = $filterInfo['filter'];
        $valueFunction = $filterInfo['value_function'];

        $property = $filter[0];

        if(!isset($this->$property)){
            $this->$property = $this->getValue($valueFunction, $data);
        }

        $operator = $filter[1];
        $comparisonValue = $filter[2];
        
        switch ($operator) {
            case Operators::LESS_THAN:
                return $this->$property < $comparisonValue;
                break;
            case Operators::LESS_THAN_EQUAL_TO:
                return$this->$property <= $comparisonValue;
                break;
            case Operators::GREATER_THAN:
                return $this->$property > $comparisonValue;
                break;
            case Operators::GREATER_THAN_EQUAL_TO:
                return $this->$property >= $comparisonValue;
                break;
            case Operators::EQUAL_TO:
                return $this->$property == $comparisonValue;
                break;
            case Operators::RANGE_INCLUSIVE:
                return $this->$property >= $comparisonValue[0] && $this->$property <= $comparisonValue[1];
                break;
            case Operators::RANGE_EXCLUSIVE:
                return $this->$property > $comparisonValue[0] && $this->$property < $comparisonValue[1];
                break;
            case Operators::TEXT_CONTAINS:
                return strpos($this->$property, $comparisonValue) !== false;
                break;
            case Operators::DATE_GREATER_THAN_OR_EQUAL_TO:
                return Carbon::parse($this->$property)->greaterThanOrEqualTo($comparisonValue);
                break;
            
            default:
                return null;
                break;
        }
    }

    public function getValue($function, $data){
        $helperClass = $this->config['helper_class'];
        return $helperClass::$function($data);
    }

    public function evaluateExpression1($expression, $data)
    {
        // matches everything except space
        preg_match_all('/\S*/', $expression, $matches);

        $operator = null;
        $result = null;
        foreach ($matches as $match) {
            if($this->isArithmeticOperator($match)){
                $operator = $match;
            }else{
                switch ($operator) {
                    case '+':
                        $result = $result ? $result + $data->$match : $data->$match;
                        break;
                    case '-':
                        $result = $result ? $result - $data->$match : $data->$match;
                        break;
                    case '*':
                        $result = $result ? $result * $data->$match : $data->$match;
                        break;
                    case '/':
                        $result = $result ? $result / $data->$match : $data->$match;
                        break;
                    
                    default:
                        
                        break;
                }
            }
        }

        return $result;
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