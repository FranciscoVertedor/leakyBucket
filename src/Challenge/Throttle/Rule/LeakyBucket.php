<?php
namespace Challenge\Throttle\Rule;

//Hmm, there should be some code here.
class LeakyBucket implements RuleInterface
{
    /** @var int */
    protected $bucketSize;

    /** @var float */
    protected $drainRate = 0.0;

    /** @var int */
    public $drips = 0;

    /** @var float */
    protected $lastRequest = 0.0;

    /** @var float */
    protected $lastLeak = 0.0;

    public function __construct(int $bucketSize, float $drainRate)
    {
        $this->bucketSize   = $bucketSize;
        $this->drainRate    = $drainRate;
    } 

    public function getTtl() : int
    {
        return $this->drainRate;
    }

    /**
     * Check that we have space in the bucket
     * @param array $requestTimestamps
     * @return boolean
     */
    public function throttled() : Bool
    {
        // We use the leak method to get the capacity we have left in the bucket.
        // This is done everytime the user makes a request so we can ensure that 
        // once the capacity of the bucket has been reached, we don't allow them
        // to do more requests
        $this->leak();
        // Is the amount of drips greater than or equal than the bucket size? 
        // yeah, we are not going to allow the user do more requests until there
        // are some space left in the bucket.
        return $this->drips() >= $this->bucketSize();

    }

    public function log(float $timestamp) : self
    {
        // Update the last request with the current time passed in the arguments.
        $this->lastRequest = $timestamp;
        // Insert a new request in the bucket as we have not reached the limit 
        // yet
        if($this->drips <= $this->bucketSize) {
            $this->drips++;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getKey() : string
    {
        return 'leakyBucket';
    }

    /**
     * @return array
     */
    public function getState() : array
    {
        return [
            'lastRequest'   => $this->lastRequest(),
            'lastLeak'      => $this->lastLeak(),
            'bucketSize'    => $this->bucketSize(),
            'drainRate'     => $this->drainRate(),
            'drips'         => $this->drips(),
        ];
    }

    public function setState(array $state) : void
    {
        if(!empty($state) &&
            (
                isset($state['lastRequest']) &&
                isset($state['lastLeak']) &&
                isset($state['drips']) &&
                isset($state['drainRate']) &&
                isset($state['bucketSize'])
            )
        ) {
            $this->bucketSize   = $state['bucketSize'];
            $this->drainRate    = $state['drainRate'];
            $this->drips        = $state['drips'];
            $this->lastRequest  = $state['lastRequest'];
            $this->lastLeak     = $state['lastLeak'];
        }
    }

    private function lastRequest() : float
    {
        return $this->lastRequest;
    }

    private function lastLeak() : float
    {
        return $this->lastLeak;
    }

    private function bucketSize() : int
    {
        return $this->bucketSize;
    }

    private function drainRate() : float
    {
        return $this->drainRate;
    }

    private function drips() : int
    {
        return max(0, ceil($this->drips));
    }

    /**
     * This method is used to empty the bucket
     */
    private function leak() : void
    {
        // Calculate the time since the last leak happened.
        $elapsedTime = microtime(true) - $this->lastLeak;
        // Multiplying the lapsed time by the number of request per seconds,
        // We know how many requests have been removed from the bucket.
        $dripsToRemove = $elapsedTime * $this->drainRate;
        // We remove the amount of drips from the bucket and we 
        // ensure that the minimum number of requests is always positive.
        $this->drips = max((int) ($this->drips - $dripsToRemove) + 1, 0);
        // Update the last leak with the current time.
        $this->lastLeak = microtime(true);
    }
}