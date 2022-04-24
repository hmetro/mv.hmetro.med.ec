import Auth from '../../models/auth';

const FormLogin = {

    view: () => {


        return [
            m("div.content.content-fixed.content-auth",
                m("div.container",
                    m("div.media.align-items-stretch.justify-content-center.ht-100p.pos-relative", [
                        m("div.media-body.align-items-center.d-none.d-lg-flex", [
                            m("div.mx-wd-600",
                                m("img.img-fluid[src='assets/dashforge/img/img15.png'][alt='']")
                            ),

                        ]),
                        m("div.sign-wrapper.mg-lg-l-50.mg-xl-l-60",
                            m("div.wd-100p", [
                                m("h3.tx-color-01.mg-b-5",
                                    "Entrar"
                                ),
                                m("p.tx-color-03.tx-16.mg-b-40",
                                    "¡Bienvenido! Por favor, inicia sesión para continuar."
                                ),

                                m("div." + Auth.statusHide + ".alert.alert-solid.response.alert-" + Auth.statusError + "[role='alert']",
                                    Auth.messageError
                                ),
                                m("div.form-group", [
                                    m("label",
                                        "Usuario o Correo electrónico:"
                                    ),
                                    m("input.form-control[type='email'][placeholder='mpaez o mpaez@hmetro.med.ec']", {
                                        oninput: function (e) { Auth.setUsername(e.target.value) },
                                        value: Auth.username,
                                        disabled: Auth.imputDisabled,
                                    })
                                ]),
                                m("div.form-group", [
                                    m("div.d-flex.justify-content-between.mg-b-5", [
                                        m("label.mg-b-0-f", "Contraseña:"),
                                    ]),
                                    m("input.form-control[type='password'][placeholder='Contraseña']", {
                                        oninput: function (e) { Auth.setPassword(e.target.value) },
                                        value: Auth.password,
                                        disabled: Auth.imputDisabled,
                                    })
                                ]),
                                m("button.btn.btn-brand-02.btn-block", {
                                    disabled: !Auth.canSubmit() || Auth.imputDisabled,
                                    onclick: Auth.login
                                }, "Entrar"),
                                m("div.divider-text",
                                    "Metrovirtual"
                                ),
                                m("div.tx-13.mg-t-20.tx-center", [
                                    m("a[href='/lostpass']",
                                        "¿Olvide mi contraseña?"
                                    )
                                ]),
                                m("div.tx-13.mg-t-0.tx-center", [
                                    "¿No tienes una cuenta? ",
                                    m("a[href='#!/registro']",
                                        "Regístrate"
                                    )
                                ])


                            ])
                        )
                    ])
                )
            )
        ];
    },

};





export default FormLogin;