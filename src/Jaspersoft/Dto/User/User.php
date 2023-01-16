<?php

namespace Jaspersoft\Dto\User;

/**
 * Class User.
 */
class User
{
    /**
     * @var string
     */
    public $username;
    /**
     * @var string
     */
    public $password;
    /**
     * @var string
     */
    public $emailAddress;
    /**
     * @var string
     */
    public $fullName;
    /**
     * @var string
     */
    public $tenantId;
    /**
     * @var array
     */
    public $roles = [];
    /**
     * @var bool
     */
    public $enabled;
    /**
     * @var bool
     */
    public $externallyDefined;
    /**
     * @var string
     */
    public $previousPasswordChangeTime;

    public function __construct($username = null, $password = null, $emailAddress = null, $fullName = null,
                                $tenantId = null, $enabled = null, $externallyDefined = null, $previousPasswordChangeTime = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->emailAddress = $emailAddress;
        $this->fullName = $fullName;
        $this->tenantId = $tenantId;
        $this->enabled = $enabled;
        $this->externallyDefined = $externallyDefined;
        $this->previousPasswordChangeTime = $previousPasswordChangeTime;
        $this->roles = [];
    }

    public function jsonSerialize()
    {
        $data = [];
        foreach (get_object_vars($this) as $k => $v) {
            if (!empty($v) || $v === false) {
                $data[$k] = $v;
            }
        }

        return $data;
    }
}
