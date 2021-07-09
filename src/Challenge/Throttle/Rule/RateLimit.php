<?php
namespace Challenge\Throttle\Rule;

/**
 * Allow one request every x seconds.
 * @author Tim Williams <tim@wordery.com>
 */
class RateLimit implements RuleInterface
{
    protected $_timespan;

    protected $_lastRequest = 0;

    public function __construct($timeBetweenRequests = 2)
    {
        $this->_timespan = $timeBetweenRequests;
    }

    public function getTtl() : int
    {
        return $this->_timespan;
    }

    /**
     * Check time of last request aginst now
     * @param array $requestTimestamps
     * @return boolean
     */
    public function throttled() : Bool
    {
        if (!$this->_lastRequest) {
            return false;
        }
        // var_dump($this->_lastRequest);
        // exit;
        return ($this->_lastRequest + $this->_timespan) >= microtime(1);
    }

    public function log($timestamp) : self
    {
        $this->_lastRequest = $timestamp;
        return $this;
    }


    public function getKey() : string
    {
        return 'rateLimit';
    }

    public function getState() : array
    {
        return [$this->_lastRequest];
    }

    public function setState(array $state) : void
    {
        if (!empty($state) && isset($state[0])) {
            $this->_lastRequest = $state[0];
        }
    }
}