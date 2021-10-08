<?

/* представление - все новости  */

class vgNewsMgmt {
    // свойства класса
    public $sResult = '';           // строка-результат работы класса
    public $aData   = null;         // массив для формирования разметки страницы и данных
    public $lSucces = false;        // результат работы класса
    public $cName   = 'vgNewsMgmt'; // имя представления

    // методы класса
    function __construct($aIn = array()) {
        // конструктор
        $this->aData = $aIn;
    }

    public function render() {
        // строим строку-результат

        $this->sResult .='
            <h1>Редактор новостей</h1>';

        // данные из модели mNewsMgmt
        foreach ($this->aData as $key=>$value) {
            if ($key == 'news') {
                // форма новой новости
                $this->sResult .= $this->formNews(0, '', '', 0, 0, 0, 0, 0);

                $this->sResult .= '
                <div class="Mgmt-container">
                    <input type="button" class="button-blue" value="Создать новость" onClick="displayBlocks(\'#newsMgmt-form-0\',\'\',\'\',true); return false;">
                    <table class="Mgmt-table">
                    <thead>
                        <tr>
                            <th class="Mgmt-table-0-th-0" style="width: 60px;">ID</th>
                            <th class="Mgmt-table-0-th-1" style="width: 402px;">Новость</th>
                            <th class="Mgmt-table-0-th-2" style="width: 402px;">Настройки</th>
                            <th class="Mgmt-table-0-th-3" style="width: 206px;">Обслуживание</th>
                        </tr>
                    </thead>
                    <tbody>';

                foreach ($value as $row) {
                    $newsid     = $row['newsid'];
                    $title      = $row['title'];
                    $title_news = $row['title_news'];
                    $content    = $row['content'];
                    $datereg    = $row['datereg'];
                    $timereg    = $row['timereg'];
                    $status     = $row['status'];
                    $userid     = $row['userid'];

                    $sStatus = '';
                    if ($status == 1)
                        $sStatus .= '<div class="status_message_orange">Показы включены</div>';

                    $this->sResult .=
                            '<tr>
                                 <td class="Mgmt-table-0-td-0 table-cell-center" style="width: 64px;">'.$newsid.'</td>
                                 <td class="Mgmt-table-0-td-1 table-cell" style="width: 406px;">
                                    <div class="right"><a href="" onClick="displayBlocks(\'#newsMgmt-preview-'.$newsid.'\',\'\',\'\',true); return false;"><img src="/images/view-icon.png" border="0" align="absmiddle" title="Предварительный просмотр"></a></div>
                                    <i>'.$title.'</i><br/><b>'.$title_news.'</b><br/>'.nl2br(html_entity_decode($content)).'
                                 </td>
                                 <td class="Mgmt-table-0-td-2 table-cell" style="width: 406px;">'.$sStatus.'</td>
                                 <td class="Mgmt-table-0-td-3 table-cell-center" style="width: 210px;">
                                     <input type="button" class="button" value="Изменить" onClick="displayBlocks(\'#newsMgmt-form-'.$newsid.'\',\'\',\'\',true); return false;">
                                     <input type="button" class="button" value="Удалить" onClick="getUrlConfirm(\''.main::$objFunctions->makeHref('?ModeratorMgmt=newsdelete&news='.$row['newsid']).'\'); return false;">
                                     '.$this->formNews($newsid, $title, $title_news, $content, $status).
                                       $this->previewNews($newsid, $title, $title_news, $content).'
                                 </td>
                             </tr>';
                }

                $this->sResult .=
                    '</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="table-cell-footer-empty-last"></td>
                        </tr>
                    </tfoot>
                    </table>
                </div>';
            }
        }

        main::$content .= '<div class="service-page-content">' . $this->sResult . '</div>';

        // представление отработало успешно
        $this->lSucces = true;
    }

    /*
     * форма новости
     */
    private function formNews($newsid, $title, $title_news, $content, $status) {
        if ($newsid > 0) {
            $action = main::$objFunctions->makeHref('?ModeratorMgmt=newsstore');
            $edit   = true;
            $sFormTitle = 'Изменить новость ID ' . $newsid;
        } else {
            $action = main::$objFunctions->makeHref('?ModeratorMgmt=newnewsstore');
            $edit   = false;
            $sFormTitle = 'Новая новость';
        }

        $sResult ='
            <div id="newsMgmt-form-'.$newsid.'" class="message-outside" style="display: none; position: fixed; top: 7%; left: 50%; margin: 0 0 0 -400px; width: 800px; overflow: hidden;">
                <form method="post" name="newsForm_'.$newsid.'" action="'.$action.'">
                    <div class="message-outside-content">
                        <h1>'.$sFormTitle.'</h1>
                        <table class="form-table">
                            <tbody style="max-height: 1000px; overflow: hidden;">
                            <tr style="background: #fff;">
                                <td class="tdr" style="width: 100px; padding-bottom: 20px;">на сайте:</td>
                                <td class="tdl" style="padding-bottom: 20px;">
                                    <input type="checkbox" name="status"  title="Включить-отключить показы" style="margin-left: 20px;" onClick="if(this.checked) {this.value=1};" value="'.$status.'"';
                                    if ($status == 1) $sResult .='checked';
                                    $sResult .='  > включить показы
                                </td>
                            </tr>

                            <tr style="background: #fff;">
                                <td class="tdr">Дата:<a class="red s20">*</a></td>
                                <td class="tdl">
                                    <input type="text" name="title" id="title_'.$newsid.'" maxlength=100 size=55 class="field" value="'.$title.'"
                                    onFocus="resetToDefaultCid(\'title_'.$newsid.'\',\'border\'); resetToDefaultCid(\'title_'.$newsid.'\',\'color\');"
                                    onKeyUp="checkLength(event, this, \'\');"
                                    onBlur="checkLength(event, this, \'\');">
                                </td>
                            </tr>

                            <tr style="background: #fff;">
                                <td class="tdr">Заголовок:<a class="red s20">*</a></td>
                                <td class="tdl">
                                    <input type="text" name="title_news" id="title_news_'.$newsid.'" maxlength=100 size=55 class="field" value="'.$title_news.'"
                                    onFocus="resetToDefaultCid(\'title_news_'.$newsid.'\',\'border\'); resetToDefaultCid(\'title_news_'.$newsid.'\',\'color\');"
                                    onKeyUp="checkLength(event, this, \'\');"
                                    onBlur="checkLength(event, this, \'\');">
                                </td>
                            </tr>

                            <tr style="background: #fff;">
                                <td colspan="2" style="padding: 20px 0 0 370px;">
                                    <a href="javascript:fnApplyTag(\'newsForm_'.$newsid.'\',\'b\');"><img src="/images/edits/bold.gif" title="Полужирный" border="0" width=23 height=22></a>
                                    <a href="javascript:fnApplyTag(\'newsForm_'.$newsid.'\',\'i\');"><img src="/images/edits/italic.gif" title="Курсив" border=0 width=23 height=22></a>
                                    <a href="javascript:fnApplyTag(\'newsForm_'.$newsid.'\',\'u\');"><img src="/images/edits/underline.gif" title="Подчеркнутый" border=0 width=23 height=22></a>
                                    <a href="javascript:fnApplyTag(\'newsForm_'.$newsid.'\',\'s\');"><img src="/images/edits/s.gif" title="Зачеркнутый" border=0 width=23 height=22></a>
                                </td>
                            </tr>

                            <tr style="background: #fff;">
                                <td class="tdr" style="padding-bottom: 60px;">Содержание:<a class="red s20">*</a></td>
                                <td class="tdl" style="padding-bottom: 20px;">
                                    <TEXTAREA name="content" id="content_'.$newsid.'" class="field" style="width: 600px; height: 260px;"
                                    onFocus="resetToDefaultCid(\'content_'.$newsid.'\',\'border\'); resetToDefaultCid(\'content_'.$newsid.'\',\'color\');"
                                    onClick="setSelectionRangeHandle(\'content_'.$newsid.'\',0,0);"
                                    onKeyUp="checkLength(event, this, \'\');"
                                    onBlur="checkLength(event, this, \'\');">'.$content.'</TEXTAREA>
                                </td>
                            </tr>

                            <tr style="background: #fff;">
                                <td class="tdr">Ссылка:</td>
                                <td class="tdl">
                                    адрес: <input type="text" name="href" class="field" maxlength="100" size="20" value="">&nbsp;
                                    название: <input type="text" name="hrefname" class="field" maxlength="100" size="10" value="">
                                    <input type="button" class="button" value="Link"
                                    onClick="paste(\'newsForm_'.$newsid.'\',\'<a href=\'+document.newsForm_'.$newsid.'.href.value+\' class=href-orange target=_blank>\'+document.newsForm_'.$newsid.'.hrefname.value+\'</a>\', 1);">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    '.($edit?'<input type="hidden" name="news" value="'.$newsid.'">':'').'
                    <div class="button-container">
                        <input type="button" class="button-blue" value="Отмена" onClick="displayBlocks(\'#newsMgmt-form-'.$newsid.'\',\'\',\'\',true); return false;">
                        <input type="submit" class="button-blue" value="Сохранить">
                    </div>
                </form>
            </div>';

//                <input type="button" class="button-blue" value="Сохранить" onClick="checkContentForm(\'newsForm_'.$newsid.'\');">

        return $sResult;
    }

    /*
     * предварительный просмотр новости
     */
    private function previewNews($newsid, $title, $title_news, $content) {
        $cDivTop = '15%';

        $sResult ='
            <div id="newsMgmt-preview-'.$newsid.'" class="message-outside" style="display: none; position: fixed; top: '.$cDivTop.'; left: 50%; margin: 0 0 0 -525px; width: 1049px; overflow: hidden;">
                    <div class="message-outside-content">
                        <h1>Предварительный просмотр новости ID '.$newsid.'</h1>';
                        $sResult .='
                            <div style="width: 1007px; min-height: 130px; margin: 5px 0 0 0; border: 1px solid #fff;">
                                <div class="s17" style="padding: 5px 7px 5px 7px; line-height: 20px">
                                    <i>'.$title.'</i>
                                </div>
                                <div class="s17 bold" style="padding: 5px 7px 5px 7px; line-height: 20px">
                                    '.$title_news.'
                                </div>
                                <div class="s17" style="padding: 1px 8px 5px 8px; line-height: 19px">
                                    '.nl2br(html_entity_decode($content)).'
                                </div>
                            </div>
                    </div>
                    <div class="button-container">
                        <input type="button" class="button-blue" value="Закрыть" onClick="displayBlocks(\'#newsMgmt-preview-'.$newsid.'\',\'\',\'\',true); return false;">
                    </div>
            </div>';

        return $sResult;
    }

}

?>