<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 14/12/2015
 * Time: 13:26
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token;

/**
 * Class PaymentToken
 * @ORM\Table()
 * @ORM\Entity()
 */
class PaymentToken extends Token
{

}