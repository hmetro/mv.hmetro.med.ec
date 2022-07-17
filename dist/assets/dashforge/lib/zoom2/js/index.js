window.addEventListener('DOMContentLoaded', function (event) {
    console.log('DOM fully loaded and parsed');
    websdkready();
});

function websdkready() {
    var testTool = window.testTool;
    if (testTool.isMobileDevice()) {
        vConsole = new VConsole();
    }
    console.log("checkSystemRequirements");
    console.log(JSON.stringify(ZoomMtg.checkSystemRequirements()));

    // it's option if you want to change the WebSDK dependency link resources. setZoomJSLib must be run at first
    // if (!china) ZoomMtg.setZoomJSLib('https://source.zoom.us/1.8.6/lib', '/av'); // CDN version default
    // else ZoomMtg.setZoomJSLib('https://jssdk.zoomus.cn/1.8.6/lib', '/av'); // china cdn option
    // ZoomMtg.setZoomJSLib('http://localhost:9999/node_modules/@zoomus/websdk/dist/lib', '/av'); // Local version default, Angular Project change to use cdn version
    ZoomMtg.preLoadWasm(); // pre download wasm file to save time.

    var API_KEY = _API_KEY_;

    /**
     * NEVER PUT YOUR ACTUAL API SECRET IN CLIENT SIDE CODE, THIS IS JUST FOR QUICK PROTOTYPING
     * The below generateSignature should be done server side as not to expose your api secret in public
     * You can find an eaxmple in here: https://marketplace.zoom.us/docs/sdk/native-sdks/web/essential/signature
     */
    var API_SECRET = _API_SECRET_;

    var meetingConfig = testTool.getMeetingConfig();

    meetingConfig.mn = API_ID_CALL;

    meetingConfig.name = _NOMBRE_MEDICO_;

    meetingConfig.pwd = '123456';

    console.log(meetingConfig);

    if (!meetingConfig.mn || !meetingConfig.name) {
        alert("Meeting number or username is empty");
        return false;
    }


    testTool.setCookie("meeting_number", API_ID_CALL);
    testTool.setCookie("meeting_pwd", '123456');

    var signature = ZoomMtg.generateSignature({
        meetingNumber: API_ID_CALL,
        apiKey: API_KEY,
        apiSecret: API_SECRET,
        role: parseInt(1, 10),
        success: function (res) {
            console.log(res.result);
            meetingConfig.signature = res.result;
            meetingConfig.apiKey = API_KEY;
            var joinUrl = BUILD_URL + "meet/?" + testTool.serialize(meetingConfig);
            console.log(joinUrl);
            window.location.href = joinUrl
        },
    });



}