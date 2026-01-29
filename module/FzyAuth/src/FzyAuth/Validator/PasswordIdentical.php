<?php
/**
 * Created by PhpStorm.
 * User: brian
 * Date: 8/18/14
 * Time: 11:19 AM
 */
namespace FzyAuth\Validator;

use Laminas\Validator\Identical;

class  PasswordIdentical extends Identical
{
    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_SAME      => "The two passwords do not match",
        self::MISSING_TOKEN => 'No password was provided to match against',
    );
}
