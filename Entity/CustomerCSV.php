<?php

namespace Plugin\DatabackupPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Common\Constant;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Customer
 *
 */
class CustomerCSV extends \Eccube\Entity\Customer
{

    public function setCustomerId($customer_id)
    {
        $this->id = $customer_id;
        return $this;
    }

    public function setName01($name01)
    {
        $this->name01 = is_null($name01) ? ' ' : $name01;

        return $this;
    }

    public function setName02($name02)
    {
        $this->name02 = is_null($name02) ? ' ' : $name02;

        return $this;
    }

}
