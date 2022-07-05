import Auth from '../../models/auth';
import HeaderPrivate from '../layout/header-private';
import App from '../app';
import m from 'mithril';
import Loader from '../loader';


const VisorRis = {
    show: "",
    view: () => {
        return [
            m("div", [
                m("iframe", {
                    src: "https://imagen.hmetro.med.ec/zfp?Lights=on&mode=proxy#view&pid=" + Paciente.nhc + "&un=WEBAPI&pw=lEcfvZxzlXTsfimMMonmVZZ15IqsgEcdV%2forI8EUrLY%3d",
                    "style": {
                        "frameborder": "0",
                        "width": "100%",
                        "height": document.body.clientHeight - (document.body.clientHeight * 0.02) + "px"
                    }
                })
            ]),
            m("div.imagen-button-menu",
                m("button.btn.fadeInDown-slide.position-relative.animated.pl-4.pr-4.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2[type='button']", {
                    onclick: () => {
                        VisorRis.show = "";
                    },
                }, [
                    m("i.icofont-close"),
                    " Cerrar "
                ])
            )
        ]
    },

};


const Imagen = {
    data: [],
    detalle: [],
    error: "",
    showResultados: "d-none",
    showButtons: "",
    fetch: () => {
        Imagen.data = [];
        Imagen.error = "";

        m.request({
                method: "GET",
                url: "https://api.hospitalmetropolitano.org/t/v1/resultados-img/" + Paciente.nhc,
                headers: {
                    "Authorization": localStorage.accessToken,
                },
            })
            .then(function(result) {
                if (result.status && result.data.length !== 0) {
                    Imagen.data = result.data;
                } else {
                    Imagen.error = result.message;
                }
            })
            .catch(function(e) {
                Imagen.error = e.message;
            })
    },
    oninit: () => {

        Imagen.fetch();
    },
    view: () => {

        return Imagen.error ? [
            m(".tab-pane.fade[id='v-pills-imagen'][role='tabpanel']", [
                m("h4.m-text-2.",
                    m("i.icofont-file-image.mr-2"),
                    "Resultados de Imagen:"
                ),
                m("h6.text-light-dark.ff-roboto.pb-40.mb-0",
                    "Hospital Metropolitano"
                ),
                m(".alert.alert-danger[role='alert']",
                    Imagen.error
                )
            ]),
        ] : Imagen.data.length !== 0 ? [
            m(".tab-pane.fade[id='v-pills-imagen'][role='tabpanel']", [
                m("h4.m-text-2.",
                    m("i.icofont-file-image.mr-2"),
                    "Resultados de Imagen:"
                ),
                m("h6.text-light-dark.ff-roboto.pb-40.mb-0",
                    "Hospital Metropolitano"
                ),

                m("div." + Imagen.showButtons + ".row.p-1",
                    m("div.col-md-6",
                        m("div.single-service.type-1.radius-10.position-relative.service-wrapper.s-dp-10-60.m-mb-50.", {
                                onclick: () => {
                                    VisorRis.show = "d-none";
                                },
                                "style": { "cursor": "pointer" }
                            },
                            m("h4.text-dark2.mb-3.position-relative.pt-2",
                                "Ver Exámenes"
                            )
                        )
                    ),
                    m("div.col-md-6",
                        m("div.single-service.type-1.radius-10.position-relative.service-wrapper.s-dp-10-60.m-mb-50.", {
                                onclick: () => {
                                    Imagen.showResultados = "";
                                    Imagen.showButtons = "d-none";
                                    MenuBoton.update = "RX";
                                },
                                "style": { "cursor": "pointer" }
                            },
                            m("h4.text-dark2.mb-3.position-relative.pt-2",
                                "Ver Informes"
                            )
                        )
                    )
                ),
                m("div." + Imagen.showResultados + ".row.p-1",
                    m("h6.mb-5.ml-2", [
                        "Resultados disponibles desde Enero, 2019.",
                        m("a.ml-2", {
                            href: "/",
                            onclick: (e) => {
                                e.preventDefault();
                                Imagen.showResultados = "d-none";
                                Imagen.showButtons = "";
                            },
                        }, [
                            m("br"),
                            "<< Regresar"
                        ]),

                    ]),
                    m("div.table-content.col-12.pd-r-0.pd-l-0.pd-b-20.",
                        m("table.table.table-sm[width='100%']", { "style": { "width": "100%", "border-color": "transparent", "margin-bottom": "50px" } }, [
                            m("tbody", [
                                Imagen.data.map(function(_v, _i, _contentData) {
                                    return [
                                        m("tr[role='row']", { "style": { "background-color": "transparent" } },
                                            m("td", { "style": { "border-color": "transparent", "padding": "0px" } },
                                                m("div.row.bg-white.radius-5.p-2.article-tags", [
                                                    m("div.col-lg-6.p-2", [
                                                        m("div", { "style": { "display": "block" } },
                                                            m("span", { "style": { "color": "red", "display": "none" } },
                                                                " Nuevo Resultado "
                                                            )
                                                        ),
                                                        m("span.d-block",
                                                            "Estudio: " + _v.ESTUDIO
                                                        ),
                                                        m("span.d-block",
                                                            " Fecha: " + _v.FECHA
                                                        ),
                                                    ]),
                                                    m("div.col-lg-6.p-2.text-xl-right", [
                                                        m("button.capsul.fz-poppins.text-default.radius-pill.active", {
                                                            onclick: () => {
                                                                window.open(_v.URL_INFORME)
                                                            },
                                                            "style": { "cursor": "pointer" }
                                                        }, [
                                                            m("i.icofont-download"),
                                                            " Descargar "
                                                        ])
                                                    ])
                                                ])
                                            )
                                        )
                                    ]
                                })
                            ])
                        ])
                    ),
                )
            ]),
        ] : [
            m(".tab-pane.fade[id='v-pills-imagen'][role='tabpanel']", [
                m("h4.m-text-2.",
                    m("i.icofont-file-image.mr-2"),
                    "Resultados de Imagen:"
                ),
                m("h6.text-light-dark.ff-roboto.pb-40.mb-0",
                    "Hospital Metropolitano"
                ),
                m("div.text-center", [
                    m("div.loader-content",
                        m("span.icon-section-wave.d-inline-block.text-active.mt-3.", )
                    )
                ])
            ]),
        ]


    },

};

const verDocPDF = {
    url: "",
    show: "",

    loadDcoument: (_url) => {



        verDocPDF.url = _url;

        var canvas = document.getElementById("render-pdf");
        console.log(canvas)
        setTimeout(function() {



            $(".doc-loader").show();
            $(".doc-content").hide();
            $(".doc-control").hide();





            // If absolute URL from the remote server is provided, configure the CORS
            // Loaded via <script> tag, create shortcut to access PDF.js exports.
            var pdfjsLib = window["pdfjs-dist/build/pdf"];
            // The workerSrc property shall be specified.
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                "assets/dashforge/lib/pdf.js/build/pdf.worker.js";
            var pdfDoc = null,
                pageNum = 1,
                pageRendering = false,
                pageNumPending = null,
                scale = 1.25,
                canvas = document.getElementById("render-pdf"),
                ctx = canvas.getContext("2d")
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

                            $('.preloader').fadeOut('slow', function() {
                                $(this).hide();
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
                e.preventDefault();
                onNextPage();
            });
            /**
             * Asynchronously downloads PDF.
             *
             */

            pdfjsLib
                .getDocument({
                    url: verDocPDF.url,
                })
                .promise.then(function(pdfDoc_) {
                    pdfDoc = pdfDoc_;
                    $(".page_count").text(pdfDoc.numPages);

                    // Initial/first page rendering
                    setTimeout(function() {
                        $(".doc-loader").hide();
                        $(".doc-content").show();
                        $(".doc-control").show();
                        if (pdfDoc.numPages == 1) {
                            $('.next').hide();
                            $('.prev').hide();
                        }

                        renderPage(pageNum);
                    }, 100);

                    if (pdfDoc.numPages > 1) {
                        $('.next').show();
                        $('.prev').show();
                    }
                });


        }, 900);





    },

    view: () => {

        if (verDocPDF.url.length !== 0) {
            return [
                m("div.col-lg-12.text-center[id='docPDF']", [
                    m("div.doc-control.row.mb-0.p-0.w-100", [

                        m("div.row.col-12.d-block.tx-14.tx-semibold", [
                            " Página: ",
                            m("span.page_num", ),
                            " / ",
                            m("span.page_count", )
                        ]),
                        m("div.row.col-12.d-block.social-icon.circle-link", [
                            m("a.prev.text-primary.rounded-circle.bg-white.s-dp-1-3-15[href='#']", {
                                    title: "Anterior",
                                    onclick: (e) => {
                                        e.preventDefault();
                                    }
                                },
                                m("i.icofont-circled-left")
                            ),
                            m("a.next.text-primary.rounded-circle.bg-white.s-dp-1-3-15[href='#']", {
                                    title: "Siguiente",
                                    onclick: (e) => {
                                        e.preventDefault();
                                    }
                                },
                                m("i.icofont-circled-right")



                            ),
                            m("a.text-primary.rounded-circle.bg-white.s-dp-1-3-15[target='_blank']", {
                                    href: verDocPDF.url,
                                    title: "Descargar"
                                },
                                m("i.icofont-download")
                            ),
                            m("a.text-primary.rounded-circle.bg-white.s-dp-1-3-15[href='#']", {
                                    onclick: (e) => {
                                        e.preventDefault();
                                        verDocPDF.show = "";
                                        verDocPDF.url = "";

                                    },
                                    title: "Cerrar"
                                },
                                m("i.icofont-close-circled")
                            ),

                        ])
                    ]),
                    m("div.doc-loader.row.col-12", { "style": { "display": "none" } },
                        m("div..col-12.pd-5",
                            m("div.preloader-inner",
                                m("div.loader-content",
                                    m("span.icon-section-wave.d-inline-block.text-active.mt-3.", ),
                                )
                            ),
                        )
                    ),
                    m("div.doc-content.row.col-12.mt-5.pd-0.", { "style": { "display": "flex" } },
                        m("div.d-flex.justify-content-start.pd-0.mg-0.w-100",
                            m("canvas[id='render-pdf']", {})
                        )
                    ),

                ])
            ]
        }




    }
}

const Laboratorio = {
    data: [],
    detalle: [],
    error: "",
    showFor: "",
    loader: false,
    verResultado: (url) => {
        m.request({
                method: "GET",
                url: url,
                headers: {
                    "Authorization": localStorage.accessToken,
                },
            })
            .then(function(result) {
                Laboratorio.loader = false;
                if (result.status !== undefined && result.status) {
                    verDocPDF.loadDcoument(result.url);

                } else {
                    Laboratorio.error = "Resultado no disponible.";
                    setTimeout(function() { Laboratorio.error = ""; }, 5000);
                }

            }).catch(function(e) {
                alert("Resultado no disponible.");
                Laboratorio.loader = false;
                verDocPDF.show = "";
                Laboratorio.error = "";
            });

    },
    fetchResultado: (url) => {
        m.request({
                method: "GET",
                url: url,
                headers: {
                    "Authorization": localStorage.accessToken,
                },
            })
            .then(function(result) {
                Laboratorio.loader = false;
                if (result.status !== undefined && result.status) {
                    window.open(result.url);
                } else {
                    Laboratorio.error = "Resultado no disponible.";
                    setTimeout(function() { Laboratorio.error = ""; }, 5000);
                }

            })

    },
    fetch: () => {
        Laboratorio.data = [];
        Laboratorio.error = "";
        verDocPDF.url = "";

        m.request({
                method: "GET",
                url: "https://api.hospitalmetropolitano.org/t/v1/resultados-laboratorio/" + Paciente.nhc,
                headers: {
                    "Authorization": localStorage.accessToken,
                },
            })
            .then(function(result) {
                if (result.status && result.data.length !== 0) {
                    Laboratorio.data = result.data;
                } else {
                    Laboratorio.error = result.message;
                }

            })
            .catch(function(e) {
                Laboratorio.error = e.message;
            })
    },
    oninit: () => {
        Laboratorio.fetch();
    },
    view: () => {

        return Laboratorio.error ? [
            m(".tab-pane.fade[id='v-pills-lab'][role='tabpanel']", [
                m("h4.m-text-2.",
                    m("i.icofont-laboratory.mr-2"),
                    "Resultados de Laboratorio:"
                ),
                m("h6.text-light-dark.ff-roboto.pb-40.mb-0",
                    "Hospital Metropolitano"
                ),
                m(".alert.alert-danger[role='alert']",
                    Laboratorio.error
                )
            ]),
        ] : (Laboratorio.data.length !== 0 && !Laboratorio.loader) ? [
            m(".tab-pane.fade[id='v-pills-lab'][role='tabpanel']", [
                m("h4.m-text-2.",
                    m("i.icofont-laboratory.mr-2"),
                    "Resultados de Laboratorio:"
                ),
                m("h6.text-light-dark.ff-roboto.pb-40.mb-0",
                    "Hospital Metropolitano"
                ),

                m("h6.mb-5.d-flex", [
                    "Resultados disponibles desde Enero, 2019.",


                ]),

                m("div.row.p-1",
                    m("div.table-content.col-12.pd-r-0.pd-l-0.pd-b-20.w-100." + verDocPDF.show,
                        m("table.table.table-sm", { "style": { "width": "100%", "border-color": "transparent", "margin-bottom": "50px" } }, [
                            m("tbody", [
                                Laboratorio.data.map(function(_v, _i, _contentData) {
                                    return [
                                        m("tr[role='row']", { "style": { "background-color": "transparent" } },
                                            m("td", { "style": { "border-color": "transparent", "padding": "0px" } },
                                                m("div.row.bg-white.radius-5.p-2.article-tags", [
                                                    m("div.col-lg-6.p-2", [
                                                        m("div", { "style": { "display": "block" } },
                                                            m("span", { "style": { "color": "red", "display": "none" } },
                                                                " Nuevo Resultado "
                                                            )
                                                        ),
                                                        m("span.d-block",
                                                            "Origen: " + _v.ORIGEN + " Médico: " + _v.MEDICO.replaceAll(":", "")
                                                        ),
                                                        m("span.d-block",
                                                            " Fecha y Hora: " + _v.FECHA
                                                        ),
                                                    ]),
                                                    m("div.col-lg-6.p-2.text-xl-right", [




                                                        m("button.capsul.fz-poppins.text-default.radius-pill.active", {
                                                            onclick: () => {
                                                                Laboratorio.loader = true;
                                                                verDocPDF.show = "d-none";
                                                                Laboratorio.verResultado(_v.urlPdf);

                                                            },
                                                            "style": { "cursor": "pointer" }
                                                        }, [
                                                            m("i.icofont-eye-alt"),
                                                            " Ver"

                                                        ]),
                                                        m("button.capsul.fz-poppins.text-default.radius-pill.active", {
                                                            onclick: () => {
                                                                Laboratorio.loader = true;
                                                                Laboratorio.fetchResultado(_v.urlPdf);
                                                            },
                                                            "style": { "cursor": "pointer" }
                                                        }, [
                                                            m("i.icofont-download"),
                                                            " Descargar"

                                                        ]),
                                                    ])
                                                ])
                                            )
                                        )
                                    ]
                                })
                            ])
                        ])
                    ),
                    m(verDocPDF)
                )
            ]),
        ] : [
            m(".tab-pane.fade[id='v-pills-lab'][role='tabpanel']", [
                m("h4.m-text-2.",
                    m("i.icofont-laboratory.mr-2"),
                    "Resultados de Laboratorio:"
                ),
                m("h6.text-light-dark.ff-roboto.pb-40.mb-0",
                    "Hospital Metropolitano"
                ),
                m("div.text-center", [
                    m("div.loader-content",
                        m("span.icon-section-wave.d-inline-block.text-active.mt-3.", )
                    )
                ])
            ]),
        ]


    },

};

const FOR005 = {
    secs: [],
    parseDoc: (_data) => {

        return Object.keys(_data.data).map(function(_v, _i, _contentData) {
            FOR005.secs.push(_data.data[_v])
        })

    },
    oninit: () => {
        FOR005.secs = [];
        return Formulario.data.map(function(_v, _i, _contentData) {
            FOR005.parseDoc(Formulario.data[_i])
        })



    },
    view: () => {

        let nombres = "";
        let apellidos_paciente = "";
        let sexo = "";
        let nhcl = "";
        let numero_admision = "";
        let edad = "";
        let edad_paciente = "";
        let fecha_admision = "";
        let ubicacion = "";
        let fecha_alta = "";
        let medico_tratante = "";
        let identificacion = "";
        let evolucion_medica_texto = "";
        let prescripciones_texto = "";



        if (FOR005.secs.length !== 0) {
            return FOR005.secs.map(function(_v, _i, _contentData) {



                if (_v.name == 'nombres') {

                    nombres = _v.answer;

                }


                if (_v.name == 'apellidos_paciente') {

                    apellidos_paciente = _v.answer;

                }

                if (_v.name == 'sexo de paciente') {

                    sexo = _v.answer;

                }

                if (_v.name == 'nhcl') {

                    nhcl = _v.answer;

                }

                if (_v.name == 'numero_admision') {

                    numero_admision = _v.answer;

                }

                if (_v.name == 'edad') {

                    edad = _v.answer;

                }

                if (_v.name == 'edad_paciente') {

                    edad_paciente = _v.answer;

                }

                if (_v.name == 'identificacion_paciente') {

                    identificacion = _v.answer;

                }

                if (_v.name == 'fecha_admision') {

                    fecha_admision = _v.answer;

                }

                if (_v.name == 'ubicacion_atencion') {

                    ubicacion = _v.answer;

                }


                if (_v.name == 'fecha_alta') {

                    fecha_alta = (_v.answer == null) ? '' : _v.answer;

                }

                if (_v.name == 'medico_tratante') {

                    medico_tratante = _v.answer;

                }

                if (_v.name == 'prescripciones_texto') {

                    prescripciones_texto = _v.answer;

                }

                if (_v.name == 'evolucion_medica_texto') {

                    evolucion_medica_texto = _v.answer;

                }

                if (_v.name == 'Logotipo_archivo') {


                    return m("table.table.table-bordered.mb-60.mt-4.p-2.w-100", {
                        "style": {
                            "zoom": Formulario.zoom,
                        }
                    }, [
                        m("thead", [
                            m("tr",
                                m("th[colspan='12'][scope='col']", [
                                    m("i.tx-light.fas.fa-file-alt.mg-r-2."),
                                    m("div.p-0", " SNS - MSP / HCU-form.005 / 2008 "),


                                ])
                            ),
                            m("tr", [
                                m("th[colspan='10'][scope='col']",
                                    m("div.m-0.p-0",
                                        m("img", {
                                            width: "100rem",
                                            src: "data:image/png;base64," + _v.answer
                                        })
                                    )
                                ),
                                m("th[colspan='2'][scope='col']",
                                    m("div.m-0.p-0",
                                        "HIS MV"

                                    )
                                )

                            ])
                        ]),
                        m("tbody", [
                            m("tr", [
                                m("th[colspan='3'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.tx-bold.text-center.",
                                        "ESTABLECIMIENTO"
                                    )
                                ),
                                m("th[colspan='3'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.tx-bold.text-center.",
                                        "NOMBRE"
                                    )
                                ),
                                m("th[colspan='3'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.tx-bold.text-center.",
                                        "APELLIDO"
                                    )
                                ),
                                m("th[colspan='1'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.tx-bold.text-center.",
                                        "SEXO (M-F)"
                                    )
                                ),
                                m("th[colspan='1][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.tx-bold.text-center.",
                                        "NHCL."
                                    )
                                ),
                                m("th[colspan='1'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.tx-bold.text-center.",
                                        "ADM."
                                    )
                                )
                            ]),
                            m("tr", [
                                m("th.text-center[colspan='3'][scope='row']",
                                    m("div.m-0.p-0.text-center",
                                        "HOSPITAL METROPOLITANO"
                                    )
                                ),
                                m("td.text-center[colspan='3']",
                                    m("div.m-0.p-0.text-center",
                                        nombres
                                    )
                                ),
                                m("td.text-center[colspan='3']",
                                    m("div.m-0.p-0.text-center",
                                        apellidos_paciente
                                    )
                                ),
                                m("td.text-center[colspan='1']",
                                    m("div.m-0.p-0.text-center",
                                        sexo
                                    )
                                ),
                                m("td.text-center[colspan='1']",
                                    m("div.m-0.p-0.text-center",
                                        nhcl
                                    )
                                ),
                                m("td.text-center[colspan='1']",
                                    m("div.m-0.p-0.text-center",
                                        numero_admision
                                    )
                                )
                            ]),
                            m("tr", [
                                m("th[colspan='1'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.m-0.p-0.tx-bold.text-center.",
                                        "EDAD"
                                    )
                                ),
                                m("th[colspan='1'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.m-0.p-0.tx-bold.text-center.",
                                        "IDENTIFICACION"
                                    )
                                ),
                                m("th[colspan='1'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.m-0.p-0.tx-bold.text-center.",
                                        "FECHA ADMISION"
                                    )
                                ),
                                m("th[colspan='1'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.m-0.p-0.tx-bold.text-center.",
                                        "FECHA ALTA"
                                    )
                                ),
                                m("th[colspan='4'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.m-0.p-0.tx-bold.text-center.",
                                        "UBICACION"
                                    )
                                ),
                                m("th[colspan='4'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.m-0.p-0.tx-bold.text-center.",
                                        "MEDICO TRATANTE"
                                    )
                                )
                            ]),
                            m("tr", [
                                m("td.text-center[colspan='1'][scope='row']",
                                    m("div.m-0.p-0.text-center",
                                        edad || edad_paciente
                                    )
                                ),
                                m("td.text-center[colspan='1']",
                                    m("div.m-0.p-0.text-center",
                                        identificacion
                                    )
                                ),
                                m("td.text-center[colspan='1']",
                                    m("div.m-0.p-0.text-center",
                                        fecha_admision
                                    )
                                ),
                                m("td.text-center[colspan='1']",
                                    m("div.m-0.p-0.text-center",
                                        fecha_admision
                                    )
                                ),
                                m("td.text-center[colspan='4']",
                                    m("div.m-0.p-0.text-center",
                                        ubicacion
                                    )
                                ),
                                m("td.text-center[colspan='4']",
                                    m("div.m-0.p-0.text-center",
                                        medico_tratante
                                    )
                                )
                            ]),
                            m("tr", [
                                m("th[colspan='6'][scope='row']", { "style": { "padding": "0", "background-color": "#eef9c8" } },
                                    m("div.m-0.p-0.tx-bold.text-center.",
                                        "1.- EVOLUCIÓN"
                                    )
                                ),
                                m("th[colspan='5'][scope='row']", { "style": { "padding": "0", "background-color": "#eef9c8" } },
                                    m("div.m-0.p-0.tx-bold.text-center.",
                                        "2.- PRESCRIPCIONES"
                                    )
                                ),
                                m("th[colspan='1'][scope='row']", { "style": { "padding": "0", "background-color": "#eef9c8" } },
                                    m("div.m-0.p-0.tx-bold.text-center.", [
                                            "FIRMAR AL PIE DE",
                                            m("br"),
                                            "CADA PRESCRIPCIÓN"
                                        ]

                                    )
                                ),

                            ]),
                            m("tr", [
                                m("th[colspan='1'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.m-0.p-0.tx-bold.text-center.", [
                                        "FECHA",
                                        m("br"),
                                        "día/mes/año"
                                    ])
                                ),
                                m("th[colspan='1'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.m-0.p-0.tx-bold.text-center.",
                                        "HORA"
                                    )
                                ),
                                m("th[colspan='4'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.m-0.p-0.tx-bold.text-center.",
                                        "NOTAS DE EVOLUCIÓN"
                                    )
                                ),
                                m("th[colspan='4'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.m-0.p-0.tx-bold.text-center.", [
                                            "FARMACOTERAPIA E INDICACIONES",
                                            m("br"),
                                            "(PARA ENFERMERÍA Y OTRO PERSONAL)"

                                        ]

                                    )
                                ),
                                m("th[colspan='2'][scope='row']", { "style": { "padding": "0", "background-color": "#edfbf5" } },
                                    m("div.m-0.p-0.tx-bold.text-center.", [
                                            "ADMINISTR.",
                                            m("br"),
                                            "FÁRMACOS INSUMOS"

                                        ]

                                    )
                                ),

                            ]),
                            m("tr", [
                                m("td[colspan='6'][scope='row']", { "style": { "padding": "0", "width": "50%" } },
                                    m("div.m-0.p-2.tx-bold.text-justify",
                                        (evolucion_medica_texto !== null && evolucion_medica_texto.length !== 0) ? m.trust(evolucion_medica_texto.replace(/(\r\n|\r|\n)/g, "<br/>")) : ""


                                    )
                                ),
                                m("td[colspan='6'][scope='row']", { "style": { "padding": "0", "width": "50%" } },
                                    m("div.m-0.p-2.text-justify",
                                        (prescripciones_texto !== null && prescripciones_texto.length !== 0) ? m.trust(prescripciones_texto.replace(/(\r\n|\r|\n)/g, "<br/>")) : ""

                                    )
                                ),

                            ]),

                        ])
                    ])

                }


            })
        }






    }

};

const Formulario = {
    zoom: 0.5,
    adm: 1,
    nhc: 1,
    data: [],
    error: "",
    fetch: () => {
        Formulario.data = [];
        Formulario.error = "";
        m.request({
                method: "GET",
                url: "https://api.hospitalmetropolitano.org/t/v1/formulario?nhcl=" + Formulario.nhc + "&adm=" + Formulario.adm,

                headers: {
                    "Authorization": localStorage.accessToken,
                },
            })
            .then(function(result) {
                if (result.length !== 0) {
                    Formulario.data = result;
                } else {
                    Formulario.error = "El documento solicitado no esta disponible.";
                }

            })
            .catch(function(e) {
                Formulario.error = e.message;
            })
    },
    oninit: () => {
        Formulario.fetch();
    },
    view: () => {

        return Formulario.error ? [
            m(".alert.alert-danger[role='alert']",
                Formulario.error
            ),
        ] : Formulario.data.length !== 0 ? [

            m(FOR005)

        ] : [
            m("div.text-center", [
                m("div.loader-content",
                    m("span.icon-section-wave.d-inline-block.text-active.mt-10.mb-10.", )
                )
            ])
        ]

    },
}

const Evoluciones = {
    data: [],
    detalle: [],
    error: "",
    showFor: "",
    fetch: () => {
        Evoluciones.data = [];
        Evoluciones.error = "";
        m.request({
                method: "POST",
                url: "https://api.hospitalmetropolitano.org/t/v1/ev-paciente-emergencia",
                body: {
                    numeroHistoriaClinica: Paciente.nhc
                },
                headers: {
                    "Authorization": localStorage.accessToken,
                },
            })
            .then(function(result) {
                if (result.status) {
                    Evoluciones.data = result.data;
                    Formulario.adm = Evoluciones.data[0].ADM;
                    Formulario.nhc = Evoluciones.data[0].NHCL;
                    Formulario.fetch();
                } else {
                    Evoluciones.error = result.message;
                }

            })
            .catch(function(e) {
                Evoluciones.error = e.message;
            })
    },
    oninit: () => {

        Evoluciones.fetch();
    },
    view: () => {


        return Evoluciones.error ? [
            m(".tab-pane.fade[id='v-pills-ev'][role='tabpanel']", [
                m("h4.m-text-2.",
                    m("i.icofont-prescription.mr-2"),

                    "Evoluciones y Prescripciones:"
                ),
                m("h6.text-light-dark.ff-roboto.pb-40.mb-0",
                    "Hospital Metropolitano"
                ),
                m(".alert.alert-danger[role='alert']",
                    Evoluciones.error
                )
            ]),
        ] : Evoluciones.data.length !== 0 ? [
            m(".tab-pane.fade[id='v-pills-ev'][role='tabpanel']", [
                m("h4.m-text-2.",
                    m("i.icofont-prescription.mr-2"),

                    "Evoluciones y Prescripciones:"
                ),
                m("h6.text-light-dark.ff-roboto.pb-40.mb-0",
                    "Hospital Metropolitano"
                ),
                m("h6.mb-5.d-flex", [
                    "Última información disponible. HIS MV.",
                ]),
                m("div.row.p-1",
                    m("div.col-12.bg-white.pd-r-0.pd-l-0.pd-b-20.",
                        m(Formulario),
                    ),

                )
            ]),
        ] : [
            m(".tab-pane.fade[id='v-pills-ev'][role='tabpanel']", [
                m("h4.m-text-2.",
                    m("i.icofont-prescription.mr-2"),

                    "Evoluciones y Prescripciones:"
                ),
                m("h6.text-light-dark.ff-roboto.pb-40.mb-0",
                    "Hospital Metropolitano"
                ),
                m("div.text-center", [
                    m("div.loader-content",
                        m("span.icon-section-wave.d-inline-block.text-active.mt-3.", )
                    )
                ])
            ]),
        ]


    },
}

const WidgetsSV = {
    isData: 0,
    data: {
        PAS: [],
        PAD: [],
        FC: [],
        FR: [],
        SO: [],
        SO: [],
        FIO: [],
        TEMP: [],
        PS: [],
        TA: [],
    },
    oninit: () => {
        WidgetsSV.isData = 0;
        SignosVitales.data.map(function(_v, _i, _contentData) {

            if (_v.SIGNO == 'PRESION ARTERIAL SISTOLICA') {

                WidgetsSV.isData = 1;

                WidgetsSV.data.PAS = _v;

            }

            if (_v.SIGNO == 'PRESION ARTERIAL DIASTOLICA') {

                WidgetsSV.isData = 1;


                WidgetsSV.data.PAD = _v;

            }

            if (_v.SIGNO == 'FRECUENCIA CARDIACA') {

                WidgetsSV.isData = 1;


                WidgetsSV.data.FC = _v;

            }

            if (_v.SIGNO == 'FRECUENCIA RESPIRATORIA') {

                WidgetsSV.isData = 1;


                WidgetsSV.data.FR = _v;

            }

            if (_v.SIGNO == 'SATURACION OXIGENO') {

                WidgetsSV.isData = 1;


                WidgetsSV.data.SO = _v;

            }

            if (_v.SIGNO == 'FI02') {

                WidgetsSV.isData = 1;


                WidgetsSV.data.FIO = _v;

            }

            if (_v.SIGNO == 'TEMPERATURA') {

                WidgetsSV.isData = 1;


                WidgetsSV.data.TEMP = _v;

            }

            if (_v.SIGNO == 'PESO') {

                WidgetsSV.isData = 1;


                WidgetsSV.data.PS = _v;

            }

            if (_v.SIGNO == 'TALLA') {

                WidgetsSV.isData = 1;


                WidgetsSV.data.TA = _v;

            }


        })

    },
    view: () => {


        if (SignosVitales.data.length !== 0 && WidgetsSV.isData !== 0) {
            return [
                Object.keys(WidgetsSV.data).map(function(_v, _i, _contentData) {

                    if ((_v == 'PAS' || _v == 'PAD') && WidgetsSV.data[_v].length !== 0) {
                        return m("div.col-sm-10.offset-sm-1.col-md-12.offset-md-0.col-xl-6",
                            m("div.single-service.bg-white.type-3.radius-10.position-relative.service-wrapper.s-dp-1-3.h-dp-10-60.m-mb-50",
                                m("div.media", [
                                    m("div.service-circle.position-relative.mb-4.text-active.bg-white.rounded-circle.d-flex.align-items-center.justify-content-center.s-dp-1-3",
                                        m("span.icon-heart-beat.text-grad-1")
                                    ),
                                    m("div.media-body", [
                                        m("h4.text-dark2.mb-3.position-relative.pt-2",
                                            WidgetsSV.data[_v].SIGNO
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].VALOR + "mmHg."
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].FECHA
                                        )
                                    ])
                                ])
                            )
                        )
                    }

                    if (_v == 'FC' && WidgetsSV.data[_v].length !== 0) {
                        return m("div.col-sm-10.offset-sm-1.col-md-12.offset-md-0.col-xl-6",
                            m("div.single-service.bg-white.type-3.radius-10.position-relative.service-wrapper.s-dp-1-3.h-dp-10-60.m-mb-50",
                                m("div.media", [
                                    m("div.service-circle.position-relative.mb-4.text-default.rounded-circle.s-dp-1-3-15.d-flex.align-items-center.justify-content-center.s-dp-1-3",
                                        m("span.icon-heart-beat.text-grad-1")
                                    ),
                                    m("div.media-body", [
                                        m("h4.text-dark2.mb-3.position-relative.pt-2",
                                            WidgetsSV.data[_v].SIGNO
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].VALOR + " LPM"
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].FECHA
                                        )
                                    ])
                                ])
                            )
                        )
                    }

                    if (_v == 'FR' && WidgetsSV.data[_v].length !== 0) {
                        return m("div.col-sm-10.offset-sm-1.col-md-12.offset-md-0.col-xl-6",
                            m("div.single-service.bg-white.type-3.radius-10.position-relative.service-wrapper.s-dp-1-3.h-dp-10-60.m-mb-50",
                                m("div.media", [
                                    m("div.service-circle.position-relative.mb-4.text-active.bg-white.rounded-circle.d-flex.align-items-center.justify-content-center.s-dp-1-3",
                                        m("span.icofont-lungs.text-grad-1")
                                    ),
                                    m("div.media-body", [
                                        m("h4.text-dark2.mb-3.position-relative.pt-2",
                                            WidgetsSV.data[_v].SIGNO
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].VALOR + " RPM"
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].FECHA
                                        )
                                    ])
                                ])
                            )
                        )
                    }

                    if (_v == 'SO' && WidgetsSV.data[_v].length !== 0) {
                        return m("div.col-sm-10.offset-sm-1.col-md-12.offset-md-0.col-xl-6",
                            m("div.single-service.bg-white.type-3.radius-10.position-relative.service-wrapper.s-dp-1-3.h-dp-10-60.m-mb-50",
                                m("div.media", [
                                    m("div.service-circle.position-relative.mb-4.text-active.bg-white.rounded-circle.d-flex.align-items-center.justify-content-center.s-dp-1-3",
                                        m("span.icofont-stethoscope.text-grad-1")
                                    ),
                                    m("div.media-body", [
                                        m("h4.text-dark2.mb-3.position-relative.pt-2",
                                            "SATURACIÓN DE OXIGENO"
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].VALOR + " %"
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].FECHA
                                        )
                                    ])
                                ])
                            )
                        )
                    }

                    if (_v == 'FIO' && WidgetsSV.data[_v].length !== 0) {
                        return m("div.col-sm-10.offset-sm-1.col-md-12.offset-md-0.col-xl-6",
                            m("div.single-service.bg-white.type-3.radius-10.position-relative.service-wrapper.s-dp-1-3.h-dp-10-60.m-mb-50",
                                m("div.media", [
                                    m("div.service-circle.position-relative.mb-4.text-active.bg-white.rounded-circle.d-flex.align-items-center.justify-content-center.s-dp-1-3",
                                        m("span.icofont-stethoscope.text-grad-1")
                                    ),
                                    m("div.media-body", [
                                        m("h4.text-dark2.mb-3.position-relative.pt-2",
                                            "FIO2"
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].VALOR + " %"
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].FECHA
                                        )
                                    ])
                                ])
                            )
                        )
                    }

                    if (_v == 'TEMP' && WidgetsSV.data[_v].length !== 0) {
                        return m("div.col-sm-10.offset-sm-1.col-md-12.offset-md-0.col-xl-6",
                            m("div.single-service.bg-white.type-3.radius-10.position-relative.service-wrapper.s-dp-1-3.h-dp-10-60.m-mb-50",
                                m("div.media", [
                                    m("div.service-circle.position-relative.mb-4.text-active.bg-white.rounded-circle.d-flex.align-items-center.justify-content-center.s-dp-1-3",
                                        m("span.icofont-thermometer.text-grad-1")
                                    ),
                                    m("div.media-body", [
                                        m("h4.text-dark2.mb-3.position-relative.pt-2",
                                            "TEMPERATURA"
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].VALOR + " °C"
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].FECHA
                                        )
                                    ])
                                ])
                            )
                        )
                    }

                    if (_v == 'PS' && WidgetsSV.data[_v].length !== 0) {
                        return m("div.col-sm-10.offset-sm-1.col-md-12.offset-md-0.col-xl-6",
                            m("div.single-service.bg-white.type-3.radius-10.position-relative.service-wrapper.s-dp-1-3.h-dp-10-60.m-mb-50",
                                m("div.media", [
                                    m("div.service-circle.position-relative.mb-4.text-active.bg-white.rounded-circle.d-flex.align-items-center.justify-content-center.s-dp-1-3",
                                        m("span.icofont-user-alt-1.text-grad-1")
                                    ),
                                    m("div.media-body", [
                                        m("h4.text-dark2.mb-3.position-relative.pt-2",
                                            "PESO"
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].VALOR + " °C"
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].FECHA
                                        )
                                    ])
                                ])
                            )
                        )
                    }

                    if (_v == 'TA' && WidgetsSV.data[_v].length !== 0) {
                        return m("div.col-sm-10.offset-sm-1.col-md-12.offset-md-0.col-xl-6",
                            m("div.single-service.bg-white.type-3.radius-10.position-relative.service-wrapper.s-dp-1-3.h-dp-10-60.m-mb-50",
                                m("div.media", [
                                    m("div.service-circle.position-relative.mb-4.text-active.bg-white.rounded-circle.d-flex.align-items-center.justify-content-center.s-dp-1-3",
                                        m("span.icofont-ruler.text-grad-1")
                                    ),
                                    m("div.media-body", [
                                        m("h4.text-dark2.mb-3.position-relative.pt-2",
                                            "TALLA"
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].VALOR + " cm."
                                        ),
                                        m("p.mb-4.text-default.fz-poppins.text-Underline",
                                            WidgetsSV.data[_v].FECHA
                                        )
                                    ])
                                ])
                            )
                        )
                    }



                })
            ]
        } else {
            return [
                m(".alert.alert-danger[role='alert']",
                    "No existe información disponible."
                )
            ]
        }










    }
};

const SignosVitales = {
    data: [],
    detalle: [],
    error: "",
    fetch: () => {
        SignosVitales.data = [];
        SignosVitales.error = "";
        m.request({
                method: "POST",
                url: "https://api.hospitalmetropolitano.org/t/v1/sv-paciente-emergencia",
                body: {
                    numeroHistoriaClinica: Paciente.nhc
                },
                headers: {
                    "Authorization": localStorage.accessToken,
                },
                extract: (xhr, options) => {

                    if (xhr.status !== 200) {
                        SignosVitales.fetch();
                    }

                    return JSON.parse(xhr.responseText);
                }
            })
            .then(function(result) {
                if (result.status) {
                    SignosVitales.data = result.data;
                } else {
                    SignosVitales.error = result.message;
                }

            })
            .catch(function(e) {
                SignosVitales.error = e.message;
            })
    },
    oninit: () => {

        SignosVitales.fetch();
    },
    view: () => {

        return SignosVitales.error ? [

            m(".tab-pane.fade.active.show[id='v-pills-sv'][role='tabpanel']", [
                m("h4.m-text-2.", [
                        m("i.icofont-heart-beat.mr-2"),
                        "Signos Vitales:"
                    ]

                ),
                m("h6.text-light-dark.ff-roboto.pb-40.mb-0",
                    "Hospital Metropolitano"
                ),
                m(".alert.alert-danger[role='alert']",
                    SignosVitales.error
                )


            ]),
        ] : SignosVitales.data.length !== 0 ? [
            m(".tab-pane.fade.active.show[id='v-pills-sv'][role='tabpanel']", [
                m("h4.m-text-2.", [
                        m("i.icofont-heart-beat.mr-2"),
                        "Signos Vitales:"
                    ]

                ),
                m("h6.text-light-dark.ff-roboto.pb-40.mb-0",
                    "Hospital Metropolitano"
                ),
                m("h6.mb-5.d-flex", [
                        "Última información disponible. HIS MV.",


                    ]

                ),
                m("div.row", [
                    m(WidgetsSV)
                ])
            ]),
        ] : [
            m(".tab-pane.fade.active.show[id='v-pills-sv'][role='tabpanel']", [
                m("h4.m-text-2.", [
                    m("i.icofont-heart-beat.mr-2"),
                    "Signos Vitales:"
                ]),
                m("h6.text-light-dark.ff-roboto.pb-40.mb-0",
                    "Hospital Metropolitano"
                ),
                m("h6.mb-5.d-flex", [
                        "Última información disponible. HIS MV.",

                    ]

                ),
                m("div.text-center", [
                    m("div.loader-content",
                        m("span.icon-section-wave.d-inline-block.text-active.mt-3.", )
                    )
                ])
            ]),
        ]



    },
}

const DetallePaciente = {
    data: [],
    detalle: [],
    error: "",
    fetch: () => {
        DetallePaciente.data = [];
        DetallePaciente.error = "";
        m.request({
                method: "POST",
                url: "https://api.hospitalmetropolitano.org/t/v1/status-paciente-emergencia",
                body: {
                    numeroHistoriaClinica: Paciente.nhc
                },
                headers: {
                    "Authorization": localStorage.accessToken,
                },
            })
            .then(function(result) {
                if (result.status) {
                    DetallePaciente.data = result.data;
                } else {
                    DetallePaciente.error = "No existe información disponible. La ubicación del paciente ya no es Emergencia.";
                }
            })
            .catch(function(e) {
                DetallePaciente.error = e.message;
            })
    },
    view: () => {
        return [
            m("div.col-md-4." + DetalleClinico.inZoom,
                m("div.department-tab-pill.m-pt-140.m-pb-140.position-relative.", [
                    m("i.icofont-prescription.text-white.fz-40", { "style": { "margin-left": "-5px" } }),
                    m("h2.text-white.pb-md-5", [

                            DetallePaciente.data.NOMBRE_PACIENTE
                        ]

                    ),
                    m("h6.ml12.text-white.text-uppercase.fadeInDown-slide.animated",
                        "NHC: " + DetallePaciente.data.HC
                    ),
                    m("h6.ml12.text-white.text-uppercase.fadeInDown-slide.animated",
                        "Edad: " + DetallePaciente.data.EDAD + " Año(s)"
                    ),
                    m("h6.ml12.text-white.text-uppercase.fadeInDown-slide.animated",
                        "Especialidad: " + DetallePaciente.data.ESPECIALIDAD
                    ),
                    m("h6.ml12.text-white.text-uppercase.fadeInDown-slide.animated",
                        (DetallePaciente.data.NRO_HABITACION !== null) ? "Ubicación: " + DetallePaciente.data.NRO_HABITACION : "Ubicación: NO DISPONIBLE"
                    ),
                    m(".nav.pt-md-0.flex-column.nav-pills[id='v-pills-tab'][role='tablist'][aria-orientation='vertical']", [
                        m("a.nav-link.active[data-toggle='pill'][href='#v-pills-sv'][role='tab']", {
                            onclick: () => {
                                MenuBoton.update = "SV";
                            },
                        }, [
                            m("i.icofont-heart-beat"),
                            m("span",
                                " Signos Vitales "
                            )
                        ]),
                        m("a.nav-link[data-toggle='pill'][href='#v-pills-ev'][role='tab']", {
                            onclick: () => {
                                MenuBoton.update = "EV";
                            },
                        }, [
                            m("i.icofont-prescription"),
                            m("span",
                                " Evoluciones "
                            )
                        ]),
                        m("a.nav-link[data-toggle='pill'][href='#v-pills-lab'][role='tab']", {
                            onclick: () => {
                                MenuBoton.update = "LAB";
                            },
                        }, [
                            m("i.icofont-laboratory"),
                            m("span",
                                " Laboratorio "
                            )
                        ]),
                        m("a.nav-link[data-toggle='pill'][href='#v-pills-imagen'][role='tab']", [
                            m("i.icofont-file-image"),
                            m("span",
                                " Imagen "
                            )
                        ]),
                        m("a.nav-link", {
                            href: "/#!/pacientes"

                        }, [
                            m("i.icofont-circled-left"),
                            m("span",
                                " Mis Pacientes "
                            )
                        ])
                    ])
                ])
            ),
        ]

    },
}

const MenuBoton = {
    show: "",
    close: "d-none",
    zoomin: "d-none",
    zoomout: "d-none",
    reload: "d-none",
    zi: "",
    update: "",
    setComand: () => {

        if (MenuBoton.update == "SV") {
            SignosVitales.fetch();
        }

        if (MenuBoton.update == "EV") {
            Evoluciones.fetch();
        }

        if (MenuBoton.update == "LAB") {
            Laboratorio.fetch();
        }

        if (MenuBoton.update == "RX") {
            Imagen.fetch();
        }




    },
    view: () => {
        return [
            m("div.button-menu-right-plus." + MenuBoton.show, { "style": { "display": "flex" } },
                m("a.btn.fadeInDown-slide.position-relative.animated.pl-3.pr-3.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2", {
                        onclick: (e) => {
                            e.preventDefault();
                            MenuBoton.show = "d-none";
                            MenuBoton.close = "";
                            MenuBoton.zoomin = "";
                            MenuBoton.zoomout = "";
                            MenuBoton.reload = "";

                        },
                    },
                    m("i.icofont-plus", { "style": { "font-size": "x-large" } })
                )
            ),
            m("div.button-menu-right-close." + MenuBoton.close, { "style": { "display": "flex" } }, [
                    m("a.btn.fadeInDown-slide.position-relative.animated.pl-3.pr-3.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2", {
                            onclick: (e) => {
                                e.preventDefault();
                                MenuBoton.show = "";
                                MenuBoton.close = "d-none";
                                MenuBoton.zoomin = "d-none";
                                MenuBoton.zoomout = "d-none";
                                MenuBoton.reload = "d-none";
                            },
                        },
                        m("i.icofont-close", { "style": { "font-size": "x-large" } })
                    )

                ]

            ),
            m("div.button-menu-right-reload-pte." + MenuBoton.reload, { "style": { "display": "flex" } }, [
                    m("div.text-primary.mr-2", "Actualizar"),
                    m("a.btn.fadeInDown-slide.position-relative.animated.pl-3.pr-3.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2", {
                            onclick: (e) => {
                                e.preventDefault();
                                MenuBoton.show = "";
                                MenuBoton.close = "d-none";
                                MenuBoton.zoomin = "d-none";
                                MenuBoton.zoomout = "d-none";
                                MenuBoton.reload = "d-none";
                                verDocPDF.show = "";

                                MenuBoton.setComand();


                            },
                        },
                        m("i.icofont-refresh", { "style": { "font-size": "x-large" } })
                    )
                ]

            ),
            m("div.button-menu-right-zoomin." + MenuBoton.zoomin, { "style": { "display": "flex" } }, [
                    m("div.text-primary.mr-2", "Aumentar"),
                    m("a.btn.fadeInDown-slide.position-relative.animated.pl-3.pr-3.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2", {
                            onclick: (e) => {
                                e.preventDefault();
                                Formulario.zoom = 0.85;
                                DetalleClinico.inZoom = "d-none";
                                MenuBoton.show = "";
                                MenuBoton.close = "d-none";
                                MenuBoton.zoomin = "d-none";
                                MenuBoton.zoomout = "d-none";
                                MenuBoton.reload = "d-none";
                            },
                        },
                        m("i.icofont-ui-zoom-in", { "style": { "font-size": "x-large" } })
                    )
                ]

            ),
            m("div.button-menu-right-zoomout." + MenuBoton.zoomout, { "style": { "display": "flex" } }, [
                    m("div.text-primary.mr-2", "Disminuir"),
                    m("a.btn.fadeInDown-slide.position-relative.animated.pl-3.pr-3.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2", {
                            onclick: (e) => {
                                e.preventDefault();
                                Formulario.zoom = 0.5;
                                DetalleClinico.inZoom = "";
                                MenuBoton.show = "";
                                MenuBoton.close = "d-none";
                                MenuBoton.zoomin = "d-none";
                                MenuBoton.zoomout = "d-none";
                                MenuBoton.reload = "d-none";
                            },
                        },
                        m("i.icofont-ui-zoom-out", { "style": { "font-size": "x-large" } })
                    )

                ]

            )

        ]
    }
};

const DetalleClinico = {
    ver: true,
    eliminar: false,
    editar: false,
    labelOperation: "Detalle:",
    inZoom: "",
    oninit: () => {
        MenuBoton.update = "SV";
        DetallePaciente.fetch();
    },
    view: () => {
        return DetallePaciente.error ? [
            m("div.container",
                m("div.m-pt-50.text-center", [
                    m(".alert.alert-danger[role='alert']", [
                            DetallePaciente.error,
                            " Ver Información disponible.",
                            m("a", {
                                href: "#!/resultados/paciente/" + Paciente.nhc
                            }, " Click Aquí"),

                        ]

                    )
                ])
            )
        ] : DetallePaciente.data.length !== 0 ? [
            m("section.m-bg-1.intro-area.type-1.position-relative", [
                m("div.intro-overlay.position-absolute.set-bg." + DetalleClinico.inZoom, {
                    "style": {
                        "background-position": "center center",
                        "background-size": "cover",
                        "background-repeat": "no-repeat",
                        "background-image": 'url(\"/assets/images/intro-bg.jpg\")',
                    }
                }),
                m("div.overlay." + DetalleClinico.inZoom),
                m("div.container", {
                        class: (DetalleClinico.inZoom.length === 0) ? "" : "bg-white",
                        style: {
                            "height": "2500px"
                        }
                    },
                    m("div.row", [
                        m(DetallePaciente),
                        m("div", {
                            class: (DetalleClinico.inZoom.length !== 0) ? "col-md-12" : "col-md-8"
                        }, [
                            m("div.tab-content.m-pb-140.", {
                                class: (DetalleClinico.inZoom.length === 0) ? "m-pt-140" : "m-pt-40"
                            }, [
                                m(SignosVitales),
                                m(Evoluciones),
                                m(Laboratorio),
                                m(Imagen)
                            ])
                        ])
                    ]),

                ),
                m(MenuBoton)
            ])
        ] : m(Loader)
    }
}


const Paciente = {
    nhc: null,
    oninit: (_data) => {
        Paciente.nhc = _data.attrs.nhc;
        Loader.show = "";
        Loader.buttonShow = "";
        DetallePaciente.data = [];
        Evoluciones.data = [];
        Formulario.data = [];
        Imagen.data = [];
        SignosVitales.data = [];




        if (!Auth.isLogin()) {
            return m.route.set('/auth');
        }
    },
    oncreate: () => {
        document.title = "Paciente NHC: " + Paciente.nhc + " | " + App.title;
        _Main()
    },
    view: () => {

        if (VisorRis.show.length === 0) {
            return [
                (DetalleClinico.inZoom.length === 0) ? m(HeaderPrivate) : "",
                m(DetalleClinico)
            ];
        } else {
            return [
                m(VisorRis)
            ];
        }

    },

};



function _Main() {

    //Global Variables
    var windowHeight = $(window).height();
    var windowWidth = $(window).width();

    //Document Ready
    //Counter up
    $('.counter').counterUp({
        delay: 10,
        time: 1000
    });

    $('.section-wave').each(function() {

        var self = $(this);

        $(this).waypoint({
            offset: '85%',
            handler: function() {
                self.addClass('active')
            }
        });
    })

    //Dynamic sticky Menu

    var sitckyHeader = $('.navbar-sticky');
    if (sitckyHeader.length > 0) {
        var navOffset = $('.navbar-sticky').offset().top;
        $(window).on('scroll', function() {
            var $cloneNav = $('.navbar-sticky').clone(true);
            $cloneNav.addClass('sticky-active');
            if ($(this).scrollTop() > navOffset) {
                if ($(document).find('.sticky-active').length < 1) {
                    $('.navbar-sticky').not('.sticky-active').css({
                        visibility: 'hidden',
                        opacity: 0
                    });
                    $('header').append($cloneNav);
                    $cloneNav.show('slow');
                } else {

                }
            } else {
                $('.navbar-sticky').not('.sticky-active').css({
                    visibility: 'visible',
                    opacity: 1
                });
                $('.sticky-active').remove();
            }
        });
    }


    //Anime js
    $('.ml12').each(function() {
        $(this).html($(this).text().replace(/([^\x00-\x80]|\w)/g, "<span class='letter'>$&</span>"));
    });

    anime.timeline({
            loop: false
        })
        .add({
            targets: '.ml12 .letter',
            translateX: [40, 0],
            translateZ: 0,
            opacity: [0, 1],
            easing: "easeOutExpo",
            duration: 1200,
            delay: function(el, i) {
                return 500 + 30 * i;
            },
            complete: function() {
                anime({
                    targets: '.ml12 .letter',
                    opacity: 1,
                    duration: 1000,
                });
            }
        });


    anime.timeline({ loop: false })
        .add({
            targets: '.ml15 .word',
            scale: [2, 1],
            opacity: [0, 1],
            easing: "easeOutCirc",
            duration: 800,
            delay: function(el, i) {
                return 800 * i;
            },
            complete: function() {
                anime({
                    targets: '.ml15 .word',
                    opacity: 1,
                    duration: 1000,
                });
            }
        });


    /* ----------------------------------
    ----------------------------------*/
    $('.doctors-slider').slick({
        slidesToScroll: 1,
        slidesToShow: 3,
        dots: true,
        infinite: true,
        centerMode: true,
        autoplay: true,
        autoplaySpeed: 2000,
        centerPadding: '0px',
        arrows: false,
        responsive: [{
                breakpoint: 992,
                settings: {
                    centerMode: false,
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 768,
                settings: {
                    centerMode: false,
                    slidesToShow: 2

                }
            },
            {
                breakpoint: 577,
                settings: {
                    autoplay: true,
                    centerMode: false,
                    slidesToShow: 1
                }
            },
            {
                breakpoint: 300,
                settings: {
                    autoplay: false,
                    centerMode: false,
                    slidesToShow: 1
                }
            }
        ]

    });
    //Testimonial slider
    $('.testimonial-slider2').slick({
        slidesToScroll: 1,
        slidesToShow: 1,
        dots: true,
        infinite: true,
        arrows: false

    });
    //Partner Slider
    $('.partner-slider').slick({
        slidesToScroll: 1,
        slidesToShow: 5,
        dots: false,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 2000,
        arrows: false,
        responsive: [{
                breakpoint: 992,
                settings: {
                    slidesToShow: 4
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 300,
                settings: {
                    slidesToShow: 1
                }
            }
        ]

    });
    //
    $('.testimonial-slider').slick({
        slidesToScroll: 1,
        slidesToShow: 2,
        dots: true,
        infinite: true,
        arrows: false,
        responsive: [{
                breakpoint: 992,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 1
                }
            },
            {
                breakpoint: 300,
                settings: {
                    slidesToShow: 1
                }
            }
        ]

    });

    // Department Carousel


    //Set background image for WordPress
    $(".set-bg").each(function() {
        var thesrc = $(this).attr('data-bg');
        $(this).css("background-image", "url(" + thesrc + ")");
        $(this).css("background-position", "center");
        $(this).css("background-size", "cover");
        $(this).css("background-repeat", "no-repeat");
        $(this).removeAttr('data-bg');
    });
    //Date Picker
    $(".datepicker").datepicker({
        dateFormat: 'yy-mm-dd',
    });
    //Select-2
    $('.js-example-basic-single').select2();

    // Convert All Image to SVG
    $('img.svg').each(function() {
        var $img = $(this),
            imgID = $img.attr('id'),
            imgClass = $img.attr('class'),
            imgURL = $img.attr('src');

        $.get(imgURL, function(data) {
            var $svg = $(data).find('svg');
            if (typeof imgID !== 'undefined') {
                $svg = $svg.attr('id', imgID);
            }
            if (typeof imgClass !== 'undefined') {
                $svg = $svg.attr('class', imgClass);
            }
            $svg = $svg.removeAttr('xmlns:a');
            $img.replaceWith($svg);
        }, 'xml');

    });

    //Burget Menu
    $('.burger_menu').on('click', function(e) {
        e.preventDefault();
        if (isMobile) {
            moileMenu()
        } else {

        }
        $(this).toggleClass('is-open');
        $('.bcbd_collpase_nav').toggleClass('show_clmenu');
        $('.bcbd_navbar').toggleClass('activeMobileMenu');
        $('.bcbd_nav').find('.opendp').removeClass('opendp');
        $('.bcbd_nav').children('.hs_dropdown').find('.mega-menu').slideUp();
        $('.bcbd_nav').children('.hs_dropdown').find('.bcbd_dropdown').slideUp();
        $('body').toggleClass("mobileMenuOpen");
        if ($('body').hasClass("mobileMenuOpen")) {
            $('body').css('overflowY', 'hidden');

        } else {
            $('body').css({
                overflow: 'visible',
                height: '100%'
            });
        }
    });

    //For drop down navigation
    $('.hs_dropdown > a').on('click', function(e) {
        var hash = this.hash;
        if ($(this).attr('href') != '' || hash) {
            e.preventDefault();
            e.stopPropagation();
        } else {

        }

    });

    var wWidth = $(window).width();
    var isMobile = wWidth < 992;
    $(window).on('resize', function() {
        wWidth = $(window).width();
        isMobile = wWidth < 992;
        if (wWidth >= 992) {
            $('.mega-menu').show();
            $('.bcbd_dropdown').show();
        }
    });

    function moileMenu() {
        $('.hs_dropdown').on('click', function(ev) {
            //ev.preventDefault();
            ev = window.event || ev;
            ev.stopPropagation();
            if ($(this).parents('.bcbd_collpase_nav').hasClass('show_clmenu')) {
                $(this).children('ul').stop().slideToggle();
                $(this).siblings('.hs_dropdown').children('ul').stop().slideUp();
                $(this).siblings('.hs_dropdown').children('.mega-menu ').stop().slideUp();
                $(this).toggleClass('opendp');
                $(this).siblings('.hs_dropdown').children('.mega-menu').find('.opendp').children('.bcbd_dropdown').stop().slideUp();
                $(this).siblings('.hs_dropdown').children('.mega-menu').find('.opendp ').removeClass('opendp')
                $(this).siblings('.hs_dropdown').removeClass('opendp');

                if ($(this).children('.mega-menu ')) {
                    $(this).children('.mega-menu ').stop().slideToggle();

                }
            }

        });
    }


    //ripple Effect
    $(".banenr").on('click', function(e) {

        // Remove any old one
        $(".ripple").remove();

        // Setup
        var posX = $(this).offset().left,
            posY = $(this).offset().top,
            buttonWidth = $(this).width(),
            buttonHeight = $(this).height();

        // Add the element
        $(this).prepend("<span class='ripple'></span>");


        // Make it round!
        if (buttonWidth >= buttonHeight) {
            buttonHeight = buttonWidth;
        } else {
            buttonWidth = buttonHeight;
        }

        var x = e.pageX - posX - buttonWidth / 2;
        var y = e.pageY - posY - buttonHeight / 2;

        $(".ripple").css({
            width: buttonWidth,
            height: buttonHeight,
            top: y + 'px',
            left: x + 'px'
        }).addClass("rippleEffect");
    })

    //scroll top
    var documentHeight = $(document).height();
    var scrollableHeight = documentHeight / 1.70;
    $('.scroll-top').hide();
    $(window).on('scroll', function() {
        if ($(this).scrollTop() > scrollableHeight) {
            $('.scroll-top').show();
        } else {
            $('.scroll-top').hide();
        }
    });
    $('.scroll-top').on('click', function() {
        $("html, body").animate({ scrollTop: 0 }, "slow");
    });

    //Venobox
    $('.venobox').venobox();
    //Check Content block children
    $('.content-block').each(function(index, el) {
        if ($(this).children().length > 0) {
            $(this).addClass('has-content')
        }
    });


    /* ----------------------------------
    ----------------------------------*/
    $(document).on('mouseup', function(e) {
        var container = $(".nav_outer,.burger_menu");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            $('.burger_menu').removeClass('is-open');
            $('.bcbd_navbar').removeClass('activeMobileMenu');
            $('body').removeClass('mobileMenuOpen');
            $('.bcbd_collpase_nav').removeClass('show_clmenu');
        }
    });


    // Makin Wp Frindly Parallax image

    $('.parallax-window').each(function() {
        var image = $(this).attr('data-bg');
        $(this).parallax({
            imageSrc: image
        });
    });


    //Mouse Move
    $(window).on('load', function() {
        var wrapper = document.querySelector('.banenr.type-2');
        if ($('.banenr.type-2').length > 0) {
            var layerOne = document.querySelector('.inner-image');
            wrapper.addEventListener('mousemove', function(e) {
                var pageX = e.clientX,
                    pageY = e.clientY;
                layerOne.style.webkitTransform = 'translateX(' + pageX / 250 + '%) translateY(' + pageY / 250 + '%)';
                layerOne.style.transform = 'translateX(' + pageX / 250 + '%) translateY(' + pageY / 250 + '%)';

            });
        }
    });

    //Windows Load
    $(window).on('load', function() {
        var wrapper = document.querySelector('.banenr.type-4');
        if ($('.banenr.type-4').length > 0) {
            var layerOne = document.querySelector('.logo-box');
            var layerTwo = document.querySelector('.banenr.type-4 h5');
            var layerThree = document.querySelector('.banenr.type-4  .btn');
            var layerFour = document.querySelector('.banenr.type-4  h1');
            var layerFive = document.querySelector('.banenr.type-4  .no-border');
            wrapper.addEventListener('mousemove', function(e) {
                var pageX = e.clientX,
                    pageY = e.clientY;
                layerOne.style.webkitTransform = 'translateX(' + pageX / 240 + '%) translateY(' + pageY / 40 + '%)';
                layerOne.style.transform = 'translateX(' + pageX / 240 + '%) translateY(' + pageY / 40 + '%)';

                layerTwo.style.webkitTransform = 'translateX(' + pageX / 240 + '%) translateY(' + pageY / 40 + '%)';
                layerTwo.style.transform = 'translateX(' + pageX / 240 + '%) translateY(' + pageY / 40 + '%)';

                layerThree.style.webkitTransform = 'translateX(' + pageX / 240 + '%) translateY(' + pageY / 40 + '%)';
                layerThree.style.transform = 'translateX(' + pageX / 240 + '%) translateY(' + pageY / 40 + '%)';

                layerFour.style.webkitTransform = 'translateX(' + pageX / 200 + '%) translateY(' + pageY / 40 + '%)';
                layerFour.style.transform = 'translateX(' + pageX / 240 + '%) translateY(' + pageY / 40 + '%)';

                layerFive.style.webkitTransform = 'translateX(' + pageX / 200 + '%) translateY(' + pageY / 40 + '%)';
                layerFive.style.transform = 'translateX(' + pageX / 240 + '%) translateY(' + pageY / 40 + '%)';

                wrapper.style = 'background-position:' + pageX / 100 + 'px' + ' ' + pageY / 40 + 'px';

            });
        }
    });
    //Check height for Banner



    $(window).on('load', function() {
        if (windowHeight <= 800) {
            $('.banenr.type-4 .banner-inner').each(function(index, el) {
                $(this).addClass('full-height');
            });
            $('.reversed-margin.appoint-area').each(function(index, el) {
                $(this).addClass('low-banner-height');
            });
        } else {
            $('.reversed-margin.appoint-area').removeClass('low-banner-height');
        }
        // isotop initialize
        $('.pricing-grid').isotope({
            itemSelector: '.grid-item',
            filter: '.monthly'

        });

        $('.pricing-filter li').on('click', function() {
            $(this).addClass('active').siblings().removeClass('active');
            var filterValue = $(this).attr('data-filter');
            $('.grid').isotope({
                filter: filterValue
            });
        });


        var grid = $('.grid');
        if (grid.length > 0) {
            grid.each(function(index, el) {
                $('.grid').isotope({
                    itemSelector: '.grid-item'

                });

                $('.filter li').on('click', function() {
                    $(this).addClass('active').siblings().removeClass('active');
                    var filterValue = $(this).attr('data-filter');
                    $('.grid').isotope({
                        filter: filterValue
                    });
                });

            });
        }
    })


    // Content schedule
    $(window).on("load resize scroll", function(e) {
        $('.content-block').each(function() {
            if ($(this).children('div').length > 0) {

            } else {
                $(this).addClass('no-schedule')
            }
        });
    });


}






export default Paciente;