<?php


namespace App\Auth\Controller;


use Base\Controller\BasicController;
use Service\Auth\AuthService;
use Service\Auth\Model\KeyTypes;
use Service\User\Model\UserModel;
use Service\User\UserService;

class Signup extends BasicController
{
    public function index()
    {
        $isSubmit = (bool) $this->p('submit');
        $isAuthorised = (bool)$this->_userId;

        $name = $this->p('firstname');
        $email = $this->p('email');
        $pass = $this->p('password');

        $message = '';

        if ($isSubmit && !$isAuthorised) {
            if ($name && $email && $pass) {
                $userApi = new UserService();
                $user = $userApi->createUser([
                    UserModel::NAME  => $name,
                    UserModel::EMAIL => $email,
                ]);

                if ($user && isset($user[UserModel::ID])) {
                    $userId = $user[UserModel::ID];
                    $this->_userId = $userId;
                    $this->_secureState->setState(self::STATE_KEY_USER_ID, $this->_userId, self::STATE_AUTHORISE_DEFAULT_TTL);
                    
                    $this->loadUser();
                    
                    $message = 'Вы успено зарегистированы';
                } else {
                    $message = 'Не удалось создать пользователя.';
                }
            } else {
                $message = 'Имя пользователя или пароль не заданы!';
            }
        }
        
        if ($isSubmit && (bool)$this->_userId) {
            $authService = new AuthService();
            $res = $authService->addAuthPair(KeyTypes::EMAIL, $email, $pass, $this->_userId);
            if ($res && $message === '') {
                $message = 'Авторизация по email успешно установлена';
            } elseif (!$res) {
                $message = 'Авторизация под таким email уже существует. ';
            }
        }

        return $this->_render(__FUNCTION__, [
            'name' => $name,
            'email' => $email,
            'message'      => $message,
            'isAuthorised' => $isAuthorised,
        ]);
    }

    private function _createUser($data)
    {

    }

    protected function getClassDirectory()
    {
        return __DIR__;
    }
}