<?php
namespace FzyAuth\Service\Password;

use FzyAuth\Entity\Base\UserInterface;
use FzyAuth\Exception\Password\InvalidUser;
use FzyAuth\Exception\Password\NotSent;
use FzyAuth\Service\Password;
use FzyCommon\Util\Params;
use Ramsey\Uuid\Uuid;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mail\Message;
use Laminas\Mime\Part as MimePart;
use Laminas\Mime\Message as MimeMessage;

/**
 * Class Forgot
 * @package FzyAuth\Service\Password
 * Service Key: FzyAuth\Password\Forgot
 */
class Forgot extends Password
{
    const OPTIONS = 'forgot_options';

    /**
     * Handle
     * @param  UserInterface $user
     * @return mixed
     */
    public function process(UserInterface $user)
    {
        $user->setPasswordToken($this->generatePasswordToken());

        return $this->sendEmail($user);
    }

    /**
     * Creates a cryptographically secure string
     * @return string
     */
    protected function generatePasswordToken()
    {
        $uuid = Uuid::uuid4();

        return $uuid->toString();
    }

    /**
     * Send message to the user
     * @param  UserInterface $user
     * @return bool
     */
    protected function sendEmail(UserInterface $user)
    {
        if ($user->isNull()) {
            $this->log('Unable to send email - user is null');
            // skip everything
            throw new InvalidUser($this->getOptions()->get('invalid_user_error_message', 'Unable to send reset email at this time.'), 400);
        }
        try {
            /* @var $transport TransportInterface */
            $transport = $this->getServiceLocator()->get('FzyAuth\Mail\Transport');
            $transport->send($this->getMessage($user));
            // save key now that the message is sent
            $this->em()->flush($user);
        } catch (\Exception $e) {
            $this->log('Unable to send email - '.$e->getMessage());
            // handle errors in some way
            throw new NotSent($this->getOptions()->get('mail_not_sent_error_message', 'Unable to send reset email at this time.'), 400, $e);
        }
    }

    /**
     * @param  UserInterface $user
     * @return Message
     */
    protected function getMessage(UserInterface $user)
    {
        // Emailing logic here
        $content            = $this->renderMessageContent($user);

        $htmlPart = new MimePart($content);
        $htmlPart->type = "text/html";

        $textPart = new MimePart($content);
        $textPart->type = "text/plain";

        $body = new MimeMessage();
        $body->setParts(array($textPart, $htmlPart));

        $message = new Message();

        $message->addFrom(
            $this->getOptions()->get('from_email'),
            $this->getOptions()->get('from_name')
        );

        $message->addTo(
            $user->getEmail(),
            $user->getFirstName().' '.$user->getLastName()
        );

        $ccs = $this->getOptions()->get('copy_to', array());

        foreach ($ccs as $cc) {
            $message->addCc($cc);
        }

        $message->setSubject(
            $this->getOptions()->get('reset_subject', 'Please Reset Your Password')
        );

        $message->setEncoding("UTF-8");
        $message->setBody($body);
        $message->getHeaders()->get('content-type')->setType('multipart/alternative');

        return $message;
    }

    /**
     * Creates body of reset email based on settings.
     * @param  UserInterface $user
     * @return string
     */
    protected function renderMessageContent(UserInterface $user)
    {
        $viewFile = $this->getOptions()->get('view');

        $viewVars = Params::create(array('user' => $user, 'resetUrl' => $this->url()->fromRoute('fzyauth-password/reset/get', array('token' => $user->getPasswordToken()), array('force_canonical' => true))));
//        $viewVars->merge();

        // render view
        return $this->getServiceLocator()->get('FzyCommon\Render')->handle($viewFile, $viewVars->get());
    }
}
