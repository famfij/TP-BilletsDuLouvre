<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 14/12/2015
 * Time: 13:30
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;

/**
 * Class Payment
 * @ORM\Table()
 * @ORM\Entity()
 */
class Payment extends BasePayment
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer $id
     */
    protected $id;
}