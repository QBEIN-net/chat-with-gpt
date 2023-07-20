var qbChat = document.getElementById('qbChat');
var qbChatOpened = document.getElementById('qbChatOpened');
var qbChatClose = document.getElementById('qbChatClose');
var qcgRespTemplate = '#qcg-chat-template .qbChat-gpt__msg.qbChat-gpt__msg-resp';
var qcgReqTemplate = '#qcg-chat-template .qbChat-gpt__msg.qbChat-gpt__msg-user';
var qcgWorkingTemplate = '#qcg-chat-template .qbChat-gpt__msg.qbChat-gpt__msg-working';
var qcgPrompts = [];
var qcgDt = new Date();
var qcgToday = String(qcgDt.getFullYear()) + String(qcgDt.getMonth()) + String(qcgDt.getDate());
var qcgAnswerField = jQuery('#qcg-answer');
var qcgWaitingAnswer = false
var qcgText = jQuery('#qcg-prompt');
var qcgBtn = jQuery('#qcg-prompt-ask');
var qcgChatOpened = jQuery('.qbChat-gpt-opened');
var _qcgOverlay = document.getElementById('qbChatOpened');
var _qcgInner = document.getElementById('qcg-answer');
var _qcgClientY = null; // remember Y position on touch start

window.visualViewport.addEventListener("resize", qcgResizeHandler);

document.addEventListener('DOMContentLoaded', function () {
    qcgBtn.on('click', function () {
        if (!qcgBtn.prop('disabled') && qcgText.val().length > 0 && !qcgWaitingAnswer) {
            qcgAsk(qcgText.val());
        }
    });
    qcgText.on('keydown', function (event) {
        if (event.key === 'Enter') {
            if (event.shiftKey) {
                if (!qcgBtn.prop('disabled') && qcgText.val().length > 0 && !qcgWaitingAnswer) {
                    qcgAsk(qcgText.val());
                }
            }
        }
    });
    qcgText.on('keyup', function () {
        qcgBtn.prop('disabled', qcgText.val().length <= 0);
    });

    jQuery('.qbChat-gpt__footer, .qbChat-gpt__footer *').on('touchmove', function (event) {
        event.preventDefault();
    })

    jQuery('#qbChatEmail form').on('submit', e => {
        var email = document.getElementById('email');
        var errorEmail = jQuery('.qbChat-gpt__emailForm-errorMsg');
        e.preventDefault();
        if (email.value) {
            var regexMatch = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email.value.trim());
            if (regexMatch) {
                errorEmail.html('').hide();
                qcgSendEmail(email.value.trim());
            } else {
                e.preventDefault();
                errorEmail.html('Incorrect email address').show();
            }
        } else {
            e.preventDefault();
            errorEmail.html('Incorrect email address').show();
        }
    });

    qcgScrollDown();

    qbChat.addEventListener('click', qcgOpenChat);
    qbChatClose.addEventListener('click', qcgCloseChat);

    _qcgOverlay.addEventListener('touchstart', function (event) {
        if (event.targetTouches.length === 1) {
            // detect single touch
            _qcgClientY = event.targetTouches[0].clientY;
        }
    }, false);

    _qcgOverlay.addEventListener('touchmove', function (event) {
        if (event.targetTouches.length === 1) {
            // detect single touch
            qcgDisableRubberBand(event);
        }
    }, false);
});

function qcgScrollDown() {
    var block = document.getElementById('qcg-answer');
    block.scrollTop = block.scrollHeight;
}

function qcgBlurHandler() {
    qcgChatOpened.css('bottom', '0');
}

function qcgResizeHandler() {
    var height = window.visualViewport.height;
    var viewport = window.visualViewport;

    if (!/iPhone|iPad|iPod/.test(window.navigator.userAgent)) {
        height = viewport.height;
    }
    qcgChatOpened.css('bottom', `${height - viewport.height}px`);
}


function qcgOpenChat() {
    qbChatOpened.classList.add('qbChat-gpt__show');
    qbChatOpened.classList.remove('qbChat-gpt__hide');
    qcgScrollDown();
}

function qcgCloseChat() {
    qbChatOpened.classList.remove('qbChat-gpt__show');
    qbChatOpened.classList.add('qbChat-gpt__hide');
}

function qcgDisableRubberBand(event) {
    var clientY = event.targetTouches[0].clientY - _qcgClientY;

    if (_qcgOverlay.scrollTop === 0 && clientY > 0 && _qcgInner.scrollTop === 0) {
        // element is at the top of its scroll
        event.preventDefault();

    }

    if (qcgIsOverlayTotallyScrolled() && qcgIsInnerTotallyScrolled() && clientY < 0) {
        //element is at the top of its scroll
        event.preventDefault();
    }
}

function qcgIsOverlayTotallyScrolled() {
    return _qcgOverlay.scrollHeight - _qcgOverlay.scrollTop - _qcgInner.scrollHeight <= _qcgOverlay.clientHeight;
}

function qcgIsInnerTotallyScrolled() {
    return _qcgInner.scrollHeight - _qcgInner.scrollTop === _qcgInner.clientHeight;
}



function qcgSendEmail(email) {
    var formData = new FormData();
    formData.append('action', 'qcg_send_email');
    formData.append('email', email);

    jQuery.ajax({
        url: qcgmyajax.url,
        method: "POST",
        processData: false,
        contentType: false,
        data: formData,
        beforeSend: function () {
        },
        success: function (resp) {
            qcgAnswerField.append(qcgPrepResp(resp));
            sessionStorage.setItem('qcg-mail-sent', qcgToday);
            jQuery('#qbChatEmail').toggleClass('shown');
            qcgScrollDown();
        }
    });
}

function qcgAsk(data) {
    var formData = new FormData();
    formData.append('action', 'qcg_ask_request');

    qcgPrompts.push({
        role: 'user',
        content: data
    });

    formData.append('q', JSON.stringify(qcgPrompts));


    jQuery.ajax({
        url: qcgmyajax.url,
        method: "POST",
        processData: false,
        contentType: false,
        data: formData,
        beforeSend: function () {
            qcgWaitingAnswer = true;
            qcgAnswerField.append(qcgPrepReq(data));
            qcgText.val('');
            qcgBtn.prop('disabled', true);
            qcgAnswerField.append(qcgPrepWorking());
            qcgScrollDown();
        },
        success: function (resp) {
            resp = JSON.parse(resp)
            qcgAnswerField.find('.qbChat-gpt__msg-working').remove();
            switch (resp.status) {
                case 'qcgInvalidApiKey':
                    qcgAnswerField.append(qcgPrepResp(resp.data));

                    break;
                case 'qcgLimitPerUserPerDate':
                    var isSent = sessionStorage.getItem('qcg-mail-sent');
                    if (isSent !== qcgToday) {
                        jQuery('#qbChatEmail').toggleClass('shown');
                    }
                    qcgAnswerField.append(qcgPrepResp(resp.data));

                    break;
                default:
                    qcgAnswerField.append(qcgPrepResp(resp.data));
                    qcgPrompts.push({
                        role: 'system',
                        content: resp.data
                    });

                    break;
            }
            qcgWaitingAnswer = false;
            qcgBtn.prop('disabled', false);
            qcgScrollDown();
        },
        error: function () {
            qcgWaitingAnswer = false;
        }
    });
}

function qcgZeroPad(num, places) {
    return String(num).padStart(places, '0')
}

function qcgPrepReq(data) {
    var clone = jQuery(qcgReqTemplate).clone();
    clone.find('p').html(data.replace(/\r?\n/g, '<br />'))
    var cqcgDt = new Date()
    clone.find('.qbChat-gpt__msg-time span').html(qcgZeroPad(cqcgDt.getHours(), 2) + ':' + qcgZeroPad(cqcgDt.getMinutes(), 2));
    return clone;
}

function qcgPrepResp(data) {
    var clone = jQuery(qcgRespTemplate).clone();
    clone.find('p').html(data.replace(/\r?\n/g, '<br />'))
    var cqcgDt = new Date()
    clone.find('.qbChat-gpt__msg-time span').html(qcgZeroPad(cqcgDt.getHours(), 2) + ':' + qcgZeroPad(cqcgDt.getMinutes(), 2));
    return clone;
}

function qcgPrepWorking() {
    var clone = jQuery(qcgWorkingTemplate).clone();
    var cqcgDt = new Date()
    clone.find('.qbChat-gpt__msg-time span').html(qcgZeroPad(cqcgDt.getHours(), 2) + ':' + qcgZeroPad(cqcgDt.getMinutes(), 2));
    return clone;
}