<?

/* контроллер $_GET  */

class cGet {
    // свойства класса
    public $cName      = 'cGet'; // имя контроллера
    public $aData      = null;   // массив данных для передачи в модель
    public $lSucces    = false;  // результат работы контроллера
    public $lCheckPost = false;  // состояние проверки переменных _POST

    // методы класса
    function __construct($aGet = array()) {
        // "чистим" $aGet
        $aGet = main::$objFunctions->cleanAll($aGet);

        // $_GET['lang'] - язык
        if (isset($aGet['lang'])) {
            main::$objFunctions->setProjectLang($aGet['lang']);
            $cHref = 'http://' . $_SERVER['HTTP_HOST'] . '/';
            header("Location: {$cHref}");
            die();
        }
        // $_GET['event'] - событие
        if (isset($aGet['event'])) {
            // события
            if ($aGet['event'] > 0 || $aGet['event'] == 'all') {
                $this->aData = array('event'=>$aGet['event']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
        }

        // $_GET['page'] - страницы сайта
        if (isset($aGet['page'])) {
            // правила размещения объявлений
            if (in_array($aGet['page'], array('rules'))) {
                $this->aData = array('page'=>$aGet['page']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // о сайте
            if (in_array($aGet['page'], array('about'))) {
                $this->aData = array('page'=>$aGet['page']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // контакты
            if (in_array($aGet['page'], array('contact'))) {
                $this->aData = array('page'=>$aGet['page']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // партнерство
            if (in_array($aGet['page'], array('partner'))) {
                $this->aData = array('page'=>$aGet['page']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
        }

        // !!! ВАЖНО !!! ДОСТУП ТОЛЬКО ЗАРЕГИСТРИРОВАННЫМ ПОЛЬЗОВАТЕЛЯМ
        if (main::$objUserAccess->checkRegisterUser() === true) {

            // сервисы обслуживания платежей пользователя (нет неподтвержденной регистрации)
            if (isset($aGet['PaymentMgmt']) && main::$nLoginError != 3) {
                // список объявлений пользователя
                if (in_array($aGet['PaymentMgmt'], array('all'))) {
                    $this->aData = array('PaymentMgmt'=>$aGet['PaymentMgmt']);
                    // контроллер отработал успешно
                    $this->lSucces = true;
                }
                // Paymaster забраковал платеж
                if (in_array($aGet['PaymentMgmt'], array('failure'))) {
                    $this->aData = array('PaymentMgmt'=>$aGet['PaymentMgmt']);
                    // контроллер отработал успешно
                    $this->lSucces = true;
                }
                // в процессе что-то пошло не так
                if (in_array($aGet['PaymentMgmt'], array('attention'))) {
                    $this->aData = array('PaymentMgmt'=>$aGet['PaymentMgmt']);
                    // контроллер отработал успешно
                    $this->lSucces = true;
                }
            }
        }
        // !!! ВАЖНО !!! ДОСТУП ТОЛЬКО ДЛЯ ЗАРЕГИСТРИРОВАННЫХ ПОЛЬЗОВАТЛЕЙ ИЛИ ИДЕНТИФИЦИРОВАННЫХ ГОСТЕЙ
        if (main::$objUserAccess->checkRegisterUser() === true || main::$objUserAccess->checkGuest() === true) {
            // нет неподтвержденной регистрации
            if (isset($aGet['AdvertMgmt']) && main::$nLoginError != 3) {
                // новое объявление
                if (in_array($aGet['AdvertMgmt'], array('new'))) {
                    // проверяем возможное наличие выбранного на сайте rp5 населенного пункта
                    if (isset($aGet['targetPlace'])) {
                        // проверка корректности данных
                    } else {
                        $this->aData = array('AdvertMgmt'=>$aGet['AdvertMgmt']);
                        // контроллер отработал успешно
                        $this->lSucces = true;
                    }
                }
                // сохранить новое объявление пользователя
                if (in_array($aGet['AdvertMgmt'], array('newadvertstore'))) {
                    // проверка валидности массива main::$aPost
                    if (main::$objFunctions->aPostValidate(array('title','title_type','title_place','content_1','phone','useremail','url_show','url_target','lang','txt','timewait')) === true) {
                        // сохраняем в переменную сессии для сохранения данных при reload страницы
                        if (!is_null(main::$aPost))
                            $_SESSION['aPost'] = main::$aPost;
                        // проверка состояния _POST переменных
                        $this->checkPost('newadvertstore');

                        $this->aData = array('AdvertMgmt'=>$aGet['AdvertMgmt']);
                        // контроллер отработал успешно
                        $this->lSucces = true;
                    }
                }
                // объявление успешно сохранено
                if (in_array($aGet['AdvertMgmt'], array('success'))) {
                    $this->aData = array('AdvertMgmt'=>$aGet['AdvertMgmt']);
                    // контроллер отработал успешно
                    $this->lSucces = true;
                }
                // объявление уже существует
                if (in_array($aGet['AdvertMgmt'], array('exist'))) {
                    $this->aData = array('AdvertMgmt'=>$aGet['AdvertMgmt']);
                    // контроллер отработал успешно
                    $this->lSucces = true;
                }
                // в процессе что-то пошло не так
                if (in_array($aGet['AdvertMgmt'], array('attention'))) {
                    $this->aData = array('AdvertMgmt'=>$aGet['AdvertMgmt']);
                    // контроллер отработал успешно
                    $this->lSucces = true;
                }
                // в процессе возникла ошибка
                if (in_array($aGet['AdvertMgmt'], array('error'))) {
                    $this->aData = array('AdvertMgmt'=>$aGet['AdvertMgmt']);
                    // контроллер отработал успешно
                    $this->lSucces = true;
                }

                // переназначение исходного домена
                if (isset($_SESSION['rp5www']) && !is_null($_SESSION['rp5www']) && $_SESSION['rp5www'] != '')
                    main::$rp5www = $_SESSION['rp5www'];

            }
        }
        // !!!

        // $_GET['type'] - сервисы доступа и обслуживания данных пользователя
        if (isset($aGet['type'])) {
            // форма - логин пользователя
            if (in_array($aGet['type'], array('login'))) {
                $this->aData = array('type'=>$aGet['type']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // пользователь уже существует
            if (in_array($aGet['type'], array('userexist'))) {
                $this->aData = array('type'=>$aGet['type']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // регистрация пользователя завершена успешно
            if (in_array($aGet['type'], array('regsuccess'))) {
                $this->aData = array('type'=>$aGet['type']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // настроить доступ в аккаунт
            if (in_array($aGet['type'], array('accessaccount'))) {
                $this->aData = array('type'=>$aGet['type']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // операция восстановления пароля прошла успешно
            if (in_array($aGet['type'], array('forgetsuccess'))) {
                $this->aData = array('type'=>$aGet['type']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // предупреждение
            if (in_array($aGet['type'], array('attention'))) {
                $this->aData = array('type'=>$aGet['type']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // операция завершена успешно
            if (in_array($aGet['type'], array('success'))) {
                $this->aData = array('type'=>$aGet['type']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // операция завершена с ошибкой
            if (in_array($aGet['type'], array('error'))) {
                $this->aData = array('type'=>$aGet['type']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // ограничен доступ к странице
            if (in_array($aGet['type'], array('service'))) {
                $this->aData = array('type'=>$aGet['type']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // ограничен доступ к странице со служебной информацией
            if (in_array($aGet['type'], array('confidential'))) {
                $this->aData = array('type'=>$aGet['type']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
            // форма восстановления пароля
            if (in_array($aGet['type'], array('forget'))) {
                $this->aData = array('type'=>$aGet['type']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }

            // данные пользователя изменен успешно
            if (in_array($aGet['type'], array('userdatasuccess'))) {
                $this->aData = array('type'=>$aGet['type']);
                // контроллер отработал успешно
                $this->lSucces = true;
            }
        }
    }

    /*
     * проверка состояния _POST переменных
     */
    private function checkPost($cParam) {
        // изменение пароля пользователя
        // редактировать объявление
        if ($cParam == 'advertstore') {
            if (main::$aPost['advert'] > 0) {
                if (main::$aPost['title'] > 0) {
                    if (main::$aPost['title_place'] != main::$objAdvertFunctions->cSelect_2) {
                        if (main::$aPost['content_1'] != '') {
                            // все ОК
                            $this->lCheckPost = true;
                        } else
                            $_SESSION['nPostError'] = 6;
                    } else
                        $_SESSION['nPostError'] = 6;
                } else
                    $_SESSION['nPostError'] = 6;
            } else
                $_SESSION['nPostError'] = 6;
        }
        // новое объявление
        if ($cParam == 'newadvertstore') {
            if (main::$aPost['title'] > 0) {
                if (main::$aPost['title_place'] != main::$objAdvertFunctions->cSelect_2) {
                    if (main::$aPost['content_1'] != '') {
                        // все ОК
                        $this->lCheckPost = true;
                    } else
                        $_SESSION['nPostError'] = 6;
                } else
                    $_SESSION['nPostError'] = 6;
            } else
                $_SESSION['nPostError'] = 6;
        }
        // sms-код завершения регистрации
        if ($cParam == 'checksms') {
            if (main::$aPost['smscode'] != '') {
                // все ОК
                $this->lCheckPost = true;
            } else
                $_SESSION['nPostError'] = 6;
        }
    }
}

?>
