import App from '../views/app';
import _Error_ from '../views/error';




const Auth = {
    username: "",
    password: "",
    messageError: "",
    statusHide: " d-none",
    statusError: "warning",
    imputDisabled: false,

    setUsername: (value) => {
        Auth.username = value
    },
    setPassword: (value) => {
        Auth.password = value
    },
    canSubmit: () => {
        return Auth.username !== "" && Auth.password !== "";
    },
    setError: (message) => {
        Auth.statusHide = "";
        Auth.statusError = "danger";
        Auth.messageError = message;

    },
    setSuccess: (message) => {
        Auth.statusHide = "";
        Auth.statusError = "success";
        Auth.messageError = message;

    },
    setProcess: () => {
        Auth.statusHide = "";
        Auth.statusError = "warning";
        Auth.messageError = 'Procesando...';
    },
    login: () => {
        Auth.imputDisabled = true;
        Auth.setProcess();
        return m.request({
                method: "POST",
                url: "https://api.hospitalmetropolitano.org/t/v1/auth",
                body: {
                    user: Auth.username,
                    pass: Auth.password
                }
            })
            .then(function(data) {

                if (data.status) {
                    window.localStorage.accessToken = data.jwt;
                    Auth.setSuccess('Bienvenido');
                    setTimeout(function() {
                        Auth.imputDisabled = false;
                        Auth.statusHide = "d-none";
                        Auth.statusError = "warning";
                        Auth.messageError = "";
                        Auth.username = "";
                        Auth.password = "";
                        App.isAuth()
                    }, 900);
                } else {
                    Auth.imputDisabled = false;
                    Auth.statusHide = "d-none";
                    Auth.statusError = "warning";
                    Auth.messageError = "";
                    Auth.setError(data.message);
                }

            }).catch(function(error) {
                Auth.imputDisabled = false;
                Auth.statusHide = "d-none";
                Auth.statusError = "warning";
                Auth.messageError = "";
                Auth.username = "";
                Auth.password = "";
                Auth.setError(_Error_.httpError);
            });
    },
    isLogin: () => {
        return true;
    },
};




export default Auth;