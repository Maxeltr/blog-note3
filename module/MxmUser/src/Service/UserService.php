<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MxmUser\Service;

use MxmUser\Mapper\MapperInterface;
use MxmUser\Model\UserInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Validator\Db\RecordExists;
use Zend\Validator\EmailAddress;
use Zend\Validator\NotEmpty;
use MxmUser\Exception\RuntimeUserException;
use MxmUser\Exception\ExpiredUserException;
use MxmUser\Exception\InvalidArgumentUserException;
use Zend\Crypt\Password\Bcrypt;
use MxmUser\Exception\RecordNotFoundUserException;
use MxmUser\Exception\AlreadyExistsUserException;
use MxmUser\Exception\InvalidPasswordUserException;
use MxmRbac\Service\AuthorizationService;
use MxmMail\Service\MailService;
use Zend\Math\Rand;
use Zend\Session\Container as SessionContainer;
use Zend\i18n\Translator\TranslatorInterface;
use MxmUser\Validator\IsPropertyMatchesDb;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Http\PhpEnvironment\Request;
use DateTimeImmutable;

class UserService implements UserServiceInterface
{
    /**
     * @var \User\Mapper\MapperInterface;
     */
    protected $mapper;

    /**
     * @var DateTimeInterface;
     */
    protected $datetime;

    /**
     * @var Zend\Authentication\AuthenticationService;
     */
    protected $authService;

    /**
     * @var Zend\Validator\EmailAddress;
     */
    protected $emailValidator;

    /**
     * @var Zend\Validator\NotEmpty;
     */
    protected $notEmptyValidator;

    /**
     * @var Zend\Validator\Db\RecordExists;
     */
    protected $isUserExists;

    /**
     * @var MxmUser\Validator\IsPropertyMatchesDb
     */
    protected $isRoleMatchesDb;

    /**
     * @var MxmRbac\Service\AthorizationService
     */
    protected $authorizationService;

    /**
     * @var Zend\Crypt\Password\Bcrypt
     */
    protected $bcrypt;

    /**
     * @var MxmMail\Service\MailService
     */
    protected $mail;

    /**
     * @var Zend\i18n\Translator\TranslatorInterface
     */
    protected $translator;

    /**
     * @var Zend\Session\Container
     */
    protected $sessionContainer;

    /**
     * @var use Zend\Stdlib\RequestInterface;
     */
    protected $request;

    use EventManagerAwareTrait;

    public function __construct(
        MapperInterface $mapper,
        DateTimeImmutable $datetime,
        AuthenticationService $authService,
        EmailAddress $emailValidator,
        NotEmpty $notEmptyValidator,
        RecordExists $isUserExists,
        IsPropertyMatchesDb $isRoleMatchesDb,
        AuthorizationService $authorizationService,
        Bcrypt $bcrypt,
        MailService $mail,
        SessionContainer $sessionContainer,
        TranslatorInterface $translator,
        Request $request
    ) {
        $this->mapper = $mapper;
        $this->datetime = $datetime;
        $this->authService = $authService;
        $this->emailValidator = $emailValidator;
        $this->notEmptyValidator = $notEmptyValidator;
        $this->isUserExists = $isUserExists;
        $this->isRoleMatchesDb = $isRoleMatchesDb;
        $this->authorizationService = $authorizationService;
        $this->bcrypt = $bcrypt;
        $this->mail = $mail;
        $this->sessionContainer = $sessionContainer;
        $this->translator = $translator;
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function findAllUsers()
    {
        $this->authService->checkIdentity();

        $this->authorizationService->checkPermission('find.users');

        return $this->mapper->findAllUsers();
    }

    /**
     * {@inheritDoc}
     */
    public function findUserById($id)
    {
        $this->authService->checkIdentity();

        $this->authorizationService->checkPermission('find.user');

        $user = $this->mapper->findUserById($id);

	return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function findUsersByRole($role)
    {
        $this->authService->checkIdentity();

        $this->authorizationService->checkPermission('find.users');

        if (! is_string($role)) {
            throw new InvalidArgumentUserException(sprintf(
                'Role must be a string, received "%s"',
                (is_object($role) ? get_class($role) : gettype($role))
            ));
        }

        return $this->mapper->findUsersByRole($role);
    }

    /**
     * {@inheritDoc}
     */
    public function insertUser(UserInterface $user)
    {
        if ($this->isUserExists->isValid($user->getEmail())) {
            throw new AlreadyExistsUserException("User with email address " . $user->getEmail() . " already exists");
        }

        $passwordHash = $this->bcrypt->create($user->getPassword());
        $user->setPassword($passwordHash);

        $user->setCreated($this->datetime->modify('now'));

        $totalUsers = $this->mapper->findAllUsers()->getTotalItemCount();
        if ($totalUsers === 0) {
            $user->setRole('admin');
        } else {
            $user->setRole('user');
        }

        $token = Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz', true);
        $user->setEmailToken($token);
        $user->setDateEmailToken($this->datetime->modify('now'));
	$user->setEmailVerification(false);

        $subject = $this->translator->translate('Confirm Email');

        $host = $this->request->getServer('HTTP_HOST', null);
	$httpHost = isset($host) ? $host : 'localhost';

        $confirmEmailUrl = '<a href="' . 'http://' . $httpHost . '/confirm/email/' . $token . '">' . $this->translator->translate("Confirm Email") . '</a>';

        $body = $this->translator->translate("Please follow the link below to confirm your email") . ":" . "<br>";

        $body .= $confirmEmailUrl . "<br>";
        $body .= $this->translator->translate("If you haven't registered, please ignore this message") . "<br>";

        $this->mail->setSubject($subject)->setBody($body, 'text/html')
            ->setFrom('birisinsk@mail.ru', 'blog-note3')
            ->setTo($user->getEmail(), $user->getUsername());

	$this->mail->send();

        return $this->mapper->insertUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser(UserInterface $user)
    {
        $this->authService->checkIdentity();

        $this->authorizationService->checkPermission('edit.user', $user);

        if (! $this->isRoleMatchesDb->isValid($user)) {
            $this->authorizationService->checkPermission('change.role');
        }

        return $this->mapper->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(UserInterface $user)
    {
        $this->authService->checkIdentity();

        $this->authorizationService->checkPermission('delete.user', $user);

        $this->getEventManager()->trigger(__FUNCTION__, $this, ['user' => $user]);

        return $this->mapper->deleteUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function editEmail($email, $password)
    {
        $this->authService->checkIdentity();

        $this->authorizationService->checkPermission('edit.email');

        if (!$this->notEmptyValidator->isValid($password)) {
            throw new InvalidArgumentUserException("No params given: password.");
        }

        if (!$this->emailValidator->isValid($email)) {
            throw new InvalidArgumentUserException("No params given: email.");
        }

        $currentUser = $this->authService->getIdentity();

        if (!$this->bcrypt->verify($password, $currentUser->getPassword())) {
            throw new InvalidPasswordUserException('Incorrect password.');
        }

        $currentUser->setEmail($email);

        return $this->mapper->updateUser($currentUser);
    }

    /**
     * {@inheritDoc}
     */
    public function editPassword($oldPassword, $newPassword)
    {
        $this->authService->checkIdentity();

        $this->authorizationService->checkPermission('edit.password');

        if (!$this->notEmptyValidator->isValid($oldPassword) or !$this->notEmptyValidator->isValid($newPassword)) {
            throw new InvalidArgumentUserException("No params given: oldPassword or newPassword.");
        }

        $currentUser = $this->authService->getIdentity();

        if (!$this->bcrypt->verify($oldPassword, $currentUser->getPassword())) {
            throw new InvalidPasswordUserException('Incorrect old password.');
        }

        $currentUser->setPassword($this->bcrypt->create($newPassword));

        return $this->mapper->updateUser($currentUser);
    }

    /**
     * {@inheritDoc}
     */
    public function loginUser($email, $password)
    {
        if (!$this->notEmptyValidator->isValid($password)) {
            throw new InvalidArgumentUserException("No params given: password.");
        }

        if (!$this->emailValidator->isValid($email)) {
            throw new InvalidArgumentUserException("No params given: email.");
        }

        if ($this->authService->hasIdentity()) {
            throw new RuntimeUserException('The user already logged in');
        }

        $authAdapter = $this->authService->getAdapter();
        $authAdapter->setEmail($email);
        $authAdapter->setPassword($password);
        $result = $this->authService->authenticate();

        if ($result->isValid()) {
            try {
                $user = $this->mapper->findUserByEmail($email);
            } catch (\Exception $e) {
                throw new RecordNotFoundUserException("User with email address " . $email . " doesn't exists");
            }

            $storage = $this->authService->getStorage();
            $storage->write($user);

            if ($user->getEmailVerification() !== true && $this->isEmailTokenExpired($user) === true) {
                $token = $user->getEmailToken();
                $userId = $user->getId();
                $this->mapper->deleteUser($user);
                $this->logoutUser();
                throw new ExpiredUserException("Email token " . $token . " expired. User id " . $userId . ' was deleted.');
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function logoutUser()
    {
        $this->authService->checkIdentity();
        $this->authService->clearIdentity();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function resetPassword($email)
    {
        if (!$this->emailValidator->isValid($email)) {
            throw new InvalidArgumentUserException("No params given: email.");
        }

        try {
            $user = $this->mapper->findUserByEmail($email);
        } catch (\Exception $e) {
            throw new RecordNotFoundUserException("User with email address " . $email . " doesn't exists");
        }

        $token = Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz', true);
        $user->setPasswordToken($token);
        $user->setDateToken($this->datetime->modify('now'));

        $this->mapper->updateUser($user);

        $subject = $this->translator->translate('Password reset');

        $host = $this->request->getServer('HTTP_HOST', null);
	$httpHost = isset($host) ? $host : 'localhost';

        $passwordResetUrl = '<a href="' . 'http://' . $httpHost . '/set/password/' . $token . '">' . $this->translator->translate('Reset password') . '</a>';

        $body = $this->translator->translate("Please follow the link below to reset your password") . ":" . "<br>";
        $body .= $passwordResetUrl . "<br>";
        $body .= $this->translator->translate("If you haven't asked to reset your password, please ignore this message") . "<br>";

        $this->mail->setSubject($subject)->setBody($body, 'text/html')
            ->setFrom('birisinsk@mail.ru', 'blog-note3')
            ->setTo($user->getEmail(), $user->getUsername());

	$this->mail->send();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPassword($newPassword, $token)
    {
        if (!$this->notEmptyValidator->isValid($newPassword)) {
            throw new InvalidArgumentUserException("No params given: password.");
        }
        if (!$this->notEmptyValidator->isValid($token)) {
            throw new InvalidArgumentUserException("No params given: token.");
        }

        try {
            $user = $this->mapper->findUserByResetPasswordToken($token);
        } catch (\Exception $e) {
            throw new RecordNotFoundUserException('Token ' . $token . ' does not exists');
        }

        if ($this->isPasswordTokenExpired($user) === true) {
            throw new ExpiredUserException("Password token " . $token . " expired. User id " . $user->getId());
	}

        $passwordHash = $this->bcrypt->create($newPassword);
        $user->setPassword($passwordHash);

        return $this->mapper->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
	public function confirmEmail($token)
    {
        if (!$this->notEmptyValidator->isValid($token)) {
            throw new InvalidArgumentUserException("No params given: token.");
        }

        try {
            $user = $this->mapper->findUserByEmailToken($token);
        } catch (\Exception $e) {
            throw new RecordNotFoundUserException("Token does not exists");
        }

	if ($this->isEmailTokenExpired($user)) {
            throw new ExpiredUserException("Email token " . $token . " expired. User id " . $user->getId());
	}

        $user->setEmailVerification(true);

        return $this->mapper->updateUser($user);
    }

    private function isEmailTokenExpired($user)
    {
	$tokenCreationDate = $user->getDateEmailToken();
        if ($tokenCreationDate instanceof \DateTimeInterface) {
            $currentDate = $this->datetime->modify('now');
            $interval = $tokenCreationDate->diff($currentDate);
            if ($interval->days > 1) {     //TODO срок годности токена вынести в настройки d - кол-во дней
                return true;
            }
        }

	return false;
    }

    private function isPasswordTokenExpired($user)
    {
        $tokenCreationDate = $user->getDateToken();
        if ($tokenCreationDate instanceof \DateTimeInterface) {
            $currentDate = $this->datetime->modify('now');
            $interval = $tokenCreationDate->diff($currentDate);
            if ($interval->i > 1) {     //TODO срок годности токена вынести в настройки
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function changeLanguage($lang)
    {
        if (!$this->notEmptyValidator->isValid($lang)) {
            throw new InvalidArgumentUserException("No params given: language.");
        }

	if ($lang !== 'ru' and $lang !== 'en' and $lang !== 'Ru' and $lang !== 'En') {  //TODO сделать валидатор
            return $this;
	}

	$this->sessionContainer->locale = $lang;

	if ($this->authService->hasIdentity()) {
            $currentUser = $this->authService->getIdentity();
            $currentUser->setLocale($lang);
            $this->mapper->updateUser($currentUser);
        }

        return $this;
    }
}