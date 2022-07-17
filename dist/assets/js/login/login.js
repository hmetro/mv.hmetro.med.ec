function _ini_login() {
    var error_icon = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ',
        success_icon = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> ',
        process_icon = '<span class="fa fa-spinner fa-spin" aria-hidden="true"></span> ';
    $('#ajax_login').removeClass('bg-danger');
    $('#ajax_login').removeClass('bg-warning');
    $('#ajax_login').addClass('bg-warning');
    $("#ajax_login").html(process_icon + 'Iniciando sesión, por favor espere...');
    $('#ajax_login').removeClass('hide');
    $('#login').html(process_icon + 'Conectando...');
    $('#login').attr('disabled', true);
    $.ajax({
        type: "POST",
        url: "api/login",
        data: $('#login_form').serialize(),
        success: function(json) {
            if (json.success == 1) {
                $('#ajax_login').html(success_icon + json.message);
                $("#ajax_login").removeClass('bg-warning');
                $("#ajax_login").addClass('bg-primary');
                setTimeout(function() {
                    window.location = urlhome;
                }, 1000);
            } else {
                $('#ajax_login').html(error_icon + json.message);
                $("#ajax_login").removeClass('bg-warning');
                $("#ajax_login").addClass('bg-danger');
                $('#login').removeAttr('disabled');
                $('#login').html('Entrar');
            }
        },
        error: function() {
            window.alert('#Request Error!');
        }
    });
};
if (document.getElementById('login')) {
    document.getElementById('login').onclick = function(e) {
        e.preventDefault();
        _ini_login();
    };
}
if (document.getElementById('login_form')) {
    document.getElementById('login_form').onkeypress = function(e) {
        if (!e) e = window.event;
        var keyCode = e.keyCode || e.which;
        if (keyCode == '13') {
            _ini_login();
            return false;
        }
    };
}

function getGET() {
    // capturamos la url
    var loc = document.location.href;
    // si existe el interrogante
    if (loc.indexOf('?') > 0) {
        // cogemos la parte de la url que hay despues del interrogante
        var getString = loc.split('?')[1];
        // obtenemos un array con cada clave=valor
        var GET = getString.split('&');
        var get = {};
        // recorremos todo el array de valores
        for (var i = 0, l = GET.length; i < l; i++) {
            var tmp = GET[i].split('=');
            get[tmp[0]] = unescape(decodeURI(tmp[1]));
        }
        return get;
    }
}