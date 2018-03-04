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
use MxmUser\Service\DateTimeInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Validator\Db\RecordExists;
use Zend\Validator\EmailAddress;
use Zend\Validator\NotEmpty;
use MxmUser\Exception\RuntimeUserException;
use MxmUser\Exception\ExpiredUserException;
use MxmUser\Exception\NotAuthenticatedUserException;
use MxmUser\Exception\InvalidArgumentUserException;
use Zend\Crypt\Password\Bcrypt;
use MxmUser\Exception\RecordNotFoundUserException;
use MxmUser\Exception\AlreadyExistsUserException;
use MxmUser\Exception\InvalidPasswordUserException;
use MxmUser\Exception\NotAuthorizedUserException;
use MxmRbac\Service\AuthorizationService;
use MxmMail\Service\MailService;
use Zend\Math\Rand;
use Zend\Session\Container as SessionContainer;
use Zend\i18n\Translator\TranslatorInterface;
use MxmUser\Validator\IsPropertyMatchesDb;

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

    protected $sessionContainer;

    public function __construct(
        MapperInterface $mapper,
        \DateTimeInterface $datetime,
        AuthenticationService $authService,
        EmailAddress $emailValidator,
        NotEmpty $notEmptyValidator,
        RecordExists $isUserExists,
        IsPropertyMatchesDb $isRoleMatchesDb,
        AuthorizationService $authorizationService,
        Bcrypt $bcrypt,
        MailService $mail,
        SessionContainer $sessionContainer,
        TranslatorInterface $translator
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
    }

    /**
     * {@inheritDoc}
     */
    public function findAllUsers()
    {
        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('find.users')) {
            throw new NotAuthorizedUserException('Access denied. Permission "find.users" is required.');
        }

        return $this->mapper->findAllUsers();
    }

    /**
     * {@inheritDoc}
     */
    public function findUserById($id)
    {
        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }

        $user = $this->mapper->findUserById($id);
        if ($user instanceof UserInterface) {
            if (!$this->authorizationService->isGranted('find.user')) {
                throw new NotAuthorizedUserException('Access denied. Permission "find.user" is required.');
            }
        } else {
            throw new Exception\RuntimeException('mapper->findUserById returns value which does not implement UserInterface');
        }

	return $user;
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

        $user->setRole('user');

        $token = Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz', true);
        $user->setEmailToken($token);
        $user->setDateEmailToken($this->datetime->modify('now'));
	$user->setEmailVerification(false);

	$httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

        $confirmEmailUrl = '<a href="' . 'http://' . $httpHost . '/confirm/email/' . $token . '">Confirm Email</a>';

        $body = $this->translator->translate("Please follow the link below to confirm your email") . ":\n";

        $body .= " $confirmEmailUrl\n";
        $body .= $this->translator->translate("If you haven't registered, please ignore this message") . "\n";

//        $this->mail->setSubject('Confirm Email')->setBody($body)
//            ->setFrom('qwer_qwerty_2018@inbox.ru')->setSenderName('blog-note3')
//            ->setTo($user->getEmail())->setRecipientName($user->getUsername());
//
//	$this->mail->send();

        return $this->mapper->insertUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser(UserInterface $user)
    {
        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('edit.user', $user)) {
            throw new NotAuthorizedUserException('Access denied. Permission "edit.user" is required.');
        }

        if (!$this->isRoleMatchesDb->isValid($user) && !$this->authorizationService->isGranted('change.roles')) {
            throw new NotAuthorizedUserException('Access denied. Permission "change.roles" is required.');
        }

        return $this->mapper->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(UserInterface $user)
    {
        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('delete.user', $user)) {
            throw new NotAuthorizedUserException('Access denied. Permission "delete.user" is required.');
        }

        return $this->mapper->deleteUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function editEmail($email, $password)
    {
        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('edit.email')) {
            throw new NotAuthorizedUserException('Access denied. Permission "edit.email" is required.');
        }

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
        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('edit.password')) {
            throw new NotAuthorizedUserException('Access denied. Permission "edit.password" is required.');
        }

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
        if (!$this->authService->hasIdentity()) {
            throw new RuntimeUserException('The user is not logged in');
        }
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

        $subject = $this->translator->translate('Password Reset');

        $httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

        $passwordResetUrl = '<a href="' . 'http://' . $httpHost . '/set/password/' . $token . '">' . $this->translator->translate('Reset password') . '</a>';

        $body = $this->translator->translate("Please follow the link below to reset your password") . ":\n";
        $body .= " $passwordResetUrl\n";
        $body .= $this->translator->translate("If you haven't asked to reset your password, please ignore this message") . "\n";

        $this->mail->sendEmail($subject, $body, 'birisinsk@mail.ru', 'blog-note3', $user->getEmail(), $user->getUsername());

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
            if ($interval->d > 1) {     //TODO срок годности токена вынести в настройки d - кол-во дней
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