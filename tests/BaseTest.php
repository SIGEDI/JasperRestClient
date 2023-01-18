<?php

use Jaspersoft\Client\Client as c;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    public $bootstrap;
    private $jcSuper;

    public function setUp(): void
    {
        $this->bootstrap = parse_ini_file(dirname(__FILE__).'/test.properties');
        $this->jc = new c(
            $this->bootstrap['hostname'],
            $this->bootstrap['admin_username'],
            $this->bootstrap['admin_password'],
            $this->bootstrap['admin_org']
        );
    }

    public function tearDown(): void
    {
    }

    public function createSuperClient(): void
    {
        $this->jcSuper = new c(
            $this->bootstrap['hostname'],
            $this->bootstrap['super_username'],
            $this->bootstrap['super_password']
        );
    }
}
