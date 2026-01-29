<?php
namespace FzyAuth\Service;

use FzyAuth\Entity\Base\UserInterface;
use FzyCommon\Util\Params;

/**
 * Class Password
 * @package FzyAuth\Service
 */
abstract class Password extends Base
{
    const OPTIONS = 'password';

    /**
     * @var Params
     */
    protected $options;

    /**
     * Handle
     * @param  UserInterface $user
     * @return mixed
     */
    abstract protected function process(UserInterface $user);

    /**
     * Ensures options get merged and set up. Then runs 'process' on the user.
     * @param  UserInterface $user
     * @param  Params        $options
     * @return mixed
     */
    public function handle(UserInterface $user, Params $options = null)
    {
        // set up options
        $this->setOptions($this->getMergedOptions($this->getDefaultOptions(), $options));

        return $this->process($user);
    }

    /**
     * @param  Params $defaultOptions
     * @param  Params $extraOptions
     * @return Params
     */
    protected function getMergedOptions(Params $defaultOptions, Params $extraOptions = null)
    {
        if ($extraOptions !== null) {
            $defaultOptions->merge($extraOptions);
        }

        return $defaultOptions;
    }

    /**
     * @return Params
     */
    protected function getDefaultOptions()
    {
        return $this->getModuleConfig()->getWrapped(static::OPTIONS);
    }

    /**
     * @return Params
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param  Params $options
     * @return $this
     */
    public function setOptions(Params $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Looks up user with email address
     * @param $email
     * @return UserInterface
     */
    public function getUserByEmail($email)
    {
        return $this->nullObject($this->getUserRepository()->findOneBy(array($this->getModuleConfig()->get('user_email_property', 'email') => $email)));
    }

    /**
     * @param $token
     * @return UserInterface
     */
    public function getUserByToken($token)
    {
        return $this->nullObject($this->getUserRepository()->findOneBy(array($this->getModuleConfig()->get('user_token_property', 'passwordToken') => $token)));
    }

    /**
     * Ensures the return value implements UserInterface
     * @param  UserInterface $user
     * @return UserInterface
     */
    protected function nullObject(UserInterface $user = null)
    {
        if ($user === null) {
            return $this->getServiceLocator()->get('FzyAuth\NullUser');
        }

        return $user;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getUserRepository()
    {
        return $this->em()->getRepository($this->getConfig()->getWrapped('lmcuser')->get('user_entity_class'));
    }
}
