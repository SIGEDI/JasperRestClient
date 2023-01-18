<?php

require_once 'BaseTest.php';

class ServerInfoTest extends BaseTest
{
    protected $jc;
    protected $newUser;

    public function testServerInfo(): void
    {
        $info = $this->jc->serverInfo();
        $this->assertTrue(isset($info['version']));
    }
}
