<?php

namespace FzyAuth\Form;

use Laminas\InputFilter\InputFilter;
use LmcUser\Options\AuthenticationOptionsInterface;

class ChangePasswordFilter extends InputFilter
{
    public function __construct(AuthenticationOptionsInterface $options)
    {
        /*
        $identityParams = array(
            'name'       => 'token',
            'required'   => true,
            'validators' => array()
        );

        $identityFields = $options->getAuthIdentityFields();

        if ($identityFields == array('email')) {
            $validators = array('name' => 'EmailAddress');
            array_push($identityParams['validators'], $validators);
        }

        $this->add($identityParams);
        */

        $this->add(array(
            'name'       => 'token',
            'required'   => true,
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'min' => 1,
                    ),
                ),
            ),
            'filters'   => array(
                array('name' => 'StringTrim'),
            ),
        ));

        $this->add(array(
            'name'       => 'newCredential',
            'required'   => true,
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                ),
                array(
                    'name'    => '\FzyAuth\Validator\PasswordFormat',
                ),
            ),
            'filters'   => array(
                array('name' => 'StringTrim'),
            ),
        ));

        $this->add(array(
            'name'       => 'newCredentialVerify',
            'required'   => true,
            'validators' => array(
                array(
                    'name' => '\FzyAuth\Validator\PasswordIdentical',
                    'options' => array(
                        'token' => 'newCredential',
                    ),
                ),
            ),
            'filters'   => array(
                array('name' => 'StringTrim'),
            ),
        ));
    }
}
