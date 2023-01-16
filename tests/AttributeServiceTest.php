<?php

require_once 'BaseTest.php';
use Jaspersoft\Dto\Attribute\Attribute;
use Jaspersoft\Tool\TestUtils as u;

class JasperAttributeServiceTest extends BaseTest
{
    protected $jc;
    protected $newUser;
    protected $as;
    protected $us;

    public function setUp()
    {
        parent::setUp();
        $this->newUser = u::createUser();
        $this->attr = new Attribute('Gender', 'Robot');
        $this->attr2 = new Attribute('Favorite Beer', 'Anchor Steam');

        $this->us = $this->jc->userService();
        $this->us->addOrUpdateUser($this->newUser);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->us->deleteUser($this->newUser);
    }

    /* Tests below */

    /**
     * Checks if user's attribute is saved correctly when addOrUpdateAttribute() is called with Attribute parameter, that is
     * single Attribute.
     */
    public function testPostAttributesAddsOneAttributeData()
    {
        $this->us->addOrUpdateAttribute($this->newUser, $this->attr);
        $tempAttr = $this->us->getAttributes($this->newUser);
        $tempValue = $tempAttr[0]->value;
        $tempName = $tempAttr[0]->name;

        $this->assertSame('Robot', $tempValue);
        $this->assertSame('Gender', $tempName);
    }

    public function testreplaceAttributes()
    {
        $this->us->replaceAttributes($this->newUser, [$this->attr, $this->attr2]);
        $attrs = $this->us->getAttributes($this->newUser);

        $this->assertSame(count($attrs), 2);
    }

     /**
      * Deleting attributes.
      */
     public function testDeleteAttribute()
     {
         $this->us->addOrUpdateAttribute($this->newUser, $this->attr);
         $count = count($this->us->getAttributes($this->newUser));
         $this->us->deleteAttributes($this->newUser);
         $newcount = count($this->us->getAttributes($this->newUser));
         $this->assertSame(1, $count);
         $this->assertSame($newcount, 0);
     }
}
