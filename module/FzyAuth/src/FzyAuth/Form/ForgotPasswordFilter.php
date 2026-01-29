<?php

namespace FzyAuth\Form;

use Laminas\InputFilter\InputFilter;
use LmcUser\Options\AuthenticationOptionsInterface;

class ForgotPasswordFilter extends InputFilter
{
    public function __construct(AuthenticationOptionsInterface $options)
    {
        $this->add(array(
            'name'       => 'email',
            'required'   => true,
            'validators' => array(
                array(
                    'name'    => 'EmailAddress',
                ),
            ),
            'filters'   => array(
                array('name' => 'StringTrim'),
            ),
        ));
    }
}
