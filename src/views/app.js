import Auth from '../models/auth';
import Loader from './loader';
import HeadPublic from '../views/layout/header-public';



const App = {
    title: "Metrovirtual v3.0.0",
    oninit: () => {
        document.title = "Cargando...";
    },
    oncreate: () => {
        document.title = "Bienvenido | " + App.title;

        var loc = window.location.href + '';
        if (loc.indexOf('http://') == 0) {
            window.location.href = loc.replace('http://', 'https://');
        }

        internalIp().then(function(ip) {
            HeadPublic.ip = ip;

            if (!ip.includes('172.16')) {
                alert("Ud debe conectarse a cualquier red del Hospital Metropolitano para continuar.")
                return m.route.set('/inicio');
            } else {
                if (!ip.includes('172.16') && !ip.includes('172.17')) {
                    alert("Ud debe conectarse a cualquier red del Hospital Metropolitano para continuar.")
                    return m.route.set('/inicio');
                }
            }


        })
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


export default App;