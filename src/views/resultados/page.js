import Auth from '../../models/auth';
import App from '../app';
import Loader from '../loader';

const DataProvider = {
    data: [],
    filteredData: [],
    searchField: "",
    tipoBusqueda: "",



    fetch: () => {

        if (DataProvider.tipoBusqueda == 'pte' && DataProvider.searchField.length !== 0) {
            let _l = countWords(DataProvider.searchField);
            if (_l >= 2) {

                DataProvider.data = [];
                Loader.show = "";
                Loader.buttonShow = "";
                m.request({
                        method: "POST",
                        url: "https://api.hospitalmetropolitano.org/t/v1/buscar-paciente",
                        body: {
                            tipoBusqueda: DataProvider.tipoBusqueda,
                            pte: DataProvider.searchField
                        },
                        headers: {
                            "Authorization": localStorage.accessToken,
                        },
                    })
                    .then(function(result) {
                        Loader.show = "d-none";
                        Loader.buttonShow = "d-none";

                        DataProvider.data = result.data;
                        DataProvider.filterData();
                    })
                    .catch(function(e) {})


            } else {
                alert('Para una mejor búsqueda te sugerimos dos apellidos u nombres. Ej. Moya Teraza')
            }
        } else {


            DataProvider.data = [];
            Loader.show = "";
            Loader.buttonShow = "";
            m.request({
                    method: "POST",
                    url: "https://api.hospitalmetropolitano.org/t/v1/buscar-paciente",
                    body: {
                        tipoBusqueda: DataProvider.tipoBusqueda,
                        pte: DataProvider.searchField
                    },
                    headers: {
                        "Authorization": localStorage.accessToken,
                    },
                })
                .then(function(result) {
                    Loader.show = "d-none";
                    Loader.buttonShow = "d-none";

                    DataProvider.data = result.data;
                    DataProvider.filterData();
                })
                .catch(function(e) {})

        }



    },
    loadData: function() {
        DataProvider.fetch();

    },
    filterData: function() {
        var to = Math.min(DataProvider.from + DataProvider.count, DataProvider.data.length + 1);
        DataProvider.filteredData = [];
        for (var i = DataProvider.from - 1; i < to - 1; i++) {
            DataProvider.filteredData.push(DataProvider.data[i]);
        }
    },
    from: 1,
    count: 10,
    setFrom: function(from) {
        DataProvider.from = parseInt(from);
        DataProvider.filterData();
    },
    setCount: function(count) {
        DataProvider.count = parseInt(count);
        DataProvider.filterData();
    },
    nextPage: function() {
        var from = DataProvider.from + DataProvider.count;
        if (from > DataProvider.data.length)
            return;
        DataProvider.from = from;
        DataProvider.filterData();
    },
    lastPage: function() {
        DataProvider.from = DataProvider.data.length - DataProvider.count + 1;
        DataProvider.filterData();
    },
    prevPage: function() {
        DataProvider.from = Math.max(1, DataProvider.from - DataProvider.count);
        DataProvider.filterData();
    },
    firstPage: function() {
        DataProvider.from = 1;
        DataProvider.filterData();
    },
    rowBack: function() {
        DataProvider.from = Math.max(1, DataProvider.from - 1);
        DataProvider.filterData();
    },
    rowFwd: function() {
        if (DataProvider.from + DataProvider.count - 1 >= DataProvider.data.length)
            return;
        DataProvider.from += 1;
        DataProvider.filterData();
    }
};

const dataView = {
    oninit: DataProvider.loadData,
    view: () => {

        if (DataProvider.filteredData.length !== 0) {

            Loader.show = "d-none";
            Loader.buttonShow = "d-none";
            return m('table.w-100.mt-5', [

                m('tbody', DataProvider.filteredData.map(function(d) {


                    return m("div.p-5.mb-3.doctrs-info-card.grad-bg--5.position-relative.type-1.radius-10", [
                        m("h4.text-white.mb-0", [
                                m("i.icofont-ui-user"),
                                " " + d['APELLIDOS'] + " " + d['NOMBRES']
                            ]

                        ),
                        m("div.text-right", [
                            m("a.btn.medim-btn.solid-btn.mt-4.text-medium.radius-pill.text-active.text-uppercase.white-btn.bg-transparent.position-relative", {
                                    href: "#!/resultados/paciente/" + d['PK_NHCL']
                                },
                                " Ver Paciente "
                            )
                        ])

                    ])
                }))
            ]);



        } else {

            Loader.show = "d-none";
            Loader.buttonShow = "d-none";
            return m('table.w-100.mt-5', [


            ]);

        }




    }
};

const pageTool = {
    view: () => {


        if (DataProvider.data.length === 0) {

            return [
                m("div.text-center.w-100.mt-5", [
                    m('span', '(0) Resultado(s)'),
                ]),
            ]

        } else if (DataProvider.data.length > 10) {

            return [
                m("div.text-center.w-100.mt-5", [
                    m('span', '(' + DataProvider.data.length + ') Resultado(s) '),
                ]),
                m('div.d-flex.w-100.text-center.mt-5', [
                    m("div.w-50.w-20", [


                        m("btn.fadeInDown-slide.position-relative.animated.pl-4.pr-4.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2.mr-2", {
                                type: "button",
                                "style": { "cursor": "pointer" },

                                onclick: function() { DataProvider.rowBack(); }
                            },
                            " << Anterior "
                        ),
                    ]),

                    m("div.w-50.w-20", [

                        m("btn.fadeInDown-slide.position-relative.animated.pl-4.pr-4.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2.mr-2", {
                                type: "button",
                                "style": { "cursor": "pointer" },

                                onclick: function() { DataProvider.rowFwd(); }
                            },
                            " Siguiente >>"
                        ),



                    ])
                ]),
                m('div.d-flex.w-100.text-center.mt-5', [
                    m("div.w-50.w-20", [
                        m("btn.fadeInDown-slide.position-relative.animated.pl-4.pr-4.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2.mr-2", {
                                type: "button",
                                "style": { "cursor": "pointer" },

                                onclick: function() { DataProvider.firstPage(); }
                            },
                            " | Inicio "
                        ),

                        m("btn.fadeInDown-slide.position-relative.animated.pl-4.pr-4.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2.mr-2", {
                                type: "button",
                                "style": { "cursor": "pointer" },

                                onclick: function() { DataProvider.prevPage(); }
                            },
                            " < Pág. Ant. "
                        ),

                    ]),

                    m("div.w-50.w-20", [


                        m("btn.fadeInDown-slide.position-relative.animated.pl-4.pr-4.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2.mr-2", {
                                type: "button",
                                "style": { "cursor": "pointer" },

                                onclick: function() { DataProvider.nextPage(); }
                            },
                            " Pág. Sig. > "
                        ),


                        m("btn.fadeInDown-slide.position-relative.animated.pl-4.pr-4.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2.mr-2", {
                                type: "button",
                                "style": { "cursor": "pointer" },

                                onclick: function() { DataProvider.lastPage(); }
                            },
                            " Fin | "
                        ),

                    ])
                ])

            ]

        } else {
            return [
                m("div.text-center.w-100.mt-5", [
                    m('span', '(' + DataProvider.data.length + ') Resultado(s) '),
                ]),
            ]


        }


    }
};

const iPaciente = {
    view: (_data) => {
        return [
            m("div.intro-box.bg-white.radius-5", [
                m("div.intro-icon.rounded-circle.grad-bg--5",
                    m("span.icofont-user.text-white")
                ),
                m("h3.m-text-2.mb-3",
                    _data.attrs.NOMBRE_PACIENTE
                ),
                m("p", [
                    m("span",
                        "Ingreso:"
                    ),
                    m("span",
                        _data.attrs.FECHA_ADMISION
                    )
                ]),
                m("p", [
                    m("span",
                        "Edad:"
                    ),
                    m("span",
                        _data.attrs.EDAD + " Años"
                    )
                ]),
                m("p", [
                    m("span",
                        "Emergencia:"
                    ),
                    m("span",
                        ((_data.attrs.NRO_HABITACION == null) ? "Ubicación Pendiente" : _data.attrs.NRO_HABITACION) + ", Especialidad: " + _data.attrs.ESPECIALIDAD
                    )
                ]),
                m("p", [
                    m("span",
                        "Dg."
                    ),
                    m("span",
                        (_data.attrs.DG_PRINCIPAL !== null) ? _data.attrs.DG_PRINCIPAL : "Diagnóstico Pendiente"
                    )
                ]),
                m("div.text-right", [
                    m("a.btn.fadeInDown-slide.mt-4.animated.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-uppercase.text-white", {
                            href: "#!/paciente/" + _data.attrs.HC.slice(0, -2)
                        },
                        " Ver Paciente "
                    )
                ]),

            ]),

        ]
    }
};

const PageResultados = {
    oninit: () => {
        Loader.show = "";
        Loader.buttonShow = "";
        if (!Auth.isLogin()) {
            return m.route.set('/auth');
        }
    },

    oncreate: () => {
        submitBusqueda();

        setTimeout(function() { document.getElementById("pte").click(); }, 500);
    },

    view: () => {
        return [
            m(Loader),
            m("section.m-bg-1",
                m("div.container",
                    m("div.row",
                        m("div.col-md-6.offset-md-3",
                            m("div.text-center.m-mt-70", [
                                m("h2.m-0.text-dark",
                                    "Resultados de Imagen y Laboratorio"
                                ),
                                m("span.icon-section-wave.d-inline-block.text-active.section-wave.mt-3.active")
                            ])
                        )
                    ),
                    m("div.row.m-mb-20",
                        m("div.col-md-12", [
                            m("div.d-flex.p-1.align-items-left.position-relative.justify-content-left", [
                                m("div.custom-control.custom-radio.m-mb-20.pd-2", [
                                    m("input.custom-control-input[type='radio'][id='pte'][name='tipoBusqueda'][value='pte']", {
                                        onclick: (e) => {
                                            if (e.target.checked) {
                                                DataProvider.tipoBusqueda = e.target.value;
                                            }
                                        }
                                    }),
                                    m("label.custom-control-label[for='pte']",
                                        "Apellidos y Nombres"
                                    )
                                ]),
                                m("div.custom-control.custom-radio.m-mb-20.ml-2.pd-2", [
                                    m("input.custom-control-input[type='radio'][id='cedula'][name='tipoBusqueda'][value='cc']", {
                                        onclick: (e) => {
                                            if (e.target.checked) {
                                                DataProvider.tipoBusqueda = e.target.value;
                                            }
                                        }
                                    }),
                                    m("label.custom-control-label[for='cedula']",
                                        "Cédula"
                                    )
                                ]),
                                m("div.custom-control.custom-radio.m-mb-20.ml-2.pd-2", [
                                    m("input.custom-control-input[type='radio'][id='nhc'][name='tipoBusqueda'][value='nhc']", {
                                        onclick: (e) => {
                                            if (e.target.checked) {
                                                DataProvider.tipoBusqueda = e.target.value;
                                            }
                                        }
                                    }),
                                    m("label.custom-control-label[for='nhc']",
                                        "Historia Clínica"
                                    )
                                ]),

                            ]),
                            m("div.input-group.banenr-seach.bg-white.m-mt-30.mb-0", [
                                m("input.form-control[type='text'][placeholder='Buscar por Apellidos y Nombres, Cédula, Historia Clínica']", {
                                    oninput: function(e) {
                                        e.target.value = e.target.value.toUpperCase();
                                        DataProvider.searchField = e.target.value;
                                    },
                                    value: DataProvider.searchField,
                                }),
                                m("div.input-group-append",
                                    m("i.icofont-close.p-2.mt-1", {

                                        style: { "color": "rgba(108, 117, 125, 0.4) !important", "font-size": "xx-large" },
                                        class: (DataProvider.searchField.length !== 0) ? "" : "d-none",
                                        onclick: () => {
                                            DataProvider.searchField = "";
                                            DataProvider.data = [];
                                            DataProvider.filterData();

                                        },
                                    }),
                                    m("button.btn[type='button'][id='actBuscar']", {

                                            onclick: () => {
                                                DataProvider.fetch();
                                            },
                                        },
                                        "Buscar"
                                    ),

                                )
                            ]),
                        ])
                    ),
                    m("div.row.m-pt-20.m-pb-60.m-mt-20", [
                        m("div.col-12.pd-r-0.pd-l-0.pd-b-20.", [
                            m(dataView),
                            m(pageTool),

                        ])
                    ]),
                    m("div.row.text-center.m-pt-20.m-pb-60.m-mt-20", [

                        m("div.col-12.pd-r-0.pd-l-0.pd-b-20.d-none", [
                            m(".text-primary", {
                                "style": { "cursor": "pointer" }
                            }, [
                                m("i.icofont-unlocked"),
                                " AUTORIZAR IP "
                            ])

                        ]),

                    ])
                )
            ),
            m("div.button-menu-center.text-center",
                m("a.btn.fadeInDown-slide.position-relative.animated.pl-4.pr-4.lsp-0.no-border.bg-transparent.medim-btn.grad-bg--3.solid-btn.mt-0.text-medium.radius-pill.text-active.text-white.s-dp-1-2[href='/']", [
                    m("i.icofont-home"),
                    " Inicio "
                ])
            )
        ];
    },

};


function submitBusqueda() {
    document.onkeypress = function(e) {
        if (!e) e = window.event;
        var keyCode = e.keyCode || e.which;
        if (keyCode == "13") {
            console.log('OK');
            document.getElementById("actBuscar").click();
        }
    };
}

function countWords(str) {
    return str.trim().split(/\s+/).length;
}




export default PageResultados;