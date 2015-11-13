<?php

namespace ReferencesConversionTest\Model\Queue;

use Xmlps\UnitTest\ModelTest;
use Manager\Entity\Job;
use ReferencesConversion\Model\Queue\Job\ReferencesJob;

class ReferencesJobTest extends ModelTest
{
    protected $document;
    protected $job;
    protected $user;

    protected $referencesJob;

    protected $testAsset = 'module/ReferencesConversion/test/assets/document.xml';
    protected $testFile = '/tmp/UNITTEST_document.xml';

    /**
     * Initialize the test
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();

        $this->referencesJob = new ReferencesJob;
        $this->referencesJob->setServiceLocator($this->sm);

        $this->resetTestData();
    }

    /**
     * Test if the conversion works properly
     *
     * @return void
     */
    public function testConversion()
    {
        $this->assertSame(
            $this->job->conversionStage,
            JOB_CONVERSION_STAGE_PDF_EXTRACT
        );
        $this->assertSame(
            $this->document->conversionStage,
            JOB_CONVERSION_STAGE_NLMXML
        );
        $documentCount = count($this->job->documents);
        $this->referencesJob->process($this->job);
        $this->assertNotSame($this->job->status, JOB_STATUS_FAILED);
        $this->assertSame($documentCount + 1, count($this->job->documents));
        $this->assertSame(
            $this->job->conversionStage,
            JOB_CONVERSION_STAGE_REFERENCES
        );
    }

    /**
     * Create test data
     *
     * @return void
     */
    protected function createTestData() {
        @copy($this->testAsset, $this->testFile);

        // Create test user
        $this->user = $this->createTestUser();

        // Create test job
        $this->job = $this->createTestJob(
            array(
                'user' => $this->user,
                'conversionStage' => JOB_CONVERSION_STAGE_PDF_EXTRACT
            )
        );

        // Create test document
        $this->document = $this->createTestDocument(
            array(
                'job' => $this->job,
                'path' => $this->testFile,
                'conversionStage' => JOB_CONVERSION_STAGE_NLMXML
            )
        );
        $this->job->documents[] = $this->document;

        $this->getJobDAO()->save($this->job);
    }

    /**
     * Remove test data
     *
     * @return void
     */
    protected function cleanTestData()
    {
        $this->deleteTestUser();

        @unlink($this->testFile);
    }
}
