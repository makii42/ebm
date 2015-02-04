<?php
namespace Ebm;

use Monolog\Logger;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;


/**
 * Super simple appender that takes a bunch of finders and appends
 * all files found by those, split by the passed separator, or
 * three PHP_EOLs by default.
 *
 * @package Ebm
 */
class ScriptAppender
{
    /** @var \Monolog\Logger */
    private $logger;

    /** @var array of Finder */
    private $finders;

    /** @var string */
    private $separator;

    /**
     * @param Logger $logger The logger to log progress to. Good for development to see
     * which files are actually appended.
     * @param array $finders The finders to iterate to identify files to add.
     * @param string|null $separator The separator to add between files.
     */
    function __construct(Logger $logger, array $finders, $separator = null)
    {
        $this->logger = $logger;
        $this->finders = $finders;

        $this->separator = ($separator === null ? PHP_EOL . PHP_EOL . PHP_EOL : $separator);
    }


    /**
     * @return string the appended files as one.
     */
    public function getBlob()
    {
        $this->logger->debug('loading script files...');

        $fileContents = array();

        /** @var Finder $finder */
        foreach ($this->finders as $finder) {
            /** @var SplFileInfo $file */
            foreach ($finder as $file) {
                $this->logger->debug('loading file ' . $file->getRealPath());
                $fileContents[] = $file->getContents();
            }
        }

        return implode($this->separator, $fileContents);
    }
}
