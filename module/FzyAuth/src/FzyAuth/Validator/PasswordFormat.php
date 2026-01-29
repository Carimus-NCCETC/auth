<?php
/**
 * Created by PhpStorm.
 * User: brian
 * Date: 8/18/14
 * Time: 11:19 AM
 */
namespace FzyAuth\Validator;

use Laminas\Validator\AbstractValidator;

class PasswordFormat extends AbstractValidator
{
    const GENERAL = 'general';
    protected $messageTemplates = array(
    self::GENERAL => "Your password must be at least 8 characters in length",

);

    public function isValid($value)
    {
        $this->setValue($value);

        $isValid = true;

        if (strlen($value) < 8) {
            $this->error(self::GENERAL);
            $isValid = false;
        }

        return $isValid;
    }
}
