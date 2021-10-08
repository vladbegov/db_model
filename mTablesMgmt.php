<?

/* модель - работа с таблицами */

class mTablesMgmt {
    // свойства класса
    public $aResult         = null;  // список данных
    public $lSucces         = false; // результат работы класса
    private $aTableTemplate = null;  // настройки для таблицы

    // методы класса
    function __construct($aIn = array()) {
        // строим строку-результат
        foreach ($aIn as $inKey=>$inValue) {
            if ($inKey == 'aParam' && is_array($inValue) && count($inValue) > 0) {
                foreach ($inValue as $key=>$value) {
                    if ($key == 'table' && $value != '') {
                        // настройки для работы с моделью mTablesMgmt
                        $objTableTemplate = null;
                        // загрузка настроек
                        eval("\$objTableTemplate = new tt".ucfirst($value)."();");

                        if (is_object($objTableTemplate) && $objTableTemplate->lSucces === true)
                            $this->aTableTemplate = $objTableTemplate->aResult;

                        unset($objTableTemplate);

                        if (is_array($this->aTableTemplate))
                            $this->lSucces = true;
                    }
                }
                if ($this->lSucces === true) {
                    foreach ($inValue as $key=>$value) {
                        if ($key == 'data' && $value == 'all') {
                            // получаем список всех данных
                            $this->aResult = $this->getDataList();
                        }
                    }
                }
            }
        }
    }

    /*
     * Получаем строку данных по Id
     */
    public function getData($Id) {
        if ($Id > 0) {
            foreach ($this->aResult as $row) {
                if ($row[$this->aTableTemplate['getData']] == $Id)
                    return $row;
            }
        }
        main::$cScriptLog .= '001-' . $this->aTableTemplate['cScriptLog'];
        return false;
    }

    /*
     * Список всех новостей
     */
    public function getDataList() {
        // каталог с кэшиками с информацией всех данных
        main::$objCache->cDir = main::$documentRoot . '/' . main::$root . '/' . main::$protected_RS;
        main::$objLock->cDir  = main::$documentRoot . '/' . main::$root . '/' . main::$protected_RL;

        $cSubDir0   = '';
        $cSubDir1   = '';
        $cSubDir2   = '';
        $cSubDir3   = '';
        $cCacheFile = $this->aTableTemplate['cCacheFile'];

        // время жизни блокировки процесса, сек
        $nLockLiveSec = 10;
        // время жизни кэша, количество дней
        $nCacheLiveDays = 3;
        // время жизни кэша не более указанного количества дней
        $nCacheLive = $nCacheLiveDays * 24 * 3600;

        $aCacheData = main::$objCache->getCache($cCacheFile, $cSubDir0, $cSubDir1, $cSubDir2, $cSubDir3, $nCacheLive);

        if ($aCacheData === false) {
            // нет файла, или время его жизни истекло, строим новый кэш
            // переинициализация массива
            $aCacheData = array();

            // блокировка процесса
            if (main::$objLock->makeLock($cCacheFile, $nLockLiveSec)) {
                // запрос в БД
                $sQuery = preg_replace("/#/", "$", $this->aTableTemplate['getDataListSql']);
                $result = main::$objDataBase->dbQuery($sQuery);

                if (is_array($result) && count($result) > 0) {
                    // создаем кэш
                    $aCacheData = $result;
                    main::$objCache->makeCache($cCacheFile, $aCacheData, $cSubDir0, $cSubDir1, $cSubDir2, $cSubDir3);
                } else
                    main::$cScriptLog .= '002-' . $this->aTableTemplate['cScriptLog'];

                main::$objLock->deleteLock($cCacheFile);
            }
        }

        return $aCacheData;
    }

    /*
     * Добавить (редактировать) строку данных
     * Важно!!! Входящие данные проверены в контроллере cServiceGet
     */
    public function dataStore($edit) {
        // проверяем корректность $_Post данных
        if (!is_null(main::$aPost)) {
            if ($edit === true) {
                if ($this->updateData(main::$aPost) === true) {
                    // данные успешно сохранены
                    return true;
                } else {
                    main::$cScriptLog .= '003-' . $this->aTableTemplate['cScriptLog'];
                    return false;
                }
            } else {
                $result = $this->insertData(main::$aPost);
                if ($result === true) {
                    // новые данные успешно сохранены
                } else {
                    if ($result === false)
                        main::$cScriptLog .= '004' . $this->aTableTemplate['cScriptLog'];
                }
                return $result; // true,false,'001'-такая новость уже существует
            }
        } else
            main::$cScriptLog .= '005-' . $this->aTableTemplate['cScriptLog'];

        return false;
    }

    /*
     * сохранение изменений
     */
    private function updateData($aIn) {
        $Id = 0;

        // переменные из переменных
        foreach ($this->aTableTemplate['updateDataVars'] as $key => $value)
            $$key = $aIn[$value];

        $userId  = main::$aServiceInfo['userid'];

        $dateupd = gmdate('Y-m-d');
        $timeupd = main::$objFunctions->getCurrentTime();

        // обновляем данные
        $sQuery = preg_replace("/#/", "$", $this->aTableTemplate['updateDataSql']);
        eval ("\$sQuery = \"$sQuery\";");

        $result = main::$objDataBase->dbQuery($sQuery);
        if (!$result) {
            main::$cScriptLog .= '006-' . $this->aTableTemplate['cScriptLog'];
            return false;
        } else {
            // работа с изображениями
            if (isset(main::$aFiles['image1']['name'])) {
                $fileImage1 = trim(main::$aFiles['image1']['name']);
                if ($fileImage1 <> '') {
                    $cImage = $timeupd . '.jpg';
                    $result = $this->setImages(1, $Id, $cImage, $aIn);
                } else
                    $result = $this->setImages(1, $Id, '', $aIn);
            }

            // удаляем кэшик со всеми данными
            main::$objCache->cDir = main::$documentRoot . '/' . main::$root . '/' . main::$protected_RS;
            $cSubDir0   = '';
            $cSubDir1   = '';
            $cCacheFile = $cSubDir0 . '/' . $cSubDir1 . '/' . $this->aTableTemplate['cCacheFile'];
            if (main::$objCache->deleteCache($cCacheFile) === false) {
                // ошибка удаления кэшика
                main::$cScriptLog .= '007-' . $this->aTableTemplate['cScriptLog'];
                return false;
            }
        }

        return true;
    }

    /*
     * новая строка данных
     */
    private function insertData($aIn) {
        // переменные из переменных
        foreach ($this->aTableTemplate['insertDataVars'] as $key => $value)
            $$key = $aIn[$value];

        $userId   = main::$aServiceInfo['userid'];

        $datereg  = gmdate('Y-m-d');
        $timereg  = main::$objFunctions->getCurrentTime();

        // проверка на наличие такой строки данных
        $sQuery = preg_replace("/#/", "$", $this->aTableTemplate['insertDataSql_0']);
        eval ("\$sQuery = \"$sQuery\";");

        $result = main::$objDataBase->dbQuery($sQuery);

        if (is_array($result) && count($result) == 1) {
            // ОШИБКА !!! СТРОКА ДАННЫХ УЖЕ СУЩЕСТВУЕТ !!!
            return '001';
        }

        // добавляем запись в таблицу
        $sQuery = preg_replace("/#/", "$", $this->aTableTemplate['insertDataSql_1']);
        eval ("\$sQuery = \"$sQuery\";");

        $result = main::$objDataBase->dbQuery($sQuery);
        if (!$result) {
            main::$cScriptLog .= '008-' . $this->aTableTemplate['cScriptLog'];
            return false;
        } else {
            // удаляем кэшик со всеми данными
            main::$objCache->cDir = main::$documentRoot . '/' . main::$root . '/' . main::$protected_RS;
            $cSubDir0   = '';
            $cSubDir1   = '';
            $cCacheFile = $cSubDir0 . '/' . $cSubDir1 . '/' . $this->aTableTemplate['cCacheFile'];
            if (main::$objCache->deleteCache($cCacheFile) === false) {
                // ошибка удаления кэшика
                main::$cScriptLog .= '009-' . $this->aTableTemplate['cScriptLog'];
                return false;
            }
        }

        return true;
    }

    /*
     * удалить строку данных
     */
    public function deleteData($Id) {
        // удаляем данные
        $sQuery = preg_replace("/#/", "$", $this->aTableTemplate['deleteDataSql']);
        eval ("\$sQuery = \"$sQuery\";");

        $result = main::$objDataBase->dbQuery($sQuery);
        if (!$result) {
            main::$cScriptLog .= '010-' . $this->aTableTemplate['cScriptLog'];
            return false;
        } else {
            // удаляем кэшик со всеми данными
            main::$objCache->cDir = main::$documentRoot . '/' . main::$root . '/' . main::$protected_RS;
            $cSubDir0   = '';
            $cSubDir1   = '';
            $cCacheFile = $cSubDir0 . '/' . $cSubDir1 . '/' . $this->aTableTemplate['cCacheFile'];
            if (main::$objCache->deleteCache($cCacheFile) === false) {
                // ошибка удаления кэшика
                main::$cScriptLog .= '011-' . $this->aTableTemplate['cScriptLog'];
                return false;
            }
        }

        return true;
    }

    /*
     * изображения
     */
    private function setImages($nImageNumb, $Id, $cImage, $aIn) {
        // признак удаления изображения
        $lImageDelete = false;

        $sQuery = preg_replace("/#/", "$", $this->aTableTemplate['setImagesSql0']);

        eval ("\$sQuery = \"$sQuery\";");

        $result = main::$objDataBase->dbQuery($sQuery);
        if (!$result) {
            main::$cScriptLog .= '011-' . $this->aTableTemplate['cScriptLog'];
            return false;
        } else {
            foreach ($result as $row) {
                $images  = trim($row['images']);
                $aImages = explode(":", $images);

                // загрузить изображение
                if ($cImage != '') {
                    // имя файла в таблице
                    $sImageName=':' . $cImage . ':';

                    if (!strstr($images, $sImageName)) {
                        if (main::$objFunctions->makePhotoUpload($nImageNumb, $cImage)) {
                            $images = $images . $sImageName;

                            $sQuery = preg_replace("/#/", "$", $this->aTableTemplate['setImagesSql1']);
                            eval ("\$sQuery = \"$sQuery\";");

                            $result = main::$objDataBase->dbQuery($sQuery);
                            if (!$result) {
                                main::$cScriptLog .= '012-' . $this->aTableTemplate['cScriptLog'];
                                return false;
                            }
                        } else {
                            main::$cScriptLog .= '013-' . $this->aTableTemplate['cScriptLog'];
                            return false;
                        }
                    }
                }

                // удаление изображений
                for ($ii = 0; $ii < count($aImages)-1; $ii++) {
                    $imageId = trim(substr($aImages[$ii], 0, 10));
                    if ($imageId != '') {
                        if (isset($aIn['del'.$imageId]) && $aIn['del'.$imageId] == 1) {
                            $sImageName = $aImages[$ii];
                            $nPos = strpos($images, $sImageName);

                            if ($nPos >= 0) {
                                $lImageDelete = true;
                                $sFile = $this->aTableTemplate['cRootPrefix'] . '/images/content/' . $sImageName;
                                @unlink($sFile);
                                $sFile = $this->aTableTemplate['cRootPrefix'] . '/images/content/small/' . $sImageName;
                                @unlink($sFile);

                                $images = str_replace($sImageName, '', $images);
                            }
                        }
                    }
                }
                if ($lImageDelete === true) {
                    $sQuery = preg_replace("/#/", "$", $this->aTableTemplate['setImagesSql1']);
                    eval ("\$sQuery = \"$sQuery\";");

                    $result = main::$objDataBase->dbQuery($sQuery);
                    if (!$result) {
                        main::$cScriptLog .= '014-' . $this->aTableTemplate['cScriptLog'];
                        return false;
                    }
                }

                // удаляем кэшик со всеми данными
                main::$objCache->cDir = main::$documentRoot . '/' . main::$root . '/' . main::$protected_RS;
                $cSubDir0   = '';
                $cSubDir1   = '';
                $cCacheFile = $cSubDir0 . '/' . $cSubDir1 . '/' . $this->aTableTemplate['cCacheFile'];
                if (main::$objCache->deleteCache($cCacheFile) === false) {
                    // ошибка удаления кэшика
                    main::$cScriptLog .= '015-' . $this->aTableTemplate['cScriptLog'];
                    return false;
                }
            }
        }
        return true;
    }

}

?>