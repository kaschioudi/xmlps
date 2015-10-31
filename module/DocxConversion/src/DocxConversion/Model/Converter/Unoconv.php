<?php

namespace DocxConversion\Model\Converter;

use Xmlps\Logger\Logger;
use Xmlps\Command\Command;
use Manager\Model\Converter\AbstractConverter;

/**
 * Converts documents using Open/LibreOffice and unoconv
 */
class Unoconv extends AbstractConverter
{
    /**
     * @var mixed $config
     */
    protected $config;
    /**
     * @var Logger $logger
     */
    protected $logger;

    /**
     * Keyword for the conversion target; will be passed to unoconv
     * with the -f switch if set.  Should be a string, or null.  The
     * default value for the unoconv command is 'pdf'.  'unoconv
     * --show' will list valid format codes.  Commonly-used values are
     * 'docx', 'docx7', 'pdf'.
     * @var mixed $filter
     */
    protected $filter;
    /**
     * @var mixed $inputFile
     */
    protected $inputFile;
    /**
     * @var mixed $outputFile
     */
    protected $outputFile;
    /**
     * @var boolean $verbose
     */
    protected $verbose = false;

    /**
     * Constructor.
     *
     * @param mixed $config unoconv config
     * @param Logger $logger Logger
     *
     * @return void
     *
     * @throws Exception if unoconv command is not configured
     */
    public function __construct($config, Logger $logger)
    {
        if (!isset($config['command'])) {
            throw new \Exception('Unoconv command is not configured');
        }

        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Set the filter to use for the conversion
     *
     * @param mixed $filter Conversion filter to use; see member
     * documentation
     *
     * @return void
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Set the file to convert
     *
     * @param mixed $inputFile
     *
     * @return void
     *
     * @throws Exception if input file is not found
     */
    public function setInputFile($inputFile)
    {
        if (!file_exists($inputFile)) {
            throw new \Exception('Input file doesn\'t exist');
        }

        $this->inputFile = $inputFile;
    }

    /**
     * Set the output file
     *
     * @param mixed $outputFile
     *
     * @return void
     */
    public function setOutputFile($outputFile)
    {
        $this->outputFile = $outputFile;
    }

    /**
     * Set whether unoconv should be verbose or not
     *
     * @param boolean $verbose
     *
     * @return void
     */
    public function setVerbose($verbose)
    {
        $this->verbose = (true === $verbose);
    }

    /**
     * Convert the document
     *
     * @return void
     *
     * @throws Exception if inputFile or outputFile are not set
     */
    public function convert()
    {
        $command = new Command;

        // Set the base command.  If HOME is not set to a writeable
        // directory, unoconv won’t work.
        $command->setCommand($this->config['command']);

        // Add verbosity switch
        if ($this->verbose) $command->addSwitch('-vvv');

        // Add the filter
        if ($this->filter) $command->addSwitch('-f', $this->filter);

        // Add the output file
        if (!$this->outputFile) {
            throw new \Exception('No output file given');
        }

        $command->addSwitch('-o', $this->outputFile);

        // Add the input file
        if (!$this->inputFile) {
            throw new \Exception('No input file given');
        }

        $command->addArgument($this->inputFile);

        // Redirect STDERR to STDOUT to captue it in $this->output
        $command->addRedirect('2>&1');

        $this->logger->debugTranslate(
            'docxconversion.unoconv.executeCommandLog',
            $command->getCommand()
        );

        // Execute the conversion
        $command->execute();
        $this->status = $command->isSuccess();
        $this->output = $command->getOutputString();

        // Report success or failure.
        if ($this->status) {
            $this->logger->debugTranslate(
                'docxconversion.unoconv.executeCommandOutputLog',
                $this->getOutput()
                );
        } else {
            $this->logger->errTranslate(
                'docxconversion.unoconv.executeFailure',
                $this->getOutput()
            );
        }
    }
}
