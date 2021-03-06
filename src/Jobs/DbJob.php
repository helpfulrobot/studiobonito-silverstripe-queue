<?php namespace StudioBonito\SilverStripe\Queue\Jobs;

use Convert;
use Injector;
use StudioBonito\SilverStripe\Queue\DbQueue;
use StudioBonito\SilverStripe\Queue\Models\JobQueue;

class DbJob extends AbstractJob implements JobInterface
{
    /**
     * The SSDatabase queue instance.
     *
     * @var \StudioBonito\SilverStripe\Queue\DbQueue
     */
    protected $db;

    /**
     * @var JobQueue
     */
    protected $job;

    /**
     * Create a new job instance.
     *
     * @param  \Injector $injector
     * @param  DbQueue   $db
     * @param  JobQueue  $job
     * @param  string    $queue
     *
     * @return void
     */
    function __construct(Injector $injector, DbQueue $db, JobQueue $job, $queue)
    {
        $this->db = $db;
        $this->job = $job;
        $this->queue = $queue;
        $this->injector = $injector;
    }

    /**
     * Run the job.
     *
     * @return void
     */
    public function run()
    {
        $this->resolveAndRun(Convert::json2array($this->getRawPayload()));
    }

    /**
     * Get the raw payload string for the job.
     *
     * @return string
     */
    public function getRawPayload()
    {
        return $this->job->Payload;
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();

        $this->job->delete();
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int $delay
     *
     * @return void
     */
    public function release($delay = 0)
    {
        $this->delete();

        $this->db->release($this->queue, $this->getRawPayload(), $delay, $this->attempts() + 1);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        $payload = Convert::json2array($this->getRawPayload());

        return isset($payload['attempts']) ? $payload['attempts'] : null;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->job->ID;
    }

    /**
     * Get the injector instance.
     *
     * @return \Injector
     */
    public function getInjector()
    {
        return $this->injector;
    }
}
 