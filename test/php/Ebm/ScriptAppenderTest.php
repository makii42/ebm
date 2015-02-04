<?php

namespace Ebm;

use Monolog\Logger;
use Symfony\Component\Finder\Finder;
use VirtualFileSystem\FileSystem;


/**
 * Yeah, I know, this test is testing the symfony finder as well. Send me a PR
 * if you care to change this.
 *
 * @package Ebm
 */
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



    public function testAppenderWorksWithOneFinderAndDefaultSeparator()
    {
        $fs = $this->prepareHelloWorldVfs();

        $separator = PHP_EOL . PHP_EOL . PHP_EOL;

        $finder = new Finder();
        $finder->files()->in($fs->path('/dirOne/'))->name('*txt');

        $testSubject = new ScriptAppender($this->loggerMock, array($finder));

        $this->assertNotEmpty($testSubject->getBlob());
        $this->assertSame('hello,' . $separator . ' world!', $testSubject->getBlob());
    }



    public function testAppenderWorksWithOneFinderAndCustomSeparator()
    {
        $fs = $this->prepareHelloWorldVfs();
        $separator = ' ### ';

        $finder = new Finder();
        $finder->files()->in($fs->path('/dirOne/'))->name('*txt');

        $testSubject = new ScriptAppender($this->loggerMock, array($finder), $separator);

        $this->assertNotEmpty($testSubject->getBlob());
        $this->assertSame('hello,' . $separator . ' world!', $testSubject->getBlob());
    }



    public function testAppenderWorksWithMultipleFinders()
    {
        $fs = $this->prepareHelloWorldVfs();
        $fs->createDirectory('/another');
        file_put_contents($fs->path('/another/file.txt'), 'beware of unicorns!');

        $separator = PHP_EOL . PHP_EOL . PHP_EOL;

        $finderFoo = new Finder();
        $finderFoo->files()->in($fs->path('/another'))->name('*txt');
        $finderBar = new Finder();
        $finderBar->files()->in($fs->path('/dirOne'))->name('*txt');

        $testSubject = new ScriptAppender($this->loggerMock, array($finderFoo, $finderBar));

        $this->assertNotEmpty($testSubject->getBlob());
        $this->assertSame('beware of unicorns!' . $separator . 'hello,' . $separator . ' world!', $testSubject->getBlob());
    }


    /**
     * @return FileSystem
     */
    public function prepareHelloWorldVfs()
    {
        $fs = new FileSystem();
        $fs->createDirectory('/dirOne');
        file_put_contents($fs->path('/dirOne/hello.txt'), 'hello,');
        file_put_contents($fs->path('/dirOne/world.txt'), ' world!');
        return $fs;
    }
}
