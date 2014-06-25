<?php

namespace Ebm;

use Monolog\Logger;
use Symfony\Component\Finder\Finder;

class ScriptAppenderTest extends \PHPUnit_Framework_TestCase
{

    /** @var Logger */
    private $loggerMock;



    protected function setUp()
    {
        parent::setUp();
        $this->loggerMock = $this->getMockBuilder('Monolog\Logger')->disableOriginalConstructor()->getMock();
    }



    public function testAppenderWorksWithoutFinder()
    {
        $testSubject = new ScriptAppender($this->loggerMock, array());

        $this->assertEmpty($testSubject->getBlob());
    }



    public function testAppenderWorksWithOneFinder()
    {
        $separator = PHP_EOL . PHP_EOL . PHP_EOL;

        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../fixtures/appender/oneDir')->name('*txt');

        $testSubject = new ScriptAppender($this->loggerMock, array($finder));

        $this->assertNotEmpty($testSubject->getBlob());
        $this->assertSame('hello,' . $separator . ' world!', $testSubject->getBlob());
    }



    public function testAppenderWorksWithOneFinderAndCustomSeparator()
    {
        $separator = ' ### ';

        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../fixtures/appender/oneDir')->name('*txt');

        $testSubject = new ScriptAppender($this->loggerMock, array($finder), $separator);

        $this->assertNotEmpty($testSubject->getBlob());
        $this->assertSame('hello,' . $separator . ' world!', $testSubject->getBlob());
    }



    public function testAppenderWorksWithMultipleFinders()
    {
        $separator = PHP_EOL . PHP_EOL . PHP_EOL;

        $finderFoo = new Finder();
        $finderFoo->files()->in(__DIR__ . '/../fixtures/appender/moreDir/foo')->name('*txt');
        $finderBar = new Finder();
        $finderBar->files()->in(__DIR__ . '/../fixtures/appender/moreDir/bar')->name('*txt');

        $testSubject = new ScriptAppender($this->loggerMock, array($finderFoo, $finderBar));

        $this->assertNotEmpty($testSubject->getBlob());
        $this->assertSame('beware of unicorns!' . $separator . 'hello,' . $separator . ' world!', $testSubject->getBlob());
    }
}
