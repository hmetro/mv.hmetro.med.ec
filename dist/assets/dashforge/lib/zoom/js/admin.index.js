(function() {
    console.log('checkSystemRequirements');
    console.log(JSON.stringify(ZoomMtg.checkSystemRequirements()));
    // it's option if you want to change the WebSDK dependency link resources. setZoomJSLib must be run at first
    // if (!china) ZoomMtg.setZoomJSLib('https://source.zoom.us/1.7.8/lib', '/av'); // CDN version default
    // else ZoomMtg.setZoomJSLib('https://jssdk.zoomus.cn/1.7.8/lib', '/av'); // china cdn option 
    // ZoomMtg.setZoomJSLib('http://localhost:9999/node_modules/@zoomus/websdk/dist/lib', '/av'); // Local version default, Angular Project change to use cdn version
    ZoomMtg.preLoadWasm();
    ZoomMtg.prepareJssdk();
    var API_KEY = _API_KEY_;
    /**
     * NEVER PUT YOUR ACTUAL API SECRET IN CLIENT SIDE CODE, THIS IS JUST FOR QUICK PROTOTYPING
     * The below generateSignature should be done server side as not to expose your api secret in public
     * You can find an eaxmple in here: https://marketplace.zoom.us/docs/sdk/native-sdks/web/essential/signature
     */
    var API_SECRET = _API_SECRET_;
    testTool = window.testTool;
    document.getElementById('display_name').value = "CDN" + ZoomMtg.getJSSDKVersion()[0] + testTool.detectOS() + "#" + testTool.getBrowserInfo();
    $.i18n.reload('es-ES');
    ZoomMtg.reRender({
        lang: 'es-ES'
    });
    document.getElementById('join_meeting').addEventListener('click', function(e) {
        e.preventDefault();
        if (!this.form.checkValidity()) {
            alert("Enter Name and Meeting Number");
            return false;
        }
        var meetConfig = {
            apiKey: API_KEY,
            apiSecret: API_SECRET,
            meetingNumber: API_ID_CALL,
            userName: _NOMBRE_MEDICO_,
            passWord: '123456',
            leaveUrl: "https://zoom.us",
            role: parseInt(1, 10)
        };
        testTool.setCookie("meeting_number", meetConfig.meetingNumber);
        testTool.setCookie("meeting_pwd", meetConfig.passWord);
        var signature = ZoomMtg.generateSignature({
            meetingNumber: meetConfig.meetingNumber,
            apiKey: meetConfig.apiKey,
            apiSecret: meetConfig.apiSecret,
            role: meetConfig.role,
            success: function(res) {
                console.log(res.result);
            }
        });
        ZoomMtg.init({
            leaveUrl: 'http://www.zoom.us',
            success: function() {
                ZoomMtg.join({
                    meetingNumber: meetConfig.meetingNumber,
                    userName: meetConfig.userName,
                    signature: signature,
                    apiKey: meetConfig.apiKey,
                    passWord: meetConfig.passWord,
                    success: function(res) {
                        setTimeout(function() {
                            $('#join_meeting').css('display', 'none');
                            $($('#wc-footer').children('div').get(0)).css('display', 'block');
                            $($('#wc-footer').children('div').get(2)).css('display', 'none');
                            $('[aria-label="open the participants list pane"]').css('display', 'none');
                            $('[aria-label="open the chat pane"]').css('display', 'none');
                            $('[aria-label="more security options"]').css('display', 'none');
                            $('[aria-label="Grabar"]').css('display', 'none');
                            $('.security-option-menu dropup btn-group').css('display', 'none');
                            $('.more-button').css('display', 'none');
                            $('.meeting-info-icon__icon').css('display', 'none');
                        }, 9);
                        /*
                        // Custom para UI de Zoom
                        $($('#wc-footer').children('div').get(1).children('div').get(0)).css('display', 'none');
                        $($('#wc-footer').children('div').get(1).children('button').get(0)).css('display', 'none');
                        $($('#wc-footer').children('div').get(1).children('button').get(2)).css('display', 'none');
                        $($('#wc-footer').children('div').get(1).children('button').get(3)).css('display', 'none');
                        $($('#wc-footer').children('div').get(1).children('button').get(4)).css('display', 'none');
                        console.log('join meeting success');
                        */
                    },
                    error: function(res) {
                        console.log(res);
                    }
                });
            },
            error: function(res) {
                console.log(res);
            }
        });
    });
})();