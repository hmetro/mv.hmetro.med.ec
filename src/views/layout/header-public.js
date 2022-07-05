const HeadPublic = {
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

                        m(".collapse.navbar-collapse.bcbd_collpase_nav[id='navbarSupportedContent']", [
                            m("div.nav_outer.mr-auto.ml-lg-auto.mr-lg-0", [
                                m("img.d-block.d-md-none[src='assets/images/logo-white.png'][alt='']"),
                                m("ul.navbar-nav.bcbd_nav.mr-lg-4.", [

                                    m("li.nav-item",
                                        m("a.nav-link[href='contact.html']",
                                            " V3.0.0 "
                                        )
                                    )
                                ]),

                            ]),

                        ])
                    ])
                )
            )
        ];
    },

};

export default HeadPublic;