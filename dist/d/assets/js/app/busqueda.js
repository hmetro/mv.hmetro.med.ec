// App Teleconsulta v1.0.1
// 18-06-2021
// @martinxapps
// mchang@hmetro.med.ec
// by Hospital Metropolitano CONCLINA C.A Quito EC.

"use strict";



// Clase Consola Paciente
class Consola {
    constructor(params = {}) {
        ({
            urlApi: this.urlApi = "https://api.hospitalmetropolitano.org/t/v1",
            dataPaciente: this.dataPaciente = [],
            data: this.data = [],
        } = params);
        document.body.Consola = this;
    }

    loadBusqueda() {
        $("#buscarPte").unbind("click");
        $("#buscarPte").click(function(e) {
            var c = document.body.Consola;
            var $this = $(this);
            var loadingText = "Procesando...";
            if ($(this).html() !== loadingText) {
                $this.html(loadingText);
                $this.attr('disabled', 'disabled');
                c.buscarPaciente();
            }
        });

        //  $("#b_pte").attr('onkeyup', 'mayus(this);');


        document.onkeypress = function(e) {
            if (!e) e = window.event;
            var keyCode = e.keyCode || e.which;
            if (keyCode == "13") {
                var c = document.body.Consola;
                c.buscarPaciente();
                return false;
            }
        };

    }

    buscarPaciente() {

        $('.preloader').fadeOut('slow', function() {
            $(this).show();
        });


        $('#alertsBusqueda').html('');

        _buscarPaciente().then(function(json) {

            $('.preloader').fadeOut('slow', function() {
                $(this).hide();
            });

            $('#buscarPte').html('Buscar').removeAttr('disabled');


            if (json.status) {

                var c = document.body.Consola;

                if (json.tipoBusqueda == 'pte') {
                    c.data = json.data;
                    c.renderListaPacientes(json.data);
                } else {
                    c.data = [json.data];
                    c.renderListaPacientes(c.data);
                }

            } else {
                $('#alertsBusqueda').html(json.message);
            }

        });
    }

    renderListaPacientes(pacientes = []) {


        var c = document.body.Consola;

        if ($("#table-pacientes").html().length > 0) {
            $('#table-pacientes').DataTable().clear().destroy();
        }


        // MOMMENT
        moment.lang("es", {
            months: "Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre".split(
                "_"
            ),
            monthsShort: "Enero._Feb._Mar_Abr._May_Jun_Jul._Ago_Sept._Oct._Nov._Dec.".split(
                "_"
            ),
            weekdays: "Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado".split(
                "_"
            ),
            weekdaysShort: "Dom._Lun._Mar._Mier._Jue._Vier._Sab.".split("_"),
            weekdaysMin: "Do_Lu_Ma_Mi_Ju_Vi_Sa".split("_"),
        });


        $.fn.dataTable.ext.errMode = "none";
        var table = $("#table-pacientes").DataTable({
            data: pacientes,
            responsive: false,
            dom: "ftp",
            language: {
                searchPlaceholder: "Buscar...",
                sSearch: "",
                lengthMenu: "Mostrar _MENU_ registros por página",
                sProcessing: "Procesando...",
                sZeroRecords: "Todavía no tienes resultados disponibles.",
                sEmptyTable: "Ningún dato disponible en esta tabla",
                sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
                sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
                sInfoPostFix: "",
                sUrl: "",
                sInfoThousands: ",",
                sLoadingRecords: "Cargando...",
                oPaginate: {
                    sFirst: "Primero",
                    sLast: "Último",
                    sNext: "Siguiente",
                    sPrevious: "Anterior",
                },
                oAria: {
                    sSortAscending: ": Activar para ordenar la columna de manera ascendente",
                    sSortDescending: ": Activar para ordenar la columna de manera descendente",
                },
            },
            cache: false,
            columns: false,
            aoColumnDefs: [

                {
                    mRender: function(data, type, full) {
                        return 0;


                    },
                    visible: false,
                    aTargets: [0],
                    orderable: true
                },
                {
                    mRender: function(data, type, full) {
                        var c = document.body.Consola;



                        return template($('#cardPte').html(), full);
                    },
                    visible: true,
                    aTargets: [1],
                    orderable: false
                },

            ],
            fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $(nRow).find("td").css("border-color", 'transparent').css('padding', 0);
                $(nRow).css('background-color', "transparent");


            },
            drawCallback: function(settings) {


                $('.preloader').fadeOut('slow', function() {
                    $(this).hide();
                });

                $("table").css("border-color", "transparent").css('margin-bottom', '50px');
                $('.paginate_button').addClass('capsul fz-poppins active text-white radius-pill');

                $("#table-pacientes_filter").find("input").css("width", '-webkit-fill-available');
                $("#table-pacientes_filter").find("input").css("width", '100%').css('font-size', 'initial').focus();



                $(".verPte").unbind("click");
                $(".verPte").click(function(e) {
                    var c = document.body.Consola;
                    c.setPaciente(this.id);
                });


            },


        });



        return table;


    }

    renderPaciente(paciente = []) {
        $('#responseData').html('');
        $('#responseData').html(template($('#cardPte').html(), paciente));
        $(".verPte").unbind("click");
        $(".verPte").click(function(e) {
            var c = document.body.Consola;
            c.setPaciente(this.id);
        });
    }

    setPaciente(idPaciente) {
        var c = document.body.Consola;
        var dataPaciente = [];

        $.each(c.data, function(index, value) {
            if (c.data[index].PK_NHCL == idPaciente) {
                dataPaciente = c.data[index]; // Para consultar el valor del Pte.
            }
        });


        if (dataPaciente.length !== 0) {
            // Put the object into storage
            localStorage.setItem('paciente', JSON.stringify(dataPaciente));
            window.location.assign('https://beta.metrovirtual.hospitalmetropolitano.org/touch/paciente/');
        } else {
            alert('Error Paciente no puede configurarse.');
        }
    }

}


function mayus(e) {
    e.value = e.value.toUpperCase();
}

function sleepTemp(ms) {
    return function(x) {
        return new Promise((resolve) => setTimeout(() => resolve(x), ms));
    };
}


function template(templateid, data) {
    return templateid.replace(
        /%(\w*)%/g, // or /{(\w*)}/g for "{this} instead of %this%"
        function(m, key) {
            return data.hasOwnProperty(key) ? data[key] : "";
        }
    );
}


// Init Load
window.onload = function() {
    var c = new Consola();
    c.loadBusqueda();
};

function _buscarPaciente() {
    var c = document.body.Consola;
    var formData = new FormData($('#busquedaPaciente')[0]);
    return fetch(c.urlApi + '/buscar-paciente', {
            method: "POST",
            body: formData,
            contentType: false,
            processData: false,
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(_json) {
            return _json;
        })
        .catch(function(err) {
            console.error(err);
        });
}