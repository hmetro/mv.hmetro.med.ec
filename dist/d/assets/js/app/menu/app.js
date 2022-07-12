// App Teleconsulta v1.0.1
// 18-06-2021
// @martinxapps
// mchang@hmetro.med.ec
// by Hospital Metropolitano CONCLINA C.A Quito EC.

"use strict";

// Clase Consola
class Consola {
    constructor(params = {}) {
        ({
            urlApi: this
                .urlApi = "https://api.metrovirtual.hospitalmetropolitano.org/v2",
            urlUpLis: this
                .urlUpLis = "https://api.hospitalmetropolitano.org/lis/v1",
            urlUpPtes: this
                .urlUpPtes = "https://api.hospitalmetropolitano.org/pacientes/v1",
            urlUpTC: this
                .urlUpTC = "https://api.hospitalmetropolitano.org/teleconsulta/v1",
            urlHMPacientes: this
                .urlHMPacientes = "https://api.metrovirtual.hospitalmetropolitano.org/hm/v1",
            modeConsola: this.modeConsola = null,
            rolConsola: this.rolConsola = null,
            templates: this.templates = [],
            pacientes: this.pacientes = [],
            misAtenciones: this.misAtenciones = [],
            nuevaAtencion: this.nuevaAtencion = [],
            buscarPacientes: this.buscarPacientes = [],
            citas: this.citas = [],
            formularios: this.formularios = [],
            buscarCitas: this.buscarCitas = [],
            fechasCitas: this.fechasCitas = [],
            agendaMedico: this.agendaMedico = [],
            dataZoom: this.dataZoom = [],
            dataPaciente: this.dataPaciente = [],
            dataAtencion: this.dataAtencion = [],
            diagnosticos: this.diagnosticos = [],
            examenesLab: this.examenesLab = [],
            examenesImg: this.examenesImg = [],
            dataCall: this.dataCall = [],
            nuevoPte: this.nuevoPte = [],
            options: this.options = []
        } = params);


        document.body.Consola = {
            urlApi: this.urlApi,
            urlUpLis: this.urlUpLis,
            urlUpPtes: this.urlUpPtes,
            urlUpTC: this.urlUpTC,
            urlHMPacientes: this.urlHMPacientes,
            modeConsola: this.modeConsola,
            rolConsola: this.rolConsola,
            templates: this.templates,
            pacientes: this.pacientes,
            misAtenciones: this.misAtenciones,
            nuevaAtencion: this.nuevaAtencion,
            buscarPacientes: this.buscarPacientes,
            citas: this.citas,
            formularios: this.formularios,
            buscarCitas: this.buscarCitas,
            fechasCitas: this.fechasCitas,
            agendaMedico: this.agendaMedico,
            dataPaciente: this.dataPaciente,
            dataZoom: this.dataZoom,
            dataAtencion: this.dataAtencion,
            diagnosticos: this.diagnosticos,
            examenesLab: this.examenesLab,
            examenesImg: this.examenesImg,
            dataCall: this.dataCall,
            nuevoPte: this.nuevoPte,
            options: this.options,

        };

    }

    init() {

        try {

            var ui = new UI();
            ui.setText('#textNetwork', 'Configurando tu perfil.');

            initConsola().then(function(json) {

                // Existe una llamada sin cerrar
                if (json.inCall && !json.idPacienteActivo) {

                    var consola = document.body.Consola;
                    consola.modeConsola = json.modeConsola;
                    consola.rolConsola = json.rolConsola;
                    consola.dataCall = json.dataCall;
                    consola.options = json;

                    document.body.Consola = consola;
                    var consola = document.body.Consola;

                    // Cargar templates de Consola y lanzar reconexion
                    var tps = new Tps(consola.templates);
                    tps.loadTpsReconect();


                } else if (json.inCall && json.idPacienteActivo) {

                    var consola = document.body.Consola;
                    consola.modeConsola = json.modeConsola;
                    consola.rolConsola = json.rolConsola;
                    consola.dataCall = json.dataCall;
                    consola.options = json;
                    document.body.Consola = consola;

                    var consola = document.body.Consola;
                    // Cargar templates de Consola y lanzar reconexion
                    var tps = new Tps(consola.templates);
                    tps.loadTpsReconectPresencial();


                } else {

                    var consola = document.body.Consola;
                    consola.modeConsola = json.modeConsola;
                    consola.rolConsola = json.rolConsola;
                    consola.options = json;
                    document.body.Consola = consola;

                    var consola = document.body.Consola;

                    // Cargar templates de Consola
                    var tps = new Tps(consola.templates);
                    tps.loadTps();
                }

            });


        } catch (error) {
            var ui = new UI();
            ui.reloadPageAction('No pudimos configurar tu perfil. *Aplicación bloqueada, espera un momento lo reintentamos automaticamente.\nError Code: XE002');
        }

    }

    network() {

        try {

            var ui = new UI();
            ui.init();
            ui.setText('#textNetwork', 'Verificando tus recursos de Internet.');

            // Validaciones de Red Internet

            //   if (navigator.connection == undefined || navigator.connection.effectiveType == "4g") {
            if (true) {


                var ui = new UI();
                ui.setText('#textNetwork', 'Hemos confirmado tus recursos de Internet con  éxito.');

                var consola = document.body.Consola;
                var tc = new Consola(consola);
                tc.init();

            } else {

                var ui = new UI();
                ui.setText('#textNetwork', 'No tienes los recursos de Internet necesarios. No podemos continuar.<br/>Una conexión estable mínimo de 15 Megas o 4g LTE es necesaria.');

            }

        } catch (e) {
            var ui = new UI();
            ui.reloadPageAction('No pudimos validar tu conexión a internet. *Aplicación bloqueada, espera un momento lo reintentamos automaticamente.\nError Code: XE001');
        }

    }


}

function sleepTemp(ms) {
    return function(x) {
        return new Promise((resolve) => setTimeout(() => resolve(x), ms));
    };
}

// Incia una consola Paciente
function initConsola() {
    var consola = document.body.Consola;

    var formData = new FormData();
    formData.append("C", 1);

    return fetch(consola.urlApi + "/consola", {
            headers: {
                Authorization: localStorage.accessToken,
            },
            method: "POST",
            body: formData,
            contentType: false,
            processData: false,
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(json) {

            console.log(json);
            return json;
        })
        .catch(function(err) {
            console.error(err);
        });
}


// Init Load
window.onload = function() {
    // Establecer nivel de Zoom
    document.body.style.zoom = "95%";
    // var tc = new Consola();
    // Verificar conexion
    // tc.network();

};

function _getProcessNoti() {

    return fetch('https://api.hospitalmetropolitano.org/lis/v1/task/informar/resultadosasc/micro', {
            method: "GET",
            contentType: false,
            processData: false,
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(_json) {
            console.log(_json);
            return _json;
        })
        .catch(function(err) {
            console.error(err);
        });
}

function _getProcessNotiAsc() {

    return fetch('https://api.hospitalmetropolitano.org/lis/v1/task/infodiferidoasc/resultados', {
            method: "GET",
            contentType: false,
            processData: false,
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(_json) {
            console.log(_json);
            return _json;
        })
        .catch(function(err) {
            console.error(err);
        });
}