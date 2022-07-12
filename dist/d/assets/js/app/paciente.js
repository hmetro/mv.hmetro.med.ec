// App Teleconsulta v1.0.1
// 18-06-2021
// @martinxapps
// mchang@hmetro.med.ec
// by Hospital Metropolitano CONCLINA C.A Quito EC.

"use strict";

class Viewer {
    verArchivoPDF() {
        $(".button-menu-left").hide();
        $(".button-menu-right").hide();
        $('.button-menu-left-reload').hide();
        $('.button-print-lab').hide();
        $('.button-download-lab').hide();




        var c = document.body.Consola;

        $("#docPDF").html(
            template($("#t_PDF").html(), {
                urlDoc: c.dataLaboratorio.urlDoc
            })
        );

        $(".doc-loader").show();
        $(".doc-content").hide();
        $(".doc-control").hide();
        $('.button-download-lab').show();
        $('.downloadLink').attr('href', c.dataLaboratorio.urlDoc);






        var consola = document.body.Consola;

        // If absolute URL from the remote server is provided, configure the CORS
        // Loaded via <script> tag, create shortcut to access PDF.js exports.
        var pdfjsLib = window["pdfjs-dist/build/pdf"];
        // The workerSrc property shall be specified.
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            "//metrovirtual.hospitalmetropolitano.org/assets/dashforge/lib/pdf.js/build/pdf.worker.js";
        var pdfDoc = null,
            pageNum = 1,
            pageRendering = false,
            pageNumPending = null,
            scale = 1.25,
            canvas = document.getElementById("render-pdf"),
            ctx = canvas.getContext("2d");
        /**
         * Get page info from document, resize canvas accordingly, and render page.
         * @param num Page number.
         */
        function renderPage(num) {
            pageRendering = true;
            // Using promise to fetch the page
            pdfDoc.getPage(num).then(function(page) {
                var viewport = page.getViewport({
                    scale: scale,
                });
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                canvas.style.width = $("#docPDF").width() + "px";
                // Render PDF page into canvas context
                var renderContext = {
                    canvasContext: ctx,
                    viewport: viewport,
                };
                var renderTask = page.render(renderContext);
                // Wait for rendering to finish
                renderTask.promise.then(function() {
                    pageRendering = false;
                    if (pageNumPending !== null) {

                        // New page rendering is pending
                        renderPage(pageNumPending);
                        pageNumPending = null;

                    } else {

                        //  var ui = new UI();
                        // ui.renderOptionsArchivoPDF();


                        $('.preloader').fadeOut('slow', function() {
                            $(this).hide();
                        });


                        $('.btn-reset-detalle').show();


                        $(".reset-detalle").unbind("click");
                        $(".reset-detalle").click(function(e) {
                            e.preventDefault();

                            $('#dataPaciente').removeClass('d-none');
                            $('#verDoc').addClass('d-none');

                            $('.btn-reset-detalle').hide();
                            $('.btn-next-detalle').hide();
                            $('.btn-prev-detalle').hide();
                            $('.button-download-lab').hide();


                            $(".button-menu-left").show();
                            $(".button-menu-right").show();

                            $('.button-menu-left-reload').show();


                        });

                    }
                });
            });
            // Update page counters
            // document.getElementsByClassName('page_num').textContent = num;
            $(".page_num").text(num);
        }
        /**
         * If another page rendering in progress, waits until the rendering is
         * finised. Otherwise, executes rendering immediately.
         */
        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }
        /**
         * Displays previous page.
         */
        function onPrevPage() {
            if (pageNum <= 1) {
                return;
            }
            pageNum--;
            queueRenderPage(pageNum);
        }
        /*
                                                                                                                                                                                                                                                                              document.getElementsByClassName('prev').onclick = function(e) {
                                                                                                                                                                                                                                                                                  e.preventDefault();
                                                                                                                                                                                                                                                                                  onPrevPage();
                                                                                                                                                                                                                                                                              };
                                                                                                                                                                                                                                                                              */
        $(".prev").click(function(e) {
            $("[data-toggle='tooltip']").tooltip("hide");
            e.preventDefault();
            onPrevPage();
        });
        /**
         * Displays next page.
         */
        function onNextPage() {
            if (pageNum >= pdfDoc.numPages) {
                return;
            }
            pageNum++;
            queueRenderPage(pageNum);
        }
        /*
                                                                                                                                                                                                                                                                              document.getElementsByClassName('next').onclick = function(e) {
                                                                                                                                                                                                                                                                                  e.preventDefault();
                                                                                                                                                                                                                                                                                  onNextPage();
                                                                                                                                                                                                                                                                              };
                                                                                                                                                                                                                                                                              */
        $(".next").click(function(e) {
            $("[data-toggle='tooltip']").tooltip("hide");
            e.preventDefault();
            onNextPage();
        });
        /**
         * Asynchronously downloads PDF.
         *
         */

        pdfjsLib
            .getDocument({
                url: consola.dataLaboratorio.urlDoc,
            })
            .promise.then(function(pdfDoc_) {
                pdfDoc = pdfDoc_;
                $(".page_count").text(pdfDoc.numPages);

                // Initial/first page rendering
                setTimeout(function() {
                    $(".doc-loader").hide();
                    $(".doc-content").show();
                    $(".doc-control").show();
                    renderPage(pageNum);
                }, 100);

                if (pdfDoc.numPages > 1) {
                    $('.btn-next-detalle').show();
                    $('.btn-prev-detalle').show();
                }
            });

    }



}

// Clase Imagen
class Imagen {
    constructor(params = {}) {
        ({
            urlDoc: this.urlDoc = null,
            resultados: this.resultados = []
        } = params);
    }

    loadResultados() {

        var c = document.body.Consola;

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
        var table = $("#table-imagen").DataTable({
            "ajax": {

                url: c.urlApi + "/resultados-imagen?numeroHistoriaClinica=" + c.dataPaciente.PK_NHCL,
                dataSrc: "data",
                serverSide: true,
            },
            processing: true,
            serverSide: true,
            responsive: false,
            dom: 't',
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
                        return full.NUM;


                    },
                    visible: false,
                    aTargets: [0],
                    orderable: true
                },
                {
                    mRender: function(data, type, full) {

                        var c = document.body.Consola;
                        if (full.PROCEDURE_START == moment().format('DD-MM-YYYY')) {
                            full.nuevo = "display:block;";
                        } else {
                            full.nuevo = "display:none;";
                        }
                        return template(c.dataImagen.templateResultado(), full);

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

                document.body.Consola.dataImagen.resultados = settings.json.data;

                $("table").css("border-color", "transparent").css('margin-bottom', '50px');

                $('.imprimirImgRes').unbind("click");
                $('.imprimirImgRes').click(function(e) {
                    var url_informe = this.id;
                    var $this = $(this);
                    $this.attr('disabled', 'disabled');
                    $this.html('Procesando...');
                    _verImgResultado(url_informe).then(function(res) {
                        if (res) {
                            $this.removeAttr('disabled');
                            $this.html('<i class="icofont-print"></i> Imprimir');
                            printJS(url_informe);
                        } else {
                            $this.removeAttr('disabled');
                            $this.html('<i class="icofont-print"></i> Imprimir');
                            alert('Error en generación de informe. Comunícate con Soporte.');
                        }
                    });
                });




            },

            panging: false,

        });



        return table;





    }

    templateResultado() {
        return $('#t_verImgResultado').html();
    }

    verResultado() {

        $('#dataPaciente').addClass('d-none');
        $('#verDoc').removeClass('d-none');

        var viewer = new Viewer();
        viewer.verArchivoPDF();
    }

}

// Clase Laboratorio
class Laboratorio {
    constructor(params = {}) {
        ({
            urlDoc: this.urlDoc = null,
            resultados: this.resultados = []
        } = params);
    }

    loadResultados() {

        var c = document.body.Consola;

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
        var table = $("#table-laboratorio").DataTable({
            "ajax": {

                url: c.urlApi + "/resultados?numeroHistoriaClinica=" + c.dataPaciente.FK_PERSONA,
                dataSrc: "data",
                serverSide: true,
            },
            processing: true,
            serverSide: true,
            responsive: false,
            dom: 't',
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
                        return full.NUM;


                    },
                    visible: false,
                    aTargets: [0],
                    orderable: true
                },
                {
                    mRender: function(data, type, full) {
                        var c = document.body.Consola;
                        if (full.FECHA == moment().format('DD-MM-YYYY')) {
                            full.nuevo = "display:block;";
                        } else {
                            full.nuevo = "display:none;";
                        }

                        return template(c.dataLaboratorio.templateResultado(), full);

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

                document.body.Consola.dataLaboratorio.resultados = settings.json.data;

                $('.preloader').fadeOut('slow', function() {
                    $(this).hide();
                });

                $(".table-loader-mis-resultados").addClass("d-none");
                $(".table-content-mis-resultados").removeClass("d-none");
                $("table").css("border-color", "transparent").css('margin-bottom', '50px');

                $('.verRes').unbind("click");
                $('.verRes').click(function(e) {
                    e.preventDefault();
                    $('.preloader').fadeOut('slow', function() {
                        $(this).show();
                    });
                    _verResultado(this.id).then(function(json) {
                        if (json.status) {
                            document.body.Consola.dataLaboratorio.urlDoc = json.url;
                            document.body.Consola.dataLaboratorio.verResultado();
                        } else {
                            $('.preloader').hide();
                            $('#dataPaciente').removeClass('d-none');
                            $('#verDoc').addClass('d-none');
                            $('.btn-reset-detalle').hide();
                            $('.btn-next-detalle').hide();
                            $('.btn-prev-detalle').hide();
                            $('.button-download-lab').hide();
                            $(".button-menu-left").show();
                            $(".button-menu-right").show();
                            $('.button-menu-left-reload').show();
                            alert('Resultado no disponible.');
                        }
                    });
                });

                $('.imprimirRes').unbind("click");
                $('.imprimirRes').click(function(e) {
                    e.preventDefault();
                    var $this = $(this);
                    $this.attr('disabled', 'disabled');
                    $this.html('Procesando...');
                    _verResultado(this.id).then(function(json) {
                        if (json.status) {
                            $this.removeAttr('disabled');
                            $this.html('<i class="icofont-print"></i> Imprimir');
                            printJS(json.url);
                        } else {
                            $this.removeAttr('disabled');
                            $this.html('<i class="icofont-print"></i> Imprimir');
                            alert('Resultado no disponible.');
                        }
                    });
                });


            },

            panging: false,

        });



        return table;





    }

    templateResultado() {
        return $('#t_verResultado').html();
    }

    verResultado() {

        $('#dataPaciente').addClass('d-none');
        $('#verDoc').removeClass('d-none');


        var viewer = new Viewer();
        viewer.verArchivoPDF();
    }

}

// Clase Consola Paciente
class Consola {
    constructor(params = {}) {
        ({
            urlApi: this.urlApi = "https://api.hospitalmetropolitano.org/t/v1",
            dataPaciente: this.dataPaciente = [],
            dataLaboratorio: this.dataLaboratorio = new Laboratorio(),
            dataImagen: this.dataImagen = new Imagen(),
        } = params);
        document.body.Consola = this;
    }

    loadPaciente() {

        var params = getGET();
        var urlString = '';

        if (params !== undefined && params.nhc !== undefined) {


            _buscarPaciente(params.nhc + '01').then(function(json) {

                localStorage.setItem('paciente', JSON.stringify(json.data));

                var paciente = JSON.parse(localStorage.getItem('paciente'));

                console.log('paciente', paciente);

                document.body.Consola.dataPaciente = paciente;

                var dataPaciente = document.body.Consola.dataPaciente;
                $('.nombresPaciente').html(dataPaciente.NOMBRES + ' ' + dataPaciente.APELLIDOS);
                $('.nhcPaciente').html('NHC: ' + dataPaciente.PK_NHCL);

                document.body.Consola.dataLaboratorio.loadResultados();
                document.body.Consola.dataImagen.loadResultados();


                $(".renderImagen").on("click", function() {
                    $('header').addClass('d-none');
                    $('#dataPaciente').addClass('d-none');
                    $('#zfpImg').removeClass('d-none');
                    $('.button-menu-left').hide();
                    $('.button-menu-right').hide();
                    $('.button-menu-left-reload').hide();
                    $('.imagen-button-menu').show();
                });

                $(".renderUIImagen").on("click", function() {
                    $('.informesImagen').hide();
                    $('.opcImagen').hide();
                    $('.opcImagen').show();
                });

                $(".verInformesImagen").on("click", function() {
                    $('.informesImagen').hide();
                    $('.opcImagen').hide();
                    $('.informesImagen').show();
                });

                $(".imagen-button-menu").on("click", function() {
                    $('header').removeClass('d-none');
                    $('#dataPaciente').removeClass('d-none');
                    $('#zfpImg').addClass('d-none');
                    $('.button-menu-left-reload').show();
                    $('.button-menu-left').show();
                    $('.button-menu-right').show();
                    $('.imagen-button-menu').hide();
                    $('.renderUIImagen').click();

                });


                var imgView = "//imagen.hmetro.med.ec/zfp?Lights=on&mode=proxy#view&pid=" + dataPaciente.PK_NHCL + "&un=WEBAPI&pw=lEcfvZxzlXTsfimMMonmVZZ15IqsgEcdV%2forI8EUrLY%3d";

                $('#zfpImg').attr('src', imgView);


            });

        } else {

            var paciente = JSON.parse(localStorage.getItem('paciente'));

            console.log('paciente', paciente);

            document.body.Consola.dataPaciente = paciente;

            var dataPaciente = document.body.Consola.dataPaciente;
            $('.nombresPaciente').html(dataPaciente.NOMBRES + ' ' + dataPaciente.APELLIDOS);
            $('.nhcPaciente').html('NHC: ' + dataPaciente.PK_NHCL);

            document.body.Consola.dataLaboratorio.loadResultados();
            document.body.Consola.dataImagen.loadResultados();


            $(".renderImagen").on("click", function() {
                $('header').addClass('d-none');
                $('#dataPaciente').addClass('d-none');
                $('#zfpImg').removeClass('d-none');
                $('.button-menu-left').hide();
                $('.button-menu-right').hide();
                $('.button-menu-left-reload').hide();
                $('.imagen-button-menu').show();
            });

            $(".verInformesImagen").on("click", function() {
                $('.informesImagen').hide();
                $('.opcImagen').hide();
                $('.informesImagen').show();
            });


            $(".renderUIImagen").on("click", function() {
                $('.informesImagen').hide();
                $('.opcImagen').hide();
                $('.opcImagen').show();
            });

            $(".imagen-button-menu").on("click", function() {
                $('header').removeClass('d-none');
                $('#dataPaciente').removeClass('d-none');
                $('#zfpImg').addClass('d-none');
                $('.button-menu-left-reload').show();
                $('.button-menu-left').show();
                $('.button-menu-right').show();
                $('.imagen-button-menu').hide();
                $('.renderUIImagen').click();

            });


            var imgView = "//imagen.hmetro.med.ec/zfp?Lights=on&mode=proxy#view&pid=" + dataPaciente.PK_NHCL + "&un=WEBAPI&pw=lEcfvZxzlXTsfimMMonmVZZ15IqsgEcdV%2forI8EUrLY%3d";

            $('#zfpImg').attr('src', imgView);


        }




    }



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
    c.loadPaciente();
};

function getGET() {
    // capturamos la url
    var loc = document.location.href;
    // si existe el interrogante
    if (loc.indexOf("?") > 0) {
        // cogemos la parte de la url que hay despues del interrogante
        var getString = loc.split("?")[1];
        // obtenemos un array con cada clave=valor
        var GET = getString.split("&");
        var get = {};
        // recorremos todo el array de valores
        for (var i = 0, l = GET.length; i < l; i++) {
            var tmp = GET[i].split("=");
            get[tmp[0]] = unescape(decodeURI(tmp[1]));
        }
        return get;
    }
}


function _buscarPaciente(nhc) {
    var c = document.body.Consola;
    var formData = new FormData();
    formData.append('tipoBusqueda', 'nhc');
    formData.append('pte', nhc);
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

function _loadResultados() {
    var c = document.body.Consola;

    return fetch(c.urlApi + '/resultados', {
            method: "GET",
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

function _verResultado(url) {

    return fetch(url, {
            method: "GET",
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


function _verImgResultado(url) {

    return fetch(url, {
            method: "GET",
            contentType: false,
            processData: false,
        })
        .then(function(response) {

            if (response.status === 200) {
                return true;
            } else {
                return false;
            }

        }).catch(function(err) {
            console.error(err);
        });
}