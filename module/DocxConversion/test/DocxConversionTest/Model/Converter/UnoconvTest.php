<?php

namespace DocxConversionTest\Model\Converter;

use PHPUnit_Framework_TestCase;
use Xmlps\UnitTest\ModelTest;
use DocxConversion\Model\Converter\Unoconv; // just for documentation

class UnoconvTest extends ModelTest
{
    /**
     * Instance of the class to test.
     * @var Unoconv $unoconv
     */
    protected $unoconv;

    /**
     * Location of the input file to copy for testing.
     * @var string $testInputFile
     */
    protected $testInputFile = 'module/DocxConversion/test/assets/document.odt';
    /**
     * Desired location of the target output file.
     * @var string $testOutputFile
     */
    protected $testOutputFile = '/tmp/UNITTEST_unoconv_docxfile.docx';

    /**
     * Initialize the test
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();

        $this->unoconv = $this->sm->get('DocxConversion\Model\Converter\Unoconv');

        $this->resetTestData();
    }

    /**
     * Test if the input file validation works properly
     *
     * @return void
     */
    public function testInputFileDoesntExist()
    {
        $this->setExpectedException('Exception');
        $this->unoconv->setInputFile($this->testInputFile . rand());
    }

    /**
     * Test if the conversion works properly
     *
     * @return void
     */
    public function testDocxConversion()
    {
        // Output file shouldn’t exist yet.
        $this->assertFalse(file_exists($this->testOutputFile));

        // Set the parameters for conversion.
        $this->unoconv->setInputFile($this->testInputFile);
        $this->unoconv->setOutputFile($this->testOutputFile);
        $this->unoconv->setFilter('docx7');
        $this->unoconv->setVerbose(true);
        // Do it!
        $this->unoconv->convert();

        // Status is true (success).
        $this->assertTrue($this->unoconv->getStatus());
        // Output location is set.
        $this->assertNotNull($this->unoconv->getOutput());
        // Output file actually exists.
        $this->assertTrue(file_exists($this->testOutputFile));
        // Input and output files differ.
        $this->assertNotSame(
            file_get_contents($this->testInputFile),
            file_get_contents($this->testOutputFile)
        );

        // Output file is a zip file (of which docx7 is a subtype).
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->testOutputFile);
        $this->assertSame($mimeType, 'application/zip');

        // Reset for the next test.
        $this->resetTestData();
    }

    /**
     * Remove test data
     *
     * @return void
     */
    protected function cleanTestData()
    {
        @unlink($this->testOutputFile);
    }
}
