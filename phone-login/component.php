<?php

/**
 * Запроса нет:
 *  - отображаем кнопку вызова формы
 *
 * Запрос на открытие формы (нажатие на кнопку):
 *  - отображаем форму с полем для телефона
 *
 * Запрос с номером телефона (отрпавка номера телефона):
 *  - отображаем форму с полем для кода
 *
 * Запрос с кодом:
 *  -код правильный:
 *      авторизуем пользователя
 *  -код неправильный:
 *      отображаем сообщение об ошибке
 */

global $USER;

if (isset($_POST['action']) && $_POST['action'] == 'openForm') {
    if (!empty($_SESSION['phone_login']['expire']) && $_SESSION['phone_login']['expire'] > time()) {
        $arResult['CODE_RESEND_TIME'] = PhoneLogin::getCodeResendTime();
        PhoneLogin::showResponse($this, 'code');
    } else {
        unset($_SESSION['phone_login']);
        PhoneLogin::showResponse($this, 'phone');
    }
}


if (isset($_POST['phone'])) {
    if (PhoneLogin::isPhoneValid($_POST['phone'])) {
        $accessCode = random_int(1000, 9999);

        $_SESSION['phone_login'] = [
            'code' => $accessCode,
            'phone' => trim($_POST['phone']),
            'expire' => time() + PhoneLogin::CODE_TIME_TO_LIVE,
        ];

        //Cюда добавить код отправляющий смс

        if (isset($arParams['DEBUG']) && $arParams['DEBUG'] === 'Y')
            $arResult['DEBUG'] = $_SESSION['phone_login'];

        $arResult['CODE_RESEND_TIME'] = PhoneLogin::getCodeResendTime();

        PhoneLogin::showResponse($this, 'code');
    }

    $arResult['ERROR']['MESSAGE'] = 'Телефон указан некорректно';

    PhoneLogin::showResponse($this, 'phone');
}


if (isset($_POST['code'])) {
    if (PhoneLogin::isCodeValid(trim($_POST['code']))) {
        $phone = $_SESSION['phone_login']['phone'];

        $userId = PhoneLogin::getUserIdByPhone($phone) ?? PhoneLogin::userRegister($phone);

        if ($userId) {
            $USER->Authorize($userId);
            $arResult['MESSAGE'] = 'Вы успешно авторизовались';
            $arResult['TYPE'] = 'success';
        } else {
            $arResult['MESSAGE'] = 'Ошибка авторизации, попробуйте еще раз';
            $arResult['TYPE'] = 'error';
        }

        unset($_SESSION['phone_login']);
        PhoneLogin::showResponse($this, 'success');
    }

    $arResult['ERROR']['MESSAGE'] = 'Код неверный';
    $arResult['CODE_RESEND_TIME'] = PhoneLogin::getCodeResendTime();

    PhoneLogin::showResponse($this, 'code');
}

if ($USER->IsAuthorized()) {
    $arResult['LOGOUT_LINK'] = '?logout=yes&' . bitrix_sessid_get();
    $this->IncludeComponentTemplate();
} else {
    $this->IncludeComponentTemplate('button');
}


class PhoneLogin {
    const CODE_TIME_TO_LIVE = 300;
    public static function isCodeValid($code) {
        return !empty($_SESSION['phone_login']['phone'])
            && !empty($_SESSION['phone_login']['code'])
            && $_SESSION['phone_login']['code'] == $code;
    }

    public static function isPhoneValid($phone) {
        return NormalizePhone($phone) !== false
            && strlen(NormalizePhone($phone)) == 11;
    }

    public static function getUserIdByPhone($phone) {
        $formattedPhone = substr(NormalizePhone($phone), 1);

        $user = \Bitrix\Main\UserTable::getList([
            'filter' => [
                [
                    'LOGIC' => 'OR',
                    ['%PERSONAL_PHONE' => $formattedPhone],
                    ['%LOGIN' => $formattedPhone],
                ],
                'ACTIVE' => 'Y',
                'BLOCKED' => 'N',
            ],
            'limit' => 1,
        ])->fetch();

        return $user['ID'] ?? null;
    }

    public static function userRegister($phone) {
        $login = NormalizePhone($phone);
        $password = \CUser::GeneratePasswordByPolicy([]);
        $fields = [
            'LOGIN' => $login,
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $password,
            'EMAIL' => $login . '@autoreg.ru',
            //'GROUP_ID' => $userData['GROUP_ID'],
            'ACTIVE' => 'Y',
            'PERSONAL_PHONE' => $phone,
        ];

        $user = new CUser;
        $userId = $user->add($fields);

        if ($userId)
            return $userId;

        file_put_contents(__DIR__ . '/component.log', print_r($user->LAST_ERROR, true) . PHP_EOL, FILE_APPEND);
        return null;
            //PhoneLogin::sendLoginData($phone, ['login' => $login, 'password' => $password]);

    }

    public static function sendLoginData($phone, $loginData) {
        file_put_contents(__DIR__ . '/sendLoginData.log', print_r([$phone, $loginData], true) . PHP_EOL, FILE_APPEND);
    }

    public static function showResponse($component, $templateName) {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        $component->IncludeComponentTemplate($templateName);
        die;
    }

    public static function getCodeResendTime() {
        $codeResendTime = $_SESSION['phone_login']['expire'] - time();
        if ($codeResendTime < 0)
            $codeResendTime = 0;

        return $codeResendTime;
    }
}