<?php
namespace Ebm;

use Monolog\Logger;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ScriptAppender
{
    /** @var \Monolog\Logger */
    private $logger;

    /** @var array of Finder */
    private $finders;

    /** @var string */
    private $separator;

    function __construct(Logger $logger, array $finders, $separator = null)
    {
        $this->logger  = $logger;
        $this->finders = $finders;

        $this->separator = ($separator === null ? PHP_EOL . PHP_EOL . PHP_EOL : $separator);
    }



    public function getBlob()
    {
        $fileContents = array();

        /** @var Finder $finder */
        foreach ($this->finders as $finder)
        {
            /** @var SplFileInfo $file */
            foreach ($finder as $file)
            {
                $this->logger->debug('loading file ' . $file->getRealPath());
                $fileContents[] = $file->getContents();
            }
        }

        return implode($this->separator, $fileContents);
    }
}
