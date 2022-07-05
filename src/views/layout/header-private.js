const HeaderPrivate = {
    mom: moment(),
    date: "",
    watch: "",

    oninit: () => {
        HeaderPrivate.mom.locale('es');
        var _t = HeaderPrivate.mom.format('LT');
        if (_t !== HeaderPrivate.watch) {
            HeaderPrivate.watch = HeaderPrivate.mom.format('LT');
        }

    },

    view: () => {

        return [
            m("header",
                m("div..position-relative.set-bg.breadcrumb-container", { "style": { "background-position": "center center", "background-size": "cover", "background-repeat": "no-repeat" } }, [
                    m("div.overlay.op-P9"),
                    m("div.container",
                        m("div.row",
                            m("div.col-md-12", )
                        )
                    )
                ]),
                m("nav.navbar.bg-white.s-dp-1-3.navbar-sticky.type-3.navbar-expand-lg.m-navbar.bcbd_navbar",
                    m("div.container.position-relative", [
                        m("a.navbar-brand[href='/']",
                            m("img[src='assets/logo.metrovirtual.png'][alt='Metrovirtual'][width='200rem']")
                        ),
                        m("div.d-lg-none",
                            m("a", { href: "#!/mi-perfil" },
                                m("div.features-circle.m-bg-3.text-active.d-inline-flex.align-items-center.justify-content-center.rounded-circle", { "style": { "height": "50px", "width": "50px" } },
                                    m("i.icofont-doctor", { "style": { "fontSize": "20px" } })
                                )
                            )
                        ),
                        m(".collapse.navbar-collapse.bcbd_collpase_nav[id='navbarSupportedContent']", [

                            m("div.nav_outer.mr-auto.ml-lg-auto.mr-lg-0", [
                                m("img.d-block.d-md-none[src='assets/images/logo-white.png'][alt='']"),
                                m("ul.navbar-nav.bcbd_nav", [

                                    m("li.nav-item.d-none",

                                        [
                                            m("a.nav-link", [
                                                m("div.watch.text-dark2.text-right", HeaderPrivate.watch),
                                                m("div.watch.text-dark2.text-capitalize", HeaderPrivate.date)
                                            ])
                                        ]
                                    )
                                ]),

                            ]),
                            m("div.p-2.navigation_right_area.position-relative.d-none.d-lg-block.navbar-search.my-lg-0",
                                m("a", { href: "#!/mi-perfil" },
                                    m("div.features-circle.m-bg-3.text-active.d-inline-flex.align-items-center.justify-content-center.rounded-circle", { "style": { "height": "50px", "width": "50px" } },
                                        m("i.icofont-doctor", { "style": { "fontSize": "20px" } })
                                    )
                                )
                            )
                        ])
                    ])
                ),

            ),

        ];
    },

};




export default HeaderPrivate;