<?php

require_once 'StructureDiffInfo.php';

class StructureDiffInfoTest extends \PHPUnit_Framework_TestCase
{
    public function testDiffInfo()
    {
        $message = StructureDiffInfo::KEY;

        $diff = StructureDiffInfo::createDiff($message);

        $this->assertFalse($diff->isEqual());

        $this->assertEquals($diff->getMessage(), $message);

        $this->assertEmpty($diff->getPath());

        foreach (['title', 'link', 'info'] as $key) {
            $diff->addPath($key);
        }

        $path = 'info.link.title';

        $this->assertEquals($diff->getPath(), $path);
        $this->assertEquals((string) $diff, "$message $path");
    }

    public function testEqualInfo()
    {
        $equal = StructureDiffInfo::createEqual();

        $this->assertTrue($equal->isEqual());

        $this->assertEmpty((string) $equal);
    }
}