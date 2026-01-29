<?php

namespace FzyAuth\Form;

use Laminas\Form\Element;
use LmcUser\Form\ProvidesEventsForm;
use LmcUser\Options\AuthenticationOptionsInterface;

class ForgotPassword extends ProvidesEventsForm
{
    /**
     * @var AuthenticationOptionsInterface
     */
    protected $authOptions;

    public function __construct($name, AuthenticationOptionsInterface $options)
    {
        $this->setAuthenticationOptions($options);
        parent::__construct($name);

        $this->add(array(
            'name' => 'email',
            'options' => array(
                'label' => 'Email Address',
                'label_attributes' => array(
                    'class'  => 'visuallyhidden',
                ),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => 'Email Address',
            ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement
            ->setLabel('Submit')
            ->setAttributes(array(
                'type'  => 'submit',
            ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));

        $this->getEventManager()->trigger('init', $this);
    }

    /**
     * Set Authentication-related Options
     *
     * @param  AuthenticationOptionsInterface $authOptions
     * @return Login
     */
    public function setAuthenticationOptions(AuthenticationOptionsInterface $authOptions)
    {
        $this->authOptions = $authOptions;

        return $this;
    }

    /**
     * Get Authentication-related Options
     *
     * @return AuthenticationOptionsInterface
     */
    public function getAuthenticationOptions()
    {
        return $this->authOptions;
    }
}
