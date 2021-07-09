<?php
namespace Challenge\Throttle\Rule;

/**
 * @author Tim Williams <tim@wordery.com>
 */
interface RuleInterface
{
    /**
     * Check to see if we are under a trottled condition.
     * @param array of int $requestTimestamps array of recent requests for a single identifier.
     * @return boolean true if request throttled, false otherwise.
     */
    public function throttled() : Bool;

    /**
     * @return int seconds to set ttl of data
     */
    public function getTtl() : int;

    /**
     * Get a name to key on when storing this state info
     * @return string
     */
    public function getKey() : string;

    /**
     * Update state to log event happening
     * @param  float $timestamp
     * @return void
     */
    public function log(float $timestamp) : self;

    /**
     * Get the current state
     * @return array
     */
    public function getState() : array;


    /**
     * Set the current state from storage
     * @param array $state
     */
    public function setState(array $state) : void;
}