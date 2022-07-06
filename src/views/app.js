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
            setTimeout(function () { App.isAuth() }, 300)
        ];
    },
};

export default App;