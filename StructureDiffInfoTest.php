<?php

require_once 'StructureDiffInfo.php';

class StructureDiffInfoTest extends \PHPUnit_Framework_TestCase
{
    public function testDiffInfo()
    {
        $diff = new StructureDiffInfo(StructureDiffInfo::KEY);

        $this->assertEquals($diff->getMessage(), StructureDiffInfo::KEY);

        $this->assertEmpty($diff->getPath());

        foreach (['title', 'link', 'info'] as $key) {
            $diff->addPath($key);
        }

        $this->assertEquals($diff->getPath(), 'info.link.title');
    }
}