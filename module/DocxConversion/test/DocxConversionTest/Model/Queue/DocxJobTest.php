<?php

namespace DocxConversionTest\Model\Queue;

use Xmlps\UnitTest\ModelTest;
use Manager\Entity\Job;
use DocxConversion\Model\Queue\Job\DocxJob;
use Manager\Model\DAO\DocumentDAO; // for documentation
use Manager\Model\DAO\JobDAO; // for documentation
use User\Model\DAO\UserDAO; // for documentation

class DocxJobTest extends ModelTest
{
    /**
     * @var DocumentDAO $document
     */
    protected $document;
    /**
     * @var JobDAO $job
     */
    protected $job;
    /**
     * @var UserDAO $user
     */
    protected $user;

    /**
     * Instance of the class to test.
     * @var DocxJob $docxJob
     */
    protected $docxJob;

    /**
     * Location of the input file to copy for testing.
     * @var string $testAsset
     */
    protected $testAsset = 'module/DocxConversion/test/assets/document.odt';
    /**
     * Destination of the input asset, to be copied before conversion.
     * @var string $testFile
     */
    protected $testFile = '/tmp/UNITTEST_document.odt';

    /**
     * Initialize the test
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();

        $this->docxJob = new DocxJob;
        $this->docxJob->setServiceLocator($this->sm);

        $this->resetTestData();
    }

    /**
     * Test if the conversion works properly
     *
     * @return void
     */
    public function testConversion()
    {
        // We ought to be starting in unconverted stage, as this is
        // our first pipeline stage.  This is more a test of the test
        // scaffold than of the conversion itself.
        $this->assertSame(
            $this->job->conversionStage,
            JOB_CONVERSION_STAGE_UNCONVERTED
            );
        $this->assertSame(
            $this->document->conversionStage,
            JOB_CONVERSION_STAGE_UNCONVERTED
            );

        // Note how many documents the job has before conversion...
        $documentCount = count($this->job->documents);

        // Do the conversion.
        $this->docxJob->process($this->job);

        // We now ought to have one more document than previously.
        $this->assertSame(
            $documentCount + 1,
            count($this->job->documents)
            );

        // The stage ought to have changed to indicate that we’re
        // done.
        $this->assertSame(
            $this->job->conversionStage,
            JOB_CONVERSION_STAGE_DOCX
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
                'conversionStage' => JOB_CONVERSION_STAGE_UNCONVERTED
            )
        );

        // Create test document
        $this->document = $this->createTestDocument(
            array(
                'job' => $this->job,
                'path' => $this->testFile,
                'conversionStage' => $this->job->conversionStage,
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
