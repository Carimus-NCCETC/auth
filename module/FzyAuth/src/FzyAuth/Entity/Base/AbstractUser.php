<?php
namespace FzyAuth\Entity\Base;

use FzyCommon\Entity\Base as Entity;
use Doctrine\ORM\Mapping as ORM;
use Laminas\Form\Annotation;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 *
 *
 * Class User
 * @package FzyAuth\Entity\Base
 */
abstract class AbstractUser extends Entity implements UserInterface
{
    /**
     * @ORM\Column(type="string", length=128, nullable=true, name="first_name")
     * @Annotation\ErrorMessage("Please provide a first name")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Attributes({"data-ng-model":"user.firstName",  "ng-required" : "true"})
     * @Annotation\Options({"label":"First Name",
     * "autorender": {
     *          "ngModel": "firstName"
     *      }})
     * @Annotation\Required(true)
     *
     * @var string
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=128, nullable=true, name="last_name")
     * @Annotation\ErrorMessage("Please provide a last name")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Attributes({"data-ng-model":"user.lastName",  "ng-required" : "true"})
     * @Annotation\Options({"label":"Last Name",
     * "autorender": {
     *          "ngModel": "lastName"
     *      }})
     * @Annotation\Required(true)
     *
     * @var string
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string", length=50, nullable=true, name="username")
     *
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Attributes({"data-ng-model":"user.username",  "ng-required" : "true"})
     * @Annotation\Options({"label":"Username",
     * "autorender": {
     *          "ngModel": "username"
     *      }})
     * @Annotation\Required(true)
     *
     * @Annotation\Validator(name="NotEmpty", options={"messages": {"isEmpty": "Please provide a username"}})
     *
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=128)
     *
     * @Annotation\Exclude()
     *
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=128, nullable=true, name="password_token")
     *
     * @Annotation\Exclude()
     *
     * @var string
     */
    protected $passwordToken;

    /**
     * @ORM\Column(type="string", length=8, nullable=false, name="role")
     *
     * @Annotation\Type("Laminas\Form\Element\Hidden")
     * @Annotation\Attributes({"required": true})
     * @Annotation\Options({
     *      "label" : "Role",
     *      "value_options": {"": "Choose a role"},
     *      "autorender": {
     *          "ngModel": "role",
     *          "selectOptions": "roleOptions",
     *          "type": "select"
     *      }
     * })
     *
     * @var string
     */
    protected $role;

    /**
     * @ORM\Column(type="datetime", name="created_ts")
     *
     * @Annotation\Exclude()
     *
     * @var \DateTime
     */
    protected $createdTs;

    /**
     * @ORM\Column(type="datetime", name="updated_ts")
     *
     * @Annotation\Exclude()
     *
     * @var \DateTime
     */
    protected $updatedTs;

    /**
     * @ORM\Column(type="string", length=128);
     * @Annotation\Type("Laminas\Form\Element\Email")
     *
     * @Annotation\Attributes({"data-ng-model":"user.email", "ng-required" : "true"})
     * @Annotation\Options({"label":"Email",
     * "autorender": {
     *          "ngModel": "email"
     *      }})
     * @Annotation\Validator(name="EmailAddress", options={"messages": {Laminas\Validator\EmailAddress::INVALID_FORMAT: "Please provide a valid email address."}})
     * @Annotation\Required(true)
     * @Annotation\Validator(name="NotEmpty", options={"messages": {"isEmpty": "Please provide an email address"}})
     * @Annotation\Validator(name="EmailAddress")
     * @var string
     */
    protected $email;

    /**
     *
     * @ORM\Column(type="string", length=8);
     * @Annotation\ErrorMessage("Invalid or unknown status")
     * @Annotation\Type("Laminas\Form\Element\Radio")
     * @Annotation\Attributes({"required": true, "data-ng-disabled": "saving"})
     * @Annotation\Options({
     *      "label":"Status",
     *      "value_options": {
     *          \FzyAuth\Entity\Base\UserInterface::STATE_ACTIVE: "Active",
     *          \FzyAuth\Entity\Base\UserInterface::STATE_INACTIVE: "Inactive"
     *      },
     *      "autorender": {
     *          "ngModel": "state"
     *      }})
     * @Annotation\Filter(name="StripTags")
     * @Annotation\Filter(name="StringTrim")
     * @Annotation\Validator(name="StringLength", options={"min": 1, "max": 8})
     *
     * @var string
     */
    protected $state;

    /**
     * Initialies the roles variable.
     */
    public function __construct()
    {
        $this->state = UserInterface::STATE_ACTIVE;
        $this->role = 'guest';
    }

    /**
     * @param  string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param  string $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param  string $userName
     * @return $this
     */
    public function setUserName($userName)
    {
        $this->username = $userName;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->username;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id();
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get displayName.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->firstName.' '.$this->lastName;
    }

    /**
     * Set displayName.
     *
     * @param string $displayName
     *
     * @return $this
     */
    public function setDisplayName($displayName)
    {
        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state.
     *
     * @param int $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    /**
     * Get role.
     *
     * @return array
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedTs()
    {
        return $this->tsGet($this->createdTs);
    }

    /**
     * @param $ts
     * @return $this
     */
    public function setCreatedTs($ts)
    {
        $this->createdTs = $this->tsSet($ts);

        return $this;
    }

    /**
     * @param string $passwordToken
     */
    public function setPasswordToken($passwordToken)
    {
        $this->passwordToken = $passwordToken;
    }

    /**
     * @return string
     */
    public function getPasswordToken()
    {
        return $this->passwordToken;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedTs()
    {
        return $this->updatedTs;
    }

    /**
     * @param $createdTs
     * @return $this
     */
    public function setUpdatedTs($updatedTs)
    {
        $this->updatedTs = $this->tsSet($updatedTs);

        return $this;
    }

    /**
     * @ORM\PrePersist
     *
     * Doctrine lifecycle callback function. When this object
     * is persisted (committed to the DB for the first time) set
     * the created timestamp to this value.
     *
     */
    public function onPersist()
    {
        $this->setCreatedTs('now');
        $this->setUpdatedTs('now');
    }

    /**
     * @ORM\PreUpdate
     *
     * Doctrine lifecycle callback funciton. When this object
     * is updated for any reason, update the 'updatedTs' value
     * to the current time.
     */
    public function onUpdate()
    {
        $this->setUpdatedTs('now');
    }

    /**
     * @return array
     */
    public function flatten()
    {
        return
            array_merge(
                parent::flatten(),
                array(
                    'username'          => $this->getUsername(),
                    'email'             => $this->getEmail(),
                    'firstName'         => $this->getFirstName(),
                    'lastName'          => $this->getLastName(),
                    'role'             => $this->getRole(),
                    'state'             => $this->getState(),
                )
            );
    }
}
