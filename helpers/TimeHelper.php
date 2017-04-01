<?php
namespace spiritdead\yii2resque\helpers;

/**
 * Class TimeHelper
 * @package spiritdead\yii2resque\helpers
 */
class TimeHelper
{
    public static function getStandardOffsetUTC($timezone)
    {
        $summer = date('I');
        if($timezone == 'UTC') {
            return '';
        } else {
            $timezone = new \DateTimeZone($timezone);
            $transitions = array_slice($timezone->getTransitions(), -3, null, true);

            foreach (array_reverse($transitions, true) as $transition)
            {
                if ($transition['isdst'] == 1)
                {
                    continue;
                }
                $offset = $transition['offset'] + ($summer * 3600);
                return sprintf('%+03d:%02u', $offset / 3600, abs($offset) % 3600 / 60);
            }

            return false;
        }
    }
}