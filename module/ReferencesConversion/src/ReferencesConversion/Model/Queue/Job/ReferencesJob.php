<?php

namespace ReferencesConversion\Model\Queue\Job;

use Manager\Model\Queue\Job\AbstractQueueJob;
use Manager\Entity\Job;

/**
 * Parses References from NLM XML document
 */
class ReferencesJob extends AbstractQueueJob
{
    /**
     * Parse references
     *
     * @param Job $job
     * @return Job $job
     */
    public function process(Job $job)
    {
        $references = $this->sm->get('ReferencesConversion\Model\Converter\References');

        // Fetch the document to convert
        if ($job->inputFileFormat == JOB_INPUT_TYPE_PDF) {
            $xmlDocument =
                $job->getStageDocument(JOB_CONVERSION_STAGE_PDF_EXTRACT);
        } else {
            $xmlDocument =
                $job->getStageDocument(JOB_CONVERSION_STAGE_NLMXML);
        }
        if (!$xmlDocument) {
            throw new \Exception('Couldn\'t find the stage document');
        }

        // Parse the references
        $outputFile = $job->getDocumentPath() . '/document.bib.xml';
        $references->setInputFile($xmlDocument->path);
        $references->setOutputDirectory($job->getDocumentPath());
        $references->setOutputFile($outputFile);
        $references->convert();

        $job->conversionStage = JOB_CONVERSION_STAGE_REFERENCES;

        if (!$references->getStatus()) {
            $job->status = JOB_STATUS_FAILED;
            return $job;
        }

        $documentDAO = $this->sm->get('Manager\Model\DAO\DocumentDAO');
        $referenceXmlDocument = $documentDAO->getInstance();
        $referenceXmlDocument->path = $outputFile;
        $referenceXmlDocument->job = $job;
        $referenceXmlDocument->conversionStage = JOB_CONVERSION_STAGE_REFERENCES;

        $job->documents[] = $referenceXmlDocument;

        // Flag the reference parsing as successful. This will
        // influence which conversion steps will be executed.
        $job->referenceParsingSuccess = true;

        return $job;
    }
}
