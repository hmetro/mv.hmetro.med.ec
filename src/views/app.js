import Auth from '../models/auth';
import Loader from './loader';


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

export default App;