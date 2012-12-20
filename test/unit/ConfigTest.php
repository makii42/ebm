<?php
/**
 * @todo Switch to autoloading later
 */

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $fixtureFile;

    /** @var Config */
    private $config;


    public function setUp()
    {
        $this->fixtureFile = __DIR__ . '/fixtures/config.json';
        $this->config      = new Config($this->fixtureFile);
    }


    /**
     * @covers Config::__construct
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Config', $this->config);
    }


    /**
     * @covers Config::get
     */
    public function testGetReturnsValueForExistingKeys()
    {
        $this->assertSame('value', $this->config->get('variable'));
        $this->assertFalse($this->config->get('enabled'));

        $expectedListItem1        = new stdClass();
        $expectedListItem1->label = 'Yeah';
        $expectedListItem2        = new stdClass();
        $expectedListItem2->label = 'Weird';
        $this->assertEquals(array($expectedListItem1, $expectedListItem2), $this->config->get('list'));
    }


    /**
     * @covers Config::get
     */
    public function testGetReturnsValueForNestedKeys()
    {
        $this->assertSame('Yeah', $this->config->get('list.0.label'));
    }


    /**
     * @covers Config::get
     */
    public function testGetReturnsDefaultValueForNonExistingKeys()
    {
        $this->assertNull($this->config->get('iDoNotExist'));
        $this->assertSame('myDefaultValue', $this->config->get('IDoNotExistToo', 'myDefaultValue'));
    }


//    public function testGet


    /**
     * @covers Config::toJSON
     */
    public function testToJsonReturnsJsonEncodedData()
    {
        $json = preg_replace('/ {1,}/', '', str_replace("\n", '', file_get_contents($this->fixtureFile)));
        $this->assertSame($json, $this->config->toJSON());
    }
}
