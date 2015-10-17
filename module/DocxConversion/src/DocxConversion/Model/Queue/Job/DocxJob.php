<?php

namespace DocxConversion\Model\Queue\Job;

use Manager\Model\Queue\Job\AbstractQueueJob;
use Manager\Entity\Job as MgrJob;

/**
 * Generates DocX documents
 */
class DocxJob extends AbstractQueueJob
{
    /**
     * Generate document
     *
     * @param MgrJob $job
     * @return MgrJob $job
     */
    public function process(MgrJob $job)
    {
        $unoconv = $this->sm->get('DocxConversion\Model\Converter\Unoconv');

        // Fetch the document to convert
        $unconvertedDocument = $job->getStageDocument(JOB_CONVERSION_STAGE_UNCONVERTED);
        if (!$unconvertedDocument) {
            throw new \Exception('Couldn\'t find the stage document');
        }

        // Convert the document
        $unoconv->setFilter('docx7');
        $unoconv->setInputFile($unconvertedDocument->path);
        $outputPath = $job->getDocumentPath() . '/document.docx';
        $unoconv->setOutputFile($outputPath);
        $unoconv->convert();

        if (!$unoconv->getStatus()) {
            $job->status = JOB_STATUS_FAILED;
            return $job;
        }

        $documentDAO = $this->sm->get('Manager\Model\DAO\DocumentDAO');
        $docxDocument = $documentDAO->getInstance();
        $docxDocument->path = $outputPath;
        $docxDocument->mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        $docxDocument->job = $job;
        $docxDocument->conversionStage = JOB_CONVERSION_STAGE_DOCX;

        $job->documents[] = $docxDocument;
        $job->conversionStage = JOB_CONVERSION_STAGE_DOCX;

        return $job;
    }
}
