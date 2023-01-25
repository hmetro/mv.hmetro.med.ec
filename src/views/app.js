import Auth from '../models/auth';
import Loader from './loader';



const App = {
    title: "Metrovirtual v3.0.0",
    oninit: () => {
        document.title = "Cargando...";
    },
    oncreate: () => {
        document.title = "Bienvenido | " + App.title;

    },
    isAuth: () => {
        if (Auth.isLogin()) {
            return m.route.set('/inicio');
        } else {
            return m.route.set('/auth');
        }
    },
    view: () => {
        return [
            m(Loader),
            setTimeout(function() { App.isAuth() }, 300)
        ];
    },
};


const internalIp = async() => {
    if (!RTCPeerConnection) {
        throw new Error("Not supported.")
    }

    const peerConnection = new RTCPeerConnection({ iceServers: [] })

    peerConnection.createDataChannel('')
    peerConnection.createOffer(peerConnection.setLocalDescription.bind(peerConnection), () => {})

    peerConnection.addEventListener("icecandidateerror", (event) => {
        throw new Error(event.errorText)
    })

    return new Promise(async resolve => {
        peerConnection.addEventListener("icecandidate", async({ candidate }) => {
            peerConnection.close()

            if (candidate && candidate.candidate) {
                const result = candidate.candidate.split(" ")[4]
                if (result.endsWith(".local")) {
                    const inputDevices = await navigator.mediaDevices.enumerateDevices()
                    const inputDeviceTypes = inputDevices.map(({ kind }) => kind)

                    const constraints = {}

                    if (inputDeviceTypes.includes("audioinput")) {
                        constraints.audio = true
                    } else if (inputDeviceTypes.includes("videoinput")) {
                        constraints.video = true
                    } else {
                        throw new Error("An audio or video input device is required!")
                    }

                    const mediaStream = await navigator.mediaDevices.getUserMedia(constraints)
                    mediaStream.getTracks().forEach(track => track.stop())
                    resolve(internalIp())
                }
                resolve(result)
            }
        })
    })
}

function reloadScripts(toRefreshList /* list of js to be refresh */ , key /* change this key every time you want force a refresh */ ) {
    var scripts = document.getElementsByTagName('script');
    for (var i = 0; i < scripts.length; i++) {
        var aScript = scripts[i];
        for (var j = 0; j < toRefreshList.length; j++) {
            var toRefresh = toRefreshList[j];
            if (aScript.src && (aScript.src.indexOf(toRefresh) > -1)) {
                new_src = aScript.src.replace(toRefresh, toRefresh + '?k=' + key);
                // console.log('Force refresh on cached script files. From: ' + aScript.src + ' to ' + new_src)
                aScript.src = new_src;
            }
        }
    }
}


export default App;