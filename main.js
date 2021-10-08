// признак открытия всплывающего окна (click)
var lModalExist  = false;
var sAjaxError   = 'Превышено время ожидания';

// инициализация JS-данных и запуск функций после загрузки страницы
$(document).ready(function() {
    // утановка значений прогресс-бара
    $('#progress-full').attr({
        value: 0,
        max: 0
    });

    $('#file_full').bind('change', function () {
        var file = this.files[0];
        if (file.size > 3*1000*1024)
            alert('Max upload size is 3Mb');
        if (file.type != 'image/jpeg')
            alert('Type must be .jpg');
        // Also see .name, .type
        // имя выбранного файла
        $("#file-name").empty().html(file.name);
    });

    $('#submit_full').bind('click', function () {
        var sUrl  = 'responses/reUpload.php ';
        try {
            $.ajax({
                type: 'POST',
                url:  sUrl,
                data: new FormData($('form')[0]),
                timeout: 20000,
                // Tell jQuery not to process data or worry about content-type
                // You *must* include these options!
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#progress-full').attr({
                        value: 0,
                        max: 0
                    });
                },
                // Custom XMLHttpRequest
                xhr: function () {
                    var myXhr = $.ajaxSettings.xhr();
                    if (myXhr.upload) {
                        // For handling the progress of the upload
                        myXhr.upload.addEventListener('progress', function (e) {
                            if (e.lengthComputable) {
                                $('#progress-full').attr({
                                    value: e.loaded,
                                    max: e.total
                                });
                            }
                        }, false);
                    }
                    return myXhr;
                },
                success: function(data) {
                    var aData = JSON.parse(data);
                    if (aData['1'] != 'error') {
                        if (aData['1'] == 'success') {
                            if (aData['2'] == '000') {
                                var sMessage = 'Новый файл успешно загружен.';
                                // добавляем строку в список файлов
                                var sNewLi = '<li class="li-list" style="cursor: pointer;"><div id="mi-'+aData['4']+'" class="li-list-marker round-full duration-03" style="background: #7b7b7b;" ' +
                                    'onClick="setActiveImage(\''+aData['3']+'\',\''+aData['4']+'\'); return false;"></div>'+
                                    '<a id="mh-'+aData['4']+'" class="mh href" onClick="showImage(\''+aData['4']+'\',\''+aData['5']+'\',\''+aData['6']+'\'); return false;">' + aData['3'] + '</a></li>';

                                $('.ul-list-edit').prepend(sNewLi);
                            } else if (aData['2'] == '001')
                                var sMessage = 'Файл с таким именем уже был! Новый файл успешно загружен.';
                            else
                                var sMessage = 'Сообщение от сервера ' + aData['2'];
                            $('#message-outside-content').empty().html(sMessage);
                            displayBlocks('#message-outside','','',true);
                        }
                    } else {
                        $('#progress-full').attr({
                            value: 0,
                            max: 0
                        });
                        var sMessage = 'Ошибка данных (' + aData['2'] + ')';
                        $('#message-outside-content').empty().html(sMessage);
                        displayBlocks('#message-outside','','',true);
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#progress-full').attr({
                        value: 0,
                        max: 0
                    });
                    var sMessage = 'Ошибка данных Ajax '+sAjaxError;
                    $('#message-outside-content').empty().html(sMessage);
                    displayBlocks('#message-outside','','',true);
                }
            });
        } catch (e) {
        }
    });

    $('input').change(function(){
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                selectedImage = e.target.result;
                $('#img-prev-upload').attr('src', selectedImage);
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});

// назначение новых изображений фона
function setActiveImage(cImageName, cImageNameShort) {
    //
    var sUrl  = 'responses/reActiveImage.php';
    try {
        $.ajax({
            type: 'POST',
            url:  sUrl,
            data: 'iname='+cImageName,
            timeout: 20000,
            beforeSend: function() {
            },
            success: function(data) {
                //
                var aData = JSON.parse(data);

                if (aData['1'] != 'error') {
                    if (aData['1'] == 'success') {
                        if (aData['2'] == 'set')
                            $('#mi-'+cImageNameShort).css('background','rgba(48,132,181,0.85)');
                        else if (aData['2'] == 'unset')
                            $('#mi-'+cImageNameShort).css('background','#7b7b7b');
                    }
                } else {
                    var sMessage = 'Ошибка данных (' + aData['2'] + ')';
                    $('#message-outside-content').empty().html(sMessage);
                    displayBlocks('#message-outside','','',true);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                // ошибка ajax
                var sMessage = 'Ошибка данных Ajax '+sAjaxError;
                $('#message-outside-content').empty().html(sMessage);
                displayBlocks('#message-outside','','',true);
            }
        });
    } catch (e) {
    }
}

// сохранить изменения и установить новые фоновые изображения
function setNewBgImages() {
    if (confirm("Установить выбранные изображения как фон?")) {
        //
        var sUrl  = 'responses/reSetBgImages.php';
        try {
            $.ajax({
                type: 'POST',
                url:  sUrl,
                data: '',
                timeout: 20000,
                beforeSend: function() {
                },
                success: function(data) {
                    //
                    var aData = JSON.parse(data);

                    if (aData['1'] != 'error') {
                        if (aData['1'] == 'success') {
                            // текущее содержимое core/inc/bd_images_v4.inc
                            $('#current-bg-inc').empty().text(aData['3']);
                            // сообщение
                            var sMessage = 'Операция завершена успешно. Изменения всупят в силу в течение получаса.';
                            $('#message-outside-content').empty().html(sMessage);
                            displayBlocks('#message-outside','','',true);
                        }
                    } else {
                        var sMessage = 'Ошибка данных (' + aData['2'] + ')';
                        $('#message-outside-content').empty().html(sMessage);
                        displayBlocks('#message-outside','','',true);
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    // ошибка ajax
                    var sMessage = 'Ошибка данных Ajax '+sAjaxError;
                    $('#message-outside-content').empty().html(sMessage);
                    displayBlocks('#message-outside','','',true);
                }
            });
        } catch (e) {
        }
    }
}


// превью изображений фона
function showImage(cId, cImageNameFull, cImageNamePrev) {
    $('.mh').css('background','');
    $('#mh-'+cId).css('background','#7b7b7b');

    $('#img-full').css('opacity', 0);
    $('#img-prev').css('opacity', 0);
    $('#img-indicator').css('display','block');

    var tmpImg = new Image() ;

    tmpImg.src = cImageNameFull;
    tmpImg.onload = function() {
        $('#img-indicator').css('display','none');
        $('#img-full').attr('src',tmpImg.src).css('opacity', 1).hide().fadeIn(700);
        $('#img-prev').attr('src', cImageNamePrev).css('opacity', 1).hide().fadeIn(700);
    }
}

function fconfirm(inForm) {
    var cError = "Форма не заполнена:";

    if (cError != "Форма не заполнена:")
        alert (cError);
    else {
        if (inForm.name=='fwaydate') {
            if (inForm.waydata.value != '') {
                cWayDataValue = inForm.waydata.value;
                getMapArrays(cWayDataValue, 1);
            }
        } else if (confirm("Подтверждаете выполнение операции ?")) {
            inForm.submit();
        }
    }
}

// размеры окон
function  GetSizes() {
    var w=document.documentElement; var d=document.body;
    var tww = document.compatMode=='CSS1Compat' && !window.opera?w.clientWidth:d.clientWidth;
    var twh = document.compatMode=='CSS1Compat' && !window.opera?w.clientHeight:d.clientHeight;
    var sl = (window.scrollX)?window.scrollX:(w.scrollLeft)?w.scrollLeft:d.scrollLeft;
    var st = (window.scrollY)?window.scrollY:(w.scrollTop)?w.scrollTop:d.scrollTop;
    var wW1 = (window.innerHeight && window.scrollMaxY)?d.scrollWidth:(d.scrollHeight > d.offsetHeight)?d.scrollWidth:(w && w.scrollHeight > w.offsetHeight)?w.scrollWidth:d.offsetWidth;
    var wH1 = (window.innerHeight && window.scrollMaxY)?d.scrollHeight:(d.scrollHeight > d.offsetHeight)?d.scrollHeight:(w && w.scrollHeight > w.offsetHeight)?w.scrollHeight:d.offsetHeight;
    var wW2 = (self.innerHeight)?self.innerWidth:(w && w.clientHeight)?w.clientWidth:d.clientWidth; var pW = (wW1 < wW2)?wW2:wW1;
    var wH2 = (self.innerHeight)?self.innerHeight:(w && w.clientHeight)?w.clientHeight:d.clientHeight; var pH = (wH1 < wH2)?wH2:wH1;
    pW = ($.browser.msie)?pW:Math.max(w.scrollWidth, w.clientWidth, d.scrollWidth, d.offsetWidth);
    pH = ($.browser.msie)?pH:Math.max(w.scrollHeight, w.clientHeight, d.scrollHeight, d.offsetHeight);
    if (window.opera){ tww = (d.scrollWidth==d.clientWidth)?w.clientWidth:tww; twh = (d.scrollHeight==d.clientHeight)?w.clientHeight:twh;}
    return {
        winWidth:tww,
        winHeight: twh,
        winScrollLeft: sl,
        winScrollTop: st,
        pageWidth: pW,
        pageHeight: pH
    }
}

function addCookie(sName, sValue, dtDays) {
    var dtExpires = new Date(), dtExpiryDate = '';

    dtExpires.setTime(dtExpires.getTime() + dtDays*24*3600*1000);
    dtExpiryDate = dtExpires.toGMTString();

    document.cookie = sName + '=' + sValue + '; expires=' + dtExpiryDate + '; path=' + '/';
}

function findCookie(sName) {
    var i = 0, nStartPosition = 0, nEndPosition = 0, sCookieString = document.cookie;

    while (i <= sCookieString.length) {
        nStartPosition = i;
        nEndPosition = nStartPosition + sName.length;

        if (sCookieString.substring(nStartPosition, nEndPosition) == sName && sCookieString.substr(nEndPosition + 1, 1) != '=') {
            nStartPosition = nEndPosition + 1;
            nEndPosition = document.cookie.indexOf(";",nStartPosition);

            if (nEndPosition < nStartPosition)
                nEndPosition = document.cookie.length;

            return document.cookie.substring(nStartPosition, nEndPosition);
            break;
        }
        i++;
    }
    return "";
}

function get_url(url) {
    window.location.href = url;
}

// аналог PADL
function pad(n, width, z) {
    z = z || '0';
    n = n + '';
    return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

// всплывающие окна
function displayBlocks(blockId1, blockId2, blockId3, lback) {

    lModalExist = false;

    if (lback) {
        if ($('#back-wrapper').css('display') == 'none') {
            $('#back-wrapper').fadeIn(100);
        } else
            $('#back-wrapper').fadeOut(100);
    }

    if (blockId1 == '#myway-info') {
        if ($(blockId1).css('display') == 'none') {
            $('#myway-info-button').css('display', 'none');
            $('#myway-info').addClass('transition', true).css('left', '9px').fadeIn(400, function() {
                lMyWayInfo = true;
                lModalExist = true;
                $('#myway-info').removeClass('transition');
            });
        } else {
            $('#myway-info').addClass('transition', true).css('left', '-360px').fadeOut(400, function() {
                //sleep(10);
                $('#myway-info-button').fadeIn(5);
            });
            lMyWayInfo = false;
            lModalExist = false;
        }

    } else if (blockId1 != '') {
        if ($(blockId1).css('display') == 'none') {
            $(blockId1).fadeIn(20, function() { lModalExist = true; });
        } else {
            $(blockId1).fadeOut(10);
            lModalExist = false;
        }
    }

    if (blockId2 != '') {
        if ($(blockId2).css('display') == 'none') {
            $(blockId2).fadeIn(20, function() { lModalExist = true; });
        } else {
            $(blockId2).fadeOut(10);
            lModalExist = false;
        }
    }

    if (blockId3 != '') {
        if ($(blockId3).css('display') == 'none') {
            $(blockId3).fadeIn(20, function() { lModalExist = true; });
        } else {
            $(blockId3).fadeOut(10);
            lModalExist = false;
        }
    }
}

// пауза
function sleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds){
            break;
        }
    }
}

// - End of JavaScript - -->
