<?php

namespace FzyAuth\Entity\Base;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Form\Annotation;

/**
 *
 * @ORM\MappedSuperclass
 *
 * @Annotation\Options({
 *      "autorender": {
 *          "ngModel": "user",
 *          "fieldsets": {
 *              {
 *                  "name": \FzyForm\Annotation\FieldSet::DEFAULT_NAME
 *              }
 *          }
 *      }
 * })
 */
class User extends AbstractUser
{
}
