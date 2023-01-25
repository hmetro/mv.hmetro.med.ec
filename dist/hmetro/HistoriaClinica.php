<?php

/*
 * This file is part of the Ocrend Framewok 3 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace app\models\v3\hmetro;

use app\models\Pedidos;
use app\models\v3\hmetro as Model;
use Exception;
use Ocrend\Kernel\Helpers as Helper;
use Ocrend\Kernel\Models\IModels;
use Ocrend\Kernel\Models\Models;
use Ocrend\Kernel\Models\ModelsException;
use Ocrend\Kernel\Models\Traits\DBModel;
use Ocrend\Kernel\Router\IRouter;

/**
 * Modelo Odbc GEMA -> Historia clínica
 */

class HistoriaClinica extends Models implements IModels
{
    use DBModel;

    # Variables de clase
    private $historiaClinica = null;
    private $motivoConsulta;
    private $revisionOrganos;
    private $antecedentesFamiliares;
    private $signosVitales;
    private $examenFisico;
    private $diagnosticos;
    private $evoluciones;
    private $prescripciones;
    private $conexion;
    private $numeroHistoriaClinica;
    private $numeroAdmision;
    private $codigoInstitucion = 1;
    private $numeroCompania = '01';
    private $recomendacionesNoFarmacologicas;
    private $tamanioCodigoExamen = 9;
    private $tamanioDescripcionExamen = 120;
    private $pedidosLaboratorio;
    private $pedidosImagen;
    private $start = 0;
    private $length = 10;

    /**
     * Get Auth
     *
     * @var
     */

    private function getAuthorization()
    {

        try {

            global $http;

            $token = $http->headers->get("Authorization");

            $auth = new Model\Auth;
            $data = $auth->GetData($token);

            $this->id_user = $data;

        } catch (ModelsException $e) {
            return array('status' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Asigna los parámetros de entrada
     */
    private function setParameters()
    {
        global $http;

        foreach ($http->request->all() as $key => $value) {
            $this->$key = strtoupper($value);
        }

        foreach ($http->query->all() as $key => $value) {
            $this->$key = $value;
        }

    }

    /**
     * Validar Historia Clinica
     */
    public function validarHC()
    {

        try {

            global $http, $config;

            $this->historiaClinica = $http->request->get('nuevaHistoriaClinica');

            //Antes de asignar todos los datos de la HCL se valida la información
            $this->validarParametrosCrear(json_decode($this->historiaClinica, true));

            return array(
                'status' => true,

            );

        } catch (ModelsException $e) {
            return array(
                'status' => false,
                'message' => $e->getMessage(),

            );
        }

    }

    /**
     * Asigna los parámetros de entrada para la creación de la historia clínica
     */
    private function setParametersCrear()
    {

        global $http, $config;

        $this->historiaClinica = json_decode($http->request->get('nuevaHistoriaClinica'), true);

        //Antes de asignar todos los datos de la HCL se valida la información
        $this->validarParametrosCrear($this->historiaClinica);

        $this->motivoConsulta = $this->historiaClinica['motivoConsulta'];
        $this->revisionOrganos = $this->historiaClinica['revisionOrganos'];
        $this->antecedentesFamiliares = $this->historiaClinica['antecedentesFamiliares'];
        $this->signosVitales = $this->historiaClinica['signosVitales'];
        $this->examenFisico = $this->historiaClinica['examenFisico'];
        $this->diagnosticos = $this->historiaClinica['diagnosticos'];
        $this->evoluciones = $this->historiaClinica['evoluciones'];
        $this->prescripciones = $this->historiaClinica['prescripciones'];
        $this->recomendacionesNoFarmacologicas = $this->historiaClinica['recomendacionesNoFarmacologicas'];
        $this->pedidosLaboratorio = $this->historiaClinica['pedidosLaboratorio'];
        $this->pedidosImagen = $this->historiaClinica['pedidosImagen'];
    }

    /**
     * Valida los parámetros de entrada
     */
    private function validarParametros()
    {
        global $config;

        //Número de historia clínica
        if ($this->numeroHistoriaClinica == null) {
            throw new ModelsException($config['errors']['numeroHistoriaClinicaObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($this->numeroHistoriaClinica)) {
                throw new ModelsException($config['errors']['numeroHistoriaClinicaNumerico']['message'], 1);
            }
        }

        //Número de admisión
        if ($this->numeroAdmision == null) {
            throw new ModelsException($config['errors']['numeroAdmisionObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($this->numeroAdmision)) {
                throw new ModelsException($config['errors']['numeroAdmisionNumerico']['message'], 1);
            }
        }
    }

    /**
     * Valida los parámetros de entrada del enlace al reporte MSP002
     */
    private function validarParametrosEnlaceReporteMSP002()
    {
        global $config;

        //Número de historia clínica
        if ($this->numeroHistoriaClinica == null) {
            throw new ModelsException($config['errors']['numeroHistoriaClinicaObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($this->numeroHistoriaClinica)) {
                throw new ModelsException($config['errors']['numeroHistoriaClinicaNumerico']['message'], 1);
            }
        }

        //Número de admisión
        if ($this->numeroAdmision == null) {
            throw new ModelsException($config['errors']['numeroAdmisionObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($this->numeroAdmision)) {
                throw new ModelsException($config['errors']['numeroAdmisionNumerico']['message'], 1);
            }
        }
    }

    /**
     * Valida los parámetros de entrada para crear una Historia Clínica
     */
    private function validarParametrosCrear($historiaClinica)
    {
        global $config;

        //Viene datos de la HCl
        if (count($historiaClinica) == 0) {
            throw new ModelsException($config['errors']['historiaClinicaObligatorio']['message'], 1);
        }

        //Número de historia clínica
        if ($historiaClinica['numeroHistoriaClinica'] == null) {
            throw new ModelsException($config['errors']['numeroHistoriaClinicaObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($historiaClinica['numeroHistoriaClinica'])) {
                throw new ModelsException($config['errors']['numeroHistoriaClinicaNumerico']['message'], 1);
            }
        }

        //Número de admisión
        if ($historiaClinica['numeroAdmision'] == null) {
            throw new ModelsException($config['errors']['numeroAdmisionObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($historiaClinica['numeroAdmision'])) {
                throw new ModelsException($config['errors']['numeroAdmisionNumerico']['message'], 1);
            }
        }

        //Sección Motivo de consulta  (Obligatorio)
        if (empty($historiaClinica['motivoConsulta'])) {
            throw new ModelsException($config['errors']['seccionMotivoConsultaObligatorio']['message'], 1);
        } else {
            //Motivo de consulta
            if ($historiaClinica['motivoConsulta']['motivoConsulta'] == null and $historiaClinica['motivoConsulta']['antecedentesPersonales'] == null and $historiaClinica['motivoConsulta']['enfermedadActual'] == null and $historiaClinica['motivoConsulta']['alergias'] == null) {
                throw new ModelsException($config['errors']['camposMotivoConsultaObligatorio']['message'], 1);
            }
        }

        //Sección revisión órganos (Opcional)
        if (!array_key_exists('revisionOrganos', $historiaClinica)) {
            throw new ModelsException($config['errors']['seccionRevisionOrganosObligatorio']['message'], 1);
        } else {
            if (count($historiaClinica['revisionOrganos']) > 0) {
                //
                if ($historiaClinica['revisionOrganos']['sentidos'] == null and $historiaClinica['revisionOrganos']['cardioVascular'] == null and $historiaClinica['revisionOrganos']['genital'] == null and $historiaClinica['revisionOrganos']['muscEsqueletico'] == null and $historiaClinica['revisionOrganos']['hemoLinfatico'] == null and $historiaClinica['revisionOrganos']['respiratorio'] == null and $historiaClinica['revisionOrganos']['digestivo'] == null and $historiaClinica['revisionOrganos']['urinario'] == null and $historiaClinica['revisionOrganos']['endocrino'] == null and $historiaClinica['revisionOrganos']['nervioso'] == null) {
                    throw new ModelsException($config['errors']['camposRevisionOrganosObligatorio']['message'], 1);
                }
            }
        }

        //Sección antecedentes familiares (se cambia a obligatorio, 28-dic-2020)
        /*if (!array_key_exists('antecedentesFamiliares', $historiaClinica)){
        throw new ModelsException($config['errors']['seccionAntecedentesFamiliaresObligatorio']['message'], 1);
        } else {
        if (count($historiaClinica['antecedentesFamiliares']) > 0) {
        if ($historiaClinica['antecedentesFamiliares']['cardiopatia'] == null and $historiaClinica['antecedentesFamiliares']['diabetes'] == null and $historiaClinica['antecedentesFamiliares']['enfermedadVascular'] == null and $historiaClinica['antecedentesFamiliares']['hipertension'] == null and $historiaClinica['antecedentesFamiliares']['cancer'] == null and $historiaClinica['antecedentesFamiliares']['tuberculosis'] == null and $historiaClinica['antecedentesFamiliares']['enfermendadMental'] == null and $historiaClinica['antecedentesFamiliares']['enfermedadInfecciosa'] == null and $historiaClinica['antecedentesFamiliares']['malformacion'] == null and $historiaClinica['antecedentesFamiliares']['otro'] == null){
        throw new ModelsException($config['errors']['camposAntecedentesFamiliaresObligatorio']['message'], 1);
        }
        }
        }*/
        if (empty($historiaClinica['antecedentesFamiliares'])) {
            throw new ModelsException($config['errors']['seccionAntecedentesFamiliaresObligatorio']['message'], 1);
        } else {
            if (!array_key_exists('cardiopatia', $historiaClinica['antecedentesFamiliares'])) {
                throw new ModelsException($config['errors']['campoCardiopatiaNoExiste']['message'], 1);
            } else if (!array_key_exists('diabetes', $historiaClinica['antecedentesFamiliares'])) {
                throw new ModelsException($config['errors']['campoDiabetesNoExiste']['message'], 1);
            } else if (!array_key_exists('enfermedadVascular', $historiaClinica['antecedentesFamiliares'])) {
                throw new ModelsException($config['errors']['campoEnfermedadVascularNoExiste']['message'], 1);
            } else if (!array_key_exists('hipertension', $historiaClinica['antecedentesFamiliares'])) {
                throw new ModelsException($config['errors']['campoHipertensionNoExiste']['message'], 1);
            } else if (!array_key_exists('cancer', $historiaClinica['antecedentesFamiliares'])) {
                throw new ModelsException($config['errors']['campoCancerNoExiste']['message'], 1);
            } else if (!array_key_exists('tuberculosis', $historiaClinica['antecedentesFamiliares'])) {
                throw new ModelsException($config['errors']['campoTuberculosisNoExiste']['message'], 1);
            } else if (!array_key_exists('enfermendadMental', $historiaClinica['antecedentesFamiliares'])) {
                throw new ModelsException($config['errors']['campoEnfermendadMentalNoExiste']['message'], 1);
            } else if (!array_key_exists('enfermedadInfecciosa', $historiaClinica['antecedentesFamiliares'])) {
                throw new ModelsException($config['errors']['campoEnfermedadInfecciosaNoExiste']['message'], 1);
            } else if (!array_key_exists('malformacion', $historiaClinica['antecedentesFamiliares'])) {
                throw new ModelsException($config['errors']['campoMalformacionNoExiste']['message'], 1);
            } else if (!array_key_exists('otro', $historiaClinica['antecedentesFamiliares'])) {
                throw new ModelsException($config['errors']['campoOtroNoExiste']['message'], 1);
            }
        }

        //Sección signos vitales (Opcional)
        if (!array_key_exists('signosVitales', $historiaClinica)) {
            throw new ModelsException($config['errors']['seccionSignosVitalesObligatorio']['message'], 1);
        } else {
            foreach ($historiaClinica['signosVitales'] as $signoVital) {
                //Valida que al menos un campo sea obligatorio
                if ($signoVital['fecha'] == null) {
                    throw new ModelsException($config['errors']['fechaSignosVitalesObligatorio']['message'], 1);
                } else if ($signoVital['temperaturaBucal'] == null and $signoVital['temperaturaAxilar'] == null and $signoVital['temperaturaRectal'] == null and $signoVital['taSistolica'] == null and $signoVital['taDiastolica'] == null and $signoVital['pulso'] == null and $signoVital['frecuenciaRespiratoria'] == null and $signoVital['perimetroCef'] == null and $signoVital['peso'] == null and $signoVital['talla'] == null and $signoVital['imc'] == null) {
                    throw new ModelsException($config['errors']['camposSignosVitalesObligatorio']['message'], 1);
                } else {
                    //Fecha

                    //Temperatura bucal
                    if ($signoVital['temperaturaBucal'] != null and !is_numeric($signoVital['temperaturaBucal'])) {
                        throw new ModelsException($config['errors']['temperaturaBucalNumerico']['message'], 1);
                    }

                    //Temperatura axilar
                    if ($signoVital['temperaturaAxilar'] != null and !is_numeric($signoVital['temperaturaAxilar'])) {
                        throw new ModelsException($config['errors']['temperaturaAxilarNumerico']['message'], 1);
                    }

                    //Temperatura rectal
                    if ($signoVital['temperaturaRectal'] != null and !is_numeric($signoVital['temperaturaRectal'])) {
                        throw new ModelsException($config['errors']['temperaturaRectalNumerico']['message'], 1);
                    }

                    //ta sistolica
                    if ($signoVital['taSistolica'] != null and !is_numeric($signoVital['taSistolica'])) {
                        throw new ModelsException($config['errors']['taSistolicaNumerico']['message'], 1);
                    }

                    //ta diastolica
                    if ($signoVital['taDiastolica'] != null and !is_numeric($signoVital['taDiastolica'])) {
                        throw new ModelsException($config['errors']['taDiastolicaNumerico']['message'], 1);
                    }

                    //Pulso
                    if ($signoVital['pulso'] != null and !is_numeric($signoVital['pulso'])) {
                        throw new ModelsException($config['errors']['pulsoNumerico']['message'], 1);
                    }

                    //Frecuencia respiratoria
                    if ($signoVital['frecuenciaRespiratoria'] != null and !is_numeric($signoVital['frecuenciaRespiratoria'])) {
                        throw new ModelsException($config['errors']['frecuenciaRespiratoriaNumerico']['message'], 1);
                    }

                    //Perímetro CEF
                    if ($signoVital['perimetroCef'] != null and !is_numeric($signoVital['perimetroCef'])) {
                        throw new ModelsException($config['errors']['perimetroCefNumerico']['message'], 1);
                    }

                    //Peso
                    if ($signoVital['peso'] != null and !is_numeric($signoVital['peso'])) {
                        throw new ModelsException($config['errors']['pesoNumerico']['message'], 1);
                    }

                    //Talla
                    if ($signoVital['talla'] != null and !is_numeric($signoVital['talla'])) {
                        throw new ModelsException($config['errors']['tallaNumerico']['message'], 1);
                    }

                    //IMC
                    /*if ($signoVital['imc'] != null and !is_numeric($signoVital['imc'])){
                throw new ModelsException($config['errors']['imcNumerico']['message'], 1);
                }*/
                }
            }
        }

        //Sección examen físico (Opcional)
        if (!array_key_exists('examenFisico', $historiaClinica)) {
            throw new ModelsException($config['errors']['seccionExamenFisicoObligatorio']['message'], 1);
        } else {
            if (count($historiaClinica['examenFisico']) > 0) {
                if ($historiaClinica['examenFisico']['cabeza1R'] == null and $historiaClinica['examenFisico']['cuello2R'] == null and $historiaClinica['examenFisico']['torax3R'] == null and $historiaClinica['examenFisico']['abdomen4R'] == null and $historiaClinica['examenFisico']['pelvis5R'] == null and $historiaClinica['examenFisico']['extremidades6R'] == null and $historiaClinica['examenFisico']['planTratamiento'] == null) {
                    throw new ModelsException($config['errors']['camposExamenFisicoObligatorio']['message'], 1);
                }
            }
        }

        //Sección diagnósticos (Obligatorio)
        if (empty($historiaClinica['diagnosticos'])) {
            throw new ModelsException($config['errors']['seccionDiagnosticosObligatorio']['message'], 1);
        } else {

            $masDeUnPrincipal = 0;

            foreach ($historiaClinica['diagnosticos'] as $diagnostico) {
                //Código
                if ($diagnostico['codigo'] == null) {
                    throw new ModelsException($config['errors']['codigoDiagnosticoObligatorio']['message'], 1);
                }

                //Clasificacion
                /*if ($diagnostico['clasificacionDiagnostico'] == null){
                throw new ModelsException($config['errors']['clasificacionDiagnosticoObligatorio']['message'], 1);
                }*/

                //Principal
                if ($diagnostico['principal'] == null) {
                    throw new ModelsException($config['errors']['principalDiagnosticoObligatorio']['message'], 1);
                } else {
                    if ($diagnostico['principal'] == 'S') {
                        $masDeUnPrincipal += 1;
                    }

                    //Valida que no exista más de un principal
                    if ($masDeUnPrincipal > 1) {
                        throw new ModelsException($config['errors']['principalDiagnosticoMasDeUno']['message'], 1);
                    }
                }

                //Grupo
                if ($diagnostico['grupo'] == null) {
                    throw new ModelsException($config['errors']['grupoDiagnosticoObligatorio']['message'], 1);
                }
            }
        }

        //Sección evoluciones
        if (!array_key_exists('evoluciones', $historiaClinica)) {
            throw new ModelsException($config['errors']['seccionEvolucionesObligatorio']['message'], 1);
        } else {
            if (count($historiaClinica['evoluciones']) > 0) {
                //Descripción evolución
                if ($historiaClinica['evoluciones']['descripcion'] == null) {
                    throw new ModelsException($config['errors']['descripcionEvolucionObligatorio']['message'], 1);
                }
            }
        }

        //Sección prescripciones (opcional)
        if (!array_key_exists('prescripciones', $historiaClinica)) {
            throw new ModelsException($config['errors']['seccionPrescripcionesObligatorio']['message'], 1);
        } else {
            foreach ($historiaClinica['prescripciones'] as $prescripcion) {
                //Descripción prescripción
                if ($prescripcion['medicamento'] == null) {
                    throw new ModelsException($config['errors']['medicamentoPrescripcionObligatorio']['message'], 1);
                }

                //Cantidad comprar
                if ($prescripcion['cantidadComprar'] == null) {
                    throw new ModelsException($config['errors']['cantidadComprarPrescripcionObligatorio']['message'], 1);
                } else {
                    //Valida que sea numérico el ingreso de datos
                    /*if (!is_numeric($prescripcion['cantidadComprar'])) {
                throw new ModelsException($config['errors']['cantidadComprarPrescripcionNumerico']['message'], 1);
                }*/
                }

                //Unidad comprar
                if ($prescripcion['unidadComprar'] == null) {
                    throw new ModelsException($config['errors']['unidadComprarPrescripcionObligatorio']['message'], 1);
                }

                //Cantidad tomar
                if ($prescripcion['cantidadTomar'] == null) {
                    throw new ModelsException($config['errors']['cantidadTomarPrescripcionObligatorio']['message'], 1);
                } else {
                    //Valida que sea numérico el ingreso de datos
                    /*if (!is_numeric($prescripcion['cantidadTomar'])) {
                throw new ModelsException($config['errors']['cantidadTomarPrescripcionNumerico']['message'], 1);
                }*/
                }

                //Unidad tomar
                if ($prescripcion['unidadTomar'] == null) {
                    throw new ModelsException($config['errors']['unidadTomarPrescripcionObligatorio']['message'], 1);
                }

                //Frecuencia dosis
                if ($prescripcion['frecuenciaDosis'] == null) {
                    throw new ModelsException($config['errors']['frecuenciaDosisPrescripcionObligatorio']['message'], 1);
                } else {
                    if (!is_numeric($prescripcion['frecuenciaDosis'])) {
                        throw new ModelsException($config['errors']['frecuenciaDosisPrescripcionNumerico']['message'], 1);
                    }
                }

                //Frecuencia total
                if ($prescripcion['frecuenciaTotal'] != null) {
                    if (!is_numeric($prescripcion['frecuenciaTotal'])) {
                        throw new ModelsException($config['errors']['frecuenciaTotalPrescripcionNumerico']['message'], 1);
                    }
                }

                //Unidad dosis
                if ($prescripcion['unidadDosis'] == null) {
                    throw new ModelsException($config['errors']['unidadDosisPrescripcionObligatorio']['message'], 1);
                }

                //Vía administración
                if ($prescripcion['viaAdministracion'] == null) {
                    throw new ModelsException($config['errors']['viaAdministracionPrescripcionObligatorio']['message'], 1);
                }
            }
        }

        //Sección recomendaciones no farmacologicas (opcional)
        if (!array_key_exists('recomendacionesNoFarmacologicas', $historiaClinica)) {
            throw new ModelsException($config['errors']['seccionRecomendacionesNoFarmacologicasObligatorio']['message'], 1);
        } else {

            if (count($historiaClinica['recomendacionesNoFarmacologicas']) > 0) {
                if ($historiaClinica['recomendacionesNoFarmacologicas']['descripcion'] == null) {
                    throw new ModelsException($config['errors']['descripcionRecomendacionNoFarmacologicaObligatorio']['message'], 1);
                }
            }
        }

        //Sección pedidos laboratorio (opcional)
        if (!array_key_exists('pedidosLaboratorio', $historiaClinica)) {
            throw new ModelsException($config['errors']['seccionPedidosLaboratorioObligatorio']['message'], 1);
        } else {
            foreach ($historiaClinica['pedidosLaboratorio'] as $pedidoLaboratorio) {
                //Código
                if ($pedidoLaboratorio['codigoExamen'] == null) {
                    throw new ModelsException($config['errors']['codigoExamenObligatorio']['message'], 1);
                } else {
                    //Validaciones de tipo de datos y rangos permitidos
                    if (strlen($pedidoLaboratorio['codigoExamen']) > $this->tamanioCodigoExamen) {
                        throw new ModelsException($config['errors']['codigoExamenTamanio']['message'], 1);
                    }
                }

                //Descripción
                if ($pedidoLaboratorio['descripcionExamen'] == null) {
                    throw new ModelsException($config['errors']['descripcionExamenObligatorio']['message'], 1);
                } else {
                    //Validaciones de tipo de datos y rangos permitidos
                    if (strlen($pedidoLaboratorio['descripcionExamen']) > $this->tamanioDescripcionExamen) {
                        throw new ModelsException($config['errors']['descripcionExamenTamanio']['message'], 1);
                    }
                }

                //Precio venta al público
                if ($pedidoLaboratorio['precioVentaPublico'] == null) {
                    throw new ModelsException($config['errors']['precioVentaPublicoObligatorio']['message'], 1);
                }

            }
        }

        //Sección pedidos imagen (opcional)
        if (!array_key_exists('pedidosImagen', $historiaClinica)) {
            throw new ModelsException($config['errors']['seccionPedidosImagenObligatorio']['message'], 1);
        } else {
            foreach ($historiaClinica['pedidosImagen'] as $pedidoImagen) {
                //Código
                if ($pedidoImagen['codigoExamen'] == null) {
                    throw new ModelsException($config['errors']['codigoExamenObligatorio']['message'], 1);
                } else {
                    //Validaciones de tipo de datos y rangos permitidos
                    if (strlen($pedidoImagen['codigoExamen']) > $this->tamanioCodigoExamen) {
                        throw new ModelsException($config['errors']['codigoExamenTamanio']['message'], 1);
                    }
                }

                //Descripción
                if ($pedidoImagen['descripcionExamen'] == null) {
                    throw new ModelsException($config['errors']['descripcionExamenObligatorio']['message'], 1);
                } else {
                    //Validaciones de tipo de datos y rangos permitidos
                    if (strlen($pedidoImagen['descripcionExamen']) > $this->tamanioDescripcionExamen) {
                        throw new ModelsException($config['errors']['descripcionExamenTamanio']['message'], 1);
                    }
                }

                //Precio venta al público
                if ($pedidoImagen['precioVentaPublico'] == null) {
                    throw new ModelsException($config['errors']['precioVentaPublicoObligatorio']['message'], 1);
                }

            }
        }
    }

    private function setSpanishOracle($stid)
    {

        $sql = "alter session set NLS_LANGUAGE = 'SPANISH'";
        # Execute
        $stid = oci_parse($this->conexion->getConexion(), $sql);
        oci_execute($stid);

        $sql = "alter session set NLS_TERRITORY = 'SPAIN'";
        # Execute
        $stid = oci_parse($this->conexion->getConexion(), $sql);
        oci_execute($stid);

        $sql = " alter session set NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI'";
        # Execute
        $stid = oci_parse($this->conexion->getConexion(), $sql);
        oci_execute($stid);

    }

    /**
     * Permite registrar un nuevo paciente
     */
    public function crear()
    {
        global $config;

        //Inicialización de variables
        $stmt = null;
        $codigoRetorno = null;
        $mensajeRetorno = null;
        $r = false;
        $rCommit = false;
        $codigoError = -1;
        $mensajeError;

        try {

            //Asigna y valida los parámetro de entrada
            $this->setParametersCrear();

            # return $this->historiaClinica;

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stmt);

            $stmt = oci_parse($this->conexion->getConexion(), 'BEGIN PRO_TEL_CEA_HC_INS(:pn_hc, :pn_adm, :pn_institucion, :pc_motivo_consulta_1, :pc_antecedentes_personales, :pc_enfermedad_actual, :pc_or_sentidos, :pc_or_cardio_vascular, :pc_or_genital, :pc_or_musc_esqueletico, :pc_or_hemo_linfatico, :pc_or_respiratorio, :pc_or_digestivo, :pc_or_urinario, :pc_or_endocrino, :pc_or_nervioso, :pc_alergias, :pc_cardiopatia, :pc_diabetes, :pc_enf_vascular, :pc_hipertension, :pc_cancer, :pc_tuberculosis, :pc_enf_mental, :pc_enf_infecciosa, :pc_malformacion, :pc_otro, :pc_cabeza_1r, :pc_cuello_2r, :pc_torax_3r, :pc_abdomen_4r, :pc_pelvis_5r, :pc_extremidades_6r, :pc_plan_tratamiento, :pc_descripcion_evol, :pn_retorno, :pc_mensaje); END;');

            // Bind the input parameter
            oci_bind_by_name($stmt, ':pn_hc', $this->historiaClinica['numeroHistoriaClinica'], 32);
            oci_bind_by_name($stmt, ':pn_adm', $this->historiaClinica['numeroAdmision'], 32);
            oci_bind_by_name($stmt, ':pn_institucion', $this->codigoInstitucion, 2);

            //Motivo de consulta
            oci_bind_by_name($stmt, ':pc_motivo_consulta_1', $this->motivoConsulta['motivoConsulta'], 300);
            oci_bind_by_name($stmt, ':pc_antecedentes_personales', $this->motivoConsulta['antecedentesPersonales'], 4000);
            oci_bind_by_name($stmt, ':pc_enfermedad_actual', $this->motivoConsulta['enfermedadActual'], 4000);
            oci_bind_by_name($stmt, ':pc_alergias', $this->motivoConsulta['alergias'], 200);

            //Revisión de órganos
            //
            $roSentidos = null;
            $roCardioVascular = null;
            $roGenital = null;
            $roMuscEsqueletico = null;
            $roHemoLinfatico = null;
            $roRespiratorio = null;
            $roDigestivo = null;
            $roUrinario = null;
            $roEndocrino = null;
            $roNervioso = null;

            if (count($this->revisionOrganos) > 0) {
                $roSentidos = $this->revisionOrganos['sentidos'];
                $roCardioVascular = $this->revisionOrganos['cardioVascular'];
                $roGenital = $this->revisionOrganos['genital'];
                $roMuscEsqueletico = $this->revisionOrganos['muscEsqueletico'];
                $roHemoLinfatico = $this->revisionOrganos['hemoLinfatico'];
                $roRespiratorio = $this->revisionOrganos['respiratorio'];
                $roDigestivo = $this->revisionOrganos['digestivo'];
                $roUrinario = $this->revisionOrganos['urinario'];
                $roEndocrino = $this->revisionOrganos['endocrino'];
                $roNervioso = $this->revisionOrganos['nervioso'];
            }

            oci_bind_by_name($stmt, ':pc_or_sentidos', $roSentidos, 4000);
            oci_bind_by_name($stmt, ':pc_or_cardio_vascular', $roCardioVascular, 4000);
            oci_bind_by_name($stmt, ':pc_or_genital', $roGenital, 4000);
            oci_bind_by_name($stmt, ':pc_or_musc_esqueletico', $roMuscEsqueletico, 4000);
            oci_bind_by_name($stmt, ':pc_or_hemo_linfatico', $roHemoLinfatico, 4000);
            oci_bind_by_name($stmt, ':pc_or_respiratorio', $roRespiratorio, 4000);
            oci_bind_by_name($stmt, ':pc_or_digestivo', $roDigestivo, 4000);
            oci_bind_by_name($stmt, ':pc_or_urinario', $roUrinario, 4000);
            oci_bind_by_name($stmt, ':pc_or_endocrino', $roEndocrino, 4000);
            oci_bind_by_name($stmt, ':pc_or_nervioso', $roNervioso, 4000);

            //Antecedentes familiares
            $afCardiopatia = $this->antecedentesFamiliares['cardiopatia'];
            $afDiabetes = $this->antecedentesFamiliares['diabetes'];
            $afEnfermedadVascular = $this->antecedentesFamiliares['enfermedadVascular'];
            $afHipertension = $this->antecedentesFamiliares['hipertension'];
            $afCancer = $this->antecedentesFamiliares['cancer'];
            $afTuberculosis = $this->antecedentesFamiliares['tuberculosis'];
            $afEnfermendadMental = $this->antecedentesFamiliares['enfermendadMental'];
            $afEnfermedadInfecciosa = $this->antecedentesFamiliares['enfermedadInfecciosa'];
            $afMalformacion = $this->antecedentesFamiliares['malformacion'];
            $afOtro = $this->antecedentesFamiliares['otro'];

            oci_bind_by_name($stmt, ':pc_cardiopatia', $afCardiopatia, 4000);
            oci_bind_by_name($stmt, ':pc_diabetes', $afDiabetes, 4000);
            oci_bind_by_name($stmt, ':pc_enf_vascular', $afEnfermedadVascular, 4000);
            oci_bind_by_name($stmt, ':pc_hipertension', $afHipertension, 4000);
            oci_bind_by_name($stmt, ':pc_cancer', $afCancer, 4000);
            oci_bind_by_name($stmt, ':pc_tuberculosis', $afTuberculosis, 4000);
            oci_bind_by_name($stmt, ':pc_enf_mental', $afEnfermendadMental, 4000);
            oci_bind_by_name($stmt, ':pc_enf_infecciosa', $afEnfermedadInfecciosa, 4000);
            oci_bind_by_name($stmt, ':pc_malformacion', $afMalformacion, 4000);
            oci_bind_by_name($stmt, ':pc_otro', $afOtro, 4000);

            //Examen físico
            $efCabeza1R = null;
            $efCuello2R = null;
            $efTorax3R = null;
            $efAbdomen4R = null;
            $efPelvis5R = null;
            $efExtremidades6R = null;
            $efPlanTratamiento = null;

            if (count($this->examenFisico) > 0) {
                $efCabeza1R = $this->examenFisico['cabeza1R'];
                $efCuello2R = $this->examenFisico['cuello2R'];
                $efTorax3R = $this->examenFisico['torax3R'];
                $efAbdomen4R = $this->examenFisico['abdomen4R'];
                $efPelvis5R = $this->examenFisico['pelvis5R'];
                $efExtremidades6R = $this->examenFisico['extremidades6R'];
                $efPlanTratamiento = $this->examenFisico['planTratamiento'];
            }

            oci_bind_by_name($stmt, ':pc_cabeza_1r', $efCabeza1R, 4000);
            oci_bind_by_name($stmt, ':pc_cuello_2r', $efCuello2R, 4000);
            oci_bind_by_name($stmt, ':pc_torax_3r', $efTorax3R, 4000);
            oci_bind_by_name($stmt, ':pc_abdomen_4r', $efAbdomen4R, 4000);
            oci_bind_by_name($stmt, ':pc_pelvis_5r', $efPelvis5R, 4000);
            oci_bind_by_name($stmt, ':pc_extremidades_6r', $efExtremidades6R, 4000);
            oci_bind_by_name($stmt, ':pc_plan_tratamiento', $efPlanTratamiento, 4000);

            //Evoluciones
            $evolDescripcion = null;

            if (count($this->evoluciones) > 0) {
                $evolDescripcion = $this->evoluciones['descripcion'];
            }

            oci_bind_by_name($stmt, ':pc_descripcion_evol', $evolDescripcion, 2000);

            // Bind the output parameter
            oci_bind_by_name($stmt, ':pn_retorno', $codigoRetorno, 32);
            oci_bind_by_name($stmt, ':pc_mensaje', $mensajeRetorno, 500);

            $r = oci_execute($stmt, OCI_DEFAULT);

            //Inserta los signos vitales
            foreach ($this->signosVitales as $signoVital) {

                $this->insertarSignoVital($signoVital, $this->historiaClinica['numeroHistoriaClinica'], $this->historiaClinica['numeroAdmision'], $this->codigoInstitucion, $stmt, $this->conexion->getConexion());
            }

            //Diagnosticos
            foreach ($this->diagnosticos as $diagnostico) {

                $this->insertarDiagnostico($diagnostico, $this->historiaClinica['numeroHistoriaClinica'], $this->historiaClinica['numeroAdmision'], $this->codigoInstitucion, $stmt, $this->conexion->getConexion());
            }

            //Prescripciones (opcional)
            foreach ($this->prescripciones as $prescripcion) {

                $this->insertarPrescripcion($prescripcion, $this->historiaClinica['numeroHistoriaClinica'], $this->historiaClinica['numeroAdmision'], $this->codigoInstitucion, $this->numeroCompania, $stmt, $this->conexion->getConexion());

            }

            //Recomendaciones No Farmacológicas (opcional)
            if (count($this->recomendacionesNoFarmacologicas) > 0) {
                $this->insertarRecomendacionesNoFarmacologicas($this->recomendacionesNoFarmacologicas, $this->historiaClinica['numeroHistoriaClinica'], $this->historiaClinica['numeroAdmision'], $this->codigoInstitucion, $stmt, $this->conexion->getConexion());
            }

            /*
            //Pedidos de laboratorio
            $pedido = new Pedidos;

            foreach ($this->pedidosLaboratorio as $pedidoLaboratorio) {

            $pedido->insertarPedidoLaboratorio($pedidoLaboratorio, $this->historiaClinica['numeroHistoriaClinica'], $this->historiaClinica['numeroAdmision'], $this->codigoInstitucion, $this->numeroCompania, $stmt, $this->conexion->getConexion());
            }

            //Pedidos de Imagen
            foreach ($this->pedidosImagen as $pedidoImagen) {

            $pedido->insertarPedidoImagen($pedidoImagen, $this->historiaClinica['numeroHistoriaClinica'], $this->historiaClinica['numeroAdmision'], $this->codigoInstitucion, $this->numeroCompania, $stmt, $this->conexion->getConexion());
            }

             */

            $rCommit = oci_commit($this->conexion->getConexion());

            $statusHC = array(
                'statusHC' => 2,
            );

            $this->db->update('atencionesHMQ', $statusHC, " numeroHistoriaClinica='" . $this->historiaClinica['numeroHistoriaClinica'] . "' AND numeroAdmision='" . $this->historiaClinica['numeroAdmision'] . "'     ", 1);

            return array(
                'status' => true,
                'data' => [],
                'message' => $mensajeRetorno
            );

        } catch (ModelsException $e) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $e->getMessage(),
                'errorCode' => $e->getCode()
            );

        } catch (Exception $ex) {
            //
            $mensajeError = $ex->getMessage();

            //Error al insertar en la tabla principal
            if (!$r) {
                $e = oci_error($stmt);

                $mensajeError = "Error al insertar los datos en la Historia Clínica, consulte con el Administrador del Sistema. " . $e['message'];

                //Verifica los mensajes de error del Oracle
                //Llave primaria duplicada
                if ($e['code'] == 1) {
                    $mensajeError = "Historia Clínica ya existe";
                }
            }

            return array(
                'status' => false,
                'data' => [],
                'message' => $mensajeError,
                'errorCode' => $codigoError
            );

        } finally {
            //Libera recursos de conexión
            if ($stmt != null) {
                oci_free_statement($stmt);
            }

            //Cierra la conexión
            $this->conexion->cerrar();
        }

    }

    /**
     * Inserta prescripción
     */
    private function insertarPrescripcion($prescripcion, $numeroHistoriaClinica, $numeroAdmision, $codigoInstitucion, $numeroCompania, $stid, $conexion1)
    {
        //Inicialización de variables
        $r = false;
        $codigoError = -1;
        $mensajeError;

        try {
            //Setear idioma y formatos en español para Oracle
            //$this->setSpanishOracle($stid);

            $stid = oci_parse($conexion1, "BEGIN PRO_TEL_RECETA_INS(:pn_hc, :pn_adm, :pn_institucion, :pc_cia_naf,  :pc_desc_medicamento, :pn_cant_comprar, :pc_unid_comprar, :pn_cant_tomar, :pc_unid_tomar, :pn_frec_dosis, :pc_unid_dosis, :pn_frec_total, :pc_unid_total, :pc_indicacion, :pn_via_administracion, :pn_retorno, :pc_mensaje); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $numeroAdmision, 32);
            oci_bind_by_name($stid, ":pn_institucion", $codigoInstitucion, 32);
            oci_bind_by_name($stid, ":pc_cia_naf", $numeroCompania, 2);

            oci_bind_by_name($stid, ":pc_desc_medicamento", $prescripcion['medicamento'], 120);
            oci_bind_by_name($stid, ":pn_cant_comprar", $prescripcion['cantidadComprar'], 32);
            oci_bind_by_name($stid, ":pc_unid_comprar", $prescripcion['unidadComprar'], 2);
            oci_bind_by_name($stid, ":pn_cant_tomar", $prescripcion['cantidadTomar'], 32);
            oci_bind_by_name($stid, ":pc_unid_tomar", $prescripcion['unidadTomar'], 2);
            oci_bind_by_name($stid, ":pn_frec_dosis", $prescripcion['frecuenciaDosis'], 32);
            oci_bind_by_name($stid, ":pc_unid_dosis", $prescripcion['unidadDosis'], 2);
            oci_bind_by_name($stid, ":pn_frec_total", $prescripcion['frecuenciaTotal'], 32);
            oci_bind_by_name($stid, ":pc_unid_total", $prescripcion['unidadTotal'], 2);
            oci_bind_by_name($stid, ":pc_indicacion", $prescripcion['indicacion'], 120);
            oci_bind_by_name($stid, ":pn_via_administracion", $prescripcion['viaAdministracion'], 32);

            // Bind the output parameter
            oci_bind_by_name($stid, ':pn_retorno', $codigoRetorno, 32);
            oci_bind_by_name($stid, ':pc_mensaje', $mensajeRetorno, 500);

            //Ejecuta el SP
            $r = oci_execute($stid, OCI_DEFAULT);

        } catch (Exception $ex) {

            if (!$r) {
                $e = oci_error($stid);

                $mensajeError = "Error al insertar los datos de las prescripciones, consulte con el Administrador del Sistema. " . $e['message'];

                //Verifica los mensajes de error del Oracle
                //Llave primaria duplicada
                if ($e['code'] == 1) {
                    $mensajeError = "Prescripción ya existe.";
                }

                oci_rollback($conexion1);
                throw new Exception($mensajeError, $codigoError);
            }

        }
    }

    /**
     * Inserta recomendaciones no farmacologicas
     */
    private function insertarRecomendacionesNoFarmacologicas($recomendaciones, $numeroHistoriaClinica, $numeroAdmision, $codigoInstitucion, $stid, $conexion1)
    {
        //Inicialización de variables
        $r = false;
        $codigoError = -1;
        $mensajeError;

        try {
            //Setear idioma y formatos en español para Oracle
            //$this->setSpanishOracle($stid);

            $stid = oci_parse($conexion1, "BEGIN PRO_TEL_CEA_PRESCRIP_INS(:pn_hc, :pn_adm, :pn_institucion, :pc_descripcion, :pn_retorno, :pc_mensaje); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $numeroAdmision, 32);
            oci_bind_by_name($stid, ":pn_institucion", $codigoInstitucion, 32);
            oci_bind_by_name($stid, ":pc_descripcion", $recomendaciones['descripcion'], 4000);

            // Bind the output parameter
            oci_bind_by_name($stid, ':pn_retorno', $codigoRetorno, 32);
            oci_bind_by_name($stid, ':pc_mensaje', $mensajeRetorno, 500);

            //Ejecuta el SP
            $r = oci_execute($stid, OCI_DEFAULT);

        } catch (Exception $ex) {

            if (!$r) {
                $e = oci_error($stid);

                $mensajeError = "Error al insertar los datos de las recomendaciones no farmacológicas, consulte con el Administrador del Sistema. " . $e['message'];

                //Verifica los mensajes de error del Oracle
                //Llave primaria duplicada
                if ($e['code'] == 1) {
                    $mensajeError = "Recomendación no farmacológica ya existe.";
                }

                oci_rollback($conexion1);
                throw new Exception($mensajeError, $codigoError);
            }

        }
    }

    /**
     * Inserta el signo vital
     */
    private function insertarSignoVital($signoVital, $numeroHistoriaClinica, $numeroAdmision, $codigoInstitucion, $stid, $conexion1)
    {
        //Inicialización de variables
        $r = false;
        $codigoError = -1;
        $mensajeError;

        try {
            //Setear idioma y formatos en español para Oracle
            //$this->setSpanishOracle($stid);

            $stid = oci_parse($conexion1, "BEGIN PRO_TEL_CEA_SIGNOS_VIT_INS(:pn_hc, :pn_adm, :pn_institucion, :pd_fecha, :pn_temp_bucal, :pn_temp_axilar, :pn_temp_rectal, :pn_ta_sistolica, :pn_ta_diastolica, :pn_pulso, :pn_frecuencia_resp, :pn_perimetro_cef, :pn_peso, :pn_talla, :pn_imc, :pn_retorno, :pc_mensaje); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $numeroAdmision, 32);
            oci_bind_by_name($stid, ":pn_institucion", $codigoInstitucion, 32);
            oci_bind_by_name($stid, ":pd_fecha", $signoVital['fecha'], 32);

            oci_bind_by_name($stid, ":pn_temp_bucal", $signoVital['temperaturaBucal'], 32);
            oci_bind_by_name($stid, ":pn_temp_axilar", $signoVital['temperaturaAxilar'], 32);
            oci_bind_by_name($stid, ":pn_temp_rectal", $signoVital['temperaturaRectal'], 32);
            oci_bind_by_name($stid, ":pn_ta_sistolica", $signoVital['taSistolica'], 32);
            oci_bind_by_name($stid, ":pn_ta_diastolica", $signoVital['taDiastolica'], 32);
            oci_bind_by_name($stid, ":pn_pulso", $signoVital['pulso'], 32);
            oci_bind_by_name($stid, ":pn_frecuencia_resp", $signoVital['frecuenciaRespiratoria'], 32);
            oci_bind_by_name($stid, ":pn_perimetro_cef", $signoVital['perimetroCef'], 32);
            oci_bind_by_name($stid, ":pn_peso", $signoVital['peso'], 32);
            oci_bind_by_name($stid, ":pn_talla", $signoVital['talla'], 32);
            oci_bind_by_name($stid, ":pn_imc", $signoVital['imc'], 32);

            // Bind the output parameter
            oci_bind_by_name($stid, ':pn_retorno', $codigoRetorno, 32);
            oci_bind_by_name($stid, ':pc_mensaje', $mensajeRetorno, 500);

            //Ejecuta el SP
            $r = oci_execute($stid, OCI_DEFAULT);

        } catch (Exception $ex) {

            if (!$r) {
                $e = oci_error($stid);

                $mensajeError = "Error al insertar los datos de los signos vitales, consulte con el Administrador del Sistema. " . $e['message'];

                //Verifica los mensajes de error del Oracle
                //Llave primaria duplicada
                if ($e['code'] == 1) {
                    $mensajeError = "Signos vitales ya existe.";
                }

                oci_rollback($conexion1);
                throw new Exception($mensajeError, $codigoError);
            }

        }
    }

    /**
     * Inserta el diagnostico
     */
    private function insertarDiagnostico($diagnostico, $numeroHistoriaClinica, $numeroAdmision, $codigoInstitucion, $stid, $conexion1)
    {
        //Inicialización de variables
        $r = false;
        $codigoError = -1;
        $mensajeError;
        // Tipo de diagnóstico no tiene dato ni de presuntivo ni definitivo
        $tipoDiagnostico = '';
        // Clasificación diagnóstico es siempre egreso 'E'
        $clasificacionDiagnostico = 'E';

        try {
            //Setear idioma y formatos en español para Oracle
            //$this->setSpanishOracle($stid);

            $stid = oci_parse($conexion1, "BEGIN PRO_TEL_CEA_DIAG_INS(:pn_hc, :pn_adm, :pn_institucion, :pc_cod_diagnostico, :pc_grupo_diagnostico, :pc_tipo, :pc_clasificacion_diagnostico, :pc_principal, :pn_retorno, :pc_mensaje); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $numeroAdmision, 32);
            oci_bind_by_name($stid, ":pn_institucion", $codigoInstitucion, 32);

            oci_bind_by_name($stid, ":pc_cod_diagnostico", $diagnostico['codigo'], 32);
            oci_bind_by_name($stid, ":pc_grupo_diagnostico", $diagnostico['grupo'], 32);
            oci_bind_by_name($stid, ":pc_tipo", $tipoDiagnostico, 1);
            oci_bind_by_name($stid, ":pc_clasificacion_diagnostico", $clasificacionDiagnostico, 1);
            oci_bind_by_name($stid, ":pc_principal", $diagnostico['principal'], 1);

            // Bind the output parameter
            oci_bind_by_name($stid, ':pn_retorno', $codigoRetorno, 32);
            oci_bind_by_name($stid, ':pc_mensaje', $mensajeRetorno, 500);

            //Ejecuta el SP
            $r = oci_execute($stid, OCI_DEFAULT);

        } catch (Exception $ex) {

            if (!$r) {
                $e = oci_error($stid);

                $mensajeError = "Error al insertar los datos de los diagnósticos, consulte con el Administrador del Sistema. " . $e['message'];

                //Verifica los mensajes de error del Oracle
                //Llave primaria duplicada
                if ($e['code'] == 1) {
                    $mensajeError = "Diagnósticos ya existe.";
                }

                oci_rollback($conexion1);
                throw new Exception($mensajeError, $codigoError);
            }

        }
    }

    /**
     * Consulta el listado de Historias Clínicas anteriores
     */
    public function consultarHistoriasClinicasAnteriores()
    {
        global $config, $http;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $historiasClinicasAnteriores[] = null;
        $registraHistoriaClinica;

        try {
            //Asignar parámetros de entrada
            # $this->setParameters();

            $this->getAuthorization();

            $this->numeroHistoriaClinica = $http->query->get('numeroHistoriaClinica');
            $this->length = $http->query->get('length');
            $this->start = $http->query->get('start');

            if ($this->start >= 10) {
                $this->length = $this->start + 10;
            }

            //Validar parámetros de entrada
            //Número de historia clínica
            if ($this->numeroHistoriaClinica == null) {
                throw new ModelsException($config['errors']['numeroHistoriaClinicaObligatorio']['message'], 1);
            } else {
                //Validaciones de tipo de datos y rangos permitidos
                if (!is_numeric($this->numeroHistoriaClinica)) {
                    throw new ModelsException($config['errors']['numeroHistoriaClinicaNumerico']['message'], 1);
                }
            }

            //Max row to fetch
            if ($this->length == null) {
                throw new ModelsException($config['errors']['lengthObligatorio']['message'], 1);
            } else {
                if ($this->length <= 0) {
                    throw new ModelsException($config['errors']['lengthIncorrecto']['message'], 1);
                }
            }

            //Min row to fetch
            if ($this->start == null) {
                throw new ModelsException($config['errors']['startObligatorio']['message'], 1);
            } else {
                if ($this->start < 0) {
                    throw new ModelsException($config['errors']['startIncorrecto']['message'], 1);
                }
            }

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stid);

            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN
                PRO_TEL_HISTORIAS_ANT(:pn_hc, :pn_num_reg, :pn_num_pag, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $this->numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_num_reg", $this->length, 32);
            oci_bind_by_name($stid, ":pn_num_pag", $this->start, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $historiasClinicasAnteriores = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;
                $registraHistoriaClinica = 'S';

                //Valida que tenga registrado la
                if ($row[6] == 'S') {
                    if ($row[7] == '' and $row[8] == '' and $row[9] == '') {
                        $registraHistoriaClinica = 'N';
                    }
                }

                if ($row[5] == $this->id_user->codMedico && $row[3] == 'C. EXTERNA') {
                    # RESULTADO OBJETO
                    $historiasClinicasAnteriores[] = array(
                        'numeroAdmision' => $row[0] == null ? '' : $row[0],
                        'fechaAdmision' => $row[1] == null ? '' : $row[1],
                        'especialidad' => $row[2] == null ? '' : $row[2],
                        'origen' => $row[3] == null ? '' : $row[3],
                        'nombreMedicoTratante' => $row[4] == null ? '' : $row[4],
                        'codigoMedicoTratante' => $row[5] == null ? '' : $row[5],
                        'esTeleconsulta' => $row[6] == null ? '' : $row[6],
                        'registraHistoriaClinica' => $registraHistoriaClinica,
                        'motivoCitaMedica' => $row[7] == null ? '' : $row[7],
                    );
                }

            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {
                return array(
                    'status' => true,
                    'data' => $historiasClinicasAnteriores,
                    'start' => $this->start,
                    'length' => $this->length,
                );
            } else {
                throw new ModelsException($config['errors']['noExistenResultados']['message'], 1);
            }

        } catch (ModelsException $e) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $e->getMessage(),
                'errorCode' => $e->getCode()
            );

        } catch (Exception $ex) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $ex->getMessage(),
                'errorCode' => -1
            );

        } finally {
            //Libera recursos de conexión
            if ($stid != null) {
                oci_free_statement($stid);
            }

            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

            //Cierra la conexión
            $this->conexion->cerrar();
        }
    }

    /**
     * Obtiene los datos de la historia clínica
     */
    public function consultar()
    {
        global $config;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $datosHistoriaClinica[] = null;

        try {
            //Asignar parámetros de entrada
            $this->setParameters();

            //Validar parámetros de entrada
            $this->validarParametros();

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stid);

            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CEA_MOT_CONS_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $this->numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $this->numeroAdmision, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $datosHistoriaClinica = array();

            //Instancia los pedidos
            $pedido = new Pedidos;

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $datosHistoriaClinica = array(
                    'numeroHistoriaClinica' => $this->numeroHistoriaClinica, 'numeroAdmision' => $this->numeroAdmision,
                    'usuarioCrea' => $row[3] == null ? '' : $row[3], 'usuarioModifica' => $row[4] == null ? '' : $row[4], 'primerApellidoPaciente' => $row[5] == null ? '' : $row[5], 'segundoApellidoPaciente' => $row[6] == null ? '' : $row[6], 'primerNombrePaciente' => $row[7] == null ? '' : $row[7], 'segundoNombrePaciente' => $row[8] == null ? '' : $row[8],
                    'motivoConsulta' => array('motivoConsulta' => $row[0] == null ? '' : $row[0], 'antecedentesPersonales' => $row[1] == null ? '' : $row[1], 'enfermedadActual' => $row[2] == null ? '' : $row[2], 'alergias' => $row[9] == null ? '' : $row[9]),
                    'revisionOrganos' => $this->obtenerRevisionOrganos($this->numeroHistoriaClinica, $this->numeroAdmision, $stid),
                    'antecedentesFamiliares' => $this->obtenerAntecedentesFamiliares($this->numeroHistoriaClinica, $this->numeroAdmision, $stid),
                    'signosVitales' => $this->obtenerSignosVitales($this->numeroHistoriaClinica, $this->numeroAdmision, $stid),
                    'examenFisico' => $this->obtenerExamenFisico($this->numeroHistoriaClinica, $this->numeroAdmision, $stid),
                    'diagnosticos' => $this->obtenerDiagnosticos($this->numeroHistoriaClinica, $this->numeroAdmision, $stid),
                    'evoluciones' => $this->obtenerEvoluciones($this->numeroHistoriaClinica, $this->numeroAdmision, $stid),
                    'prescripciones' => $this->obtenerDetalleRecetaMedica($this->numeroHistoriaClinica, $this->numeroAdmision, $stid),
                    'recomendacionesNoFarmacologicas' => $this->obtenerRecomendacionesNoFarmacologicas($this->numeroHistoriaClinica, $this->numeroAdmision, $stid),
                    'pedidosLaboratorio' => $pedido->consultarPedidos($this->numeroHistoriaClinica, $this->numeroAdmision, 'L', $stid, $this->conexion->getConexion()),
                    'pedidosImagen' => $pedido->consultarPedidos($this->numeroHistoriaClinica, $this->numeroAdmision, 'I', $stid, $this->conexion->getConexion()),
                );

            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {
                return array(
                    'status' => true,
                    'data' => $datosHistoriaClinica,
                );
            } else {
                throw new ModelsException($config['errors']['noExistenResultados']['message'], 1);
            }

        } catch (ModelsException $e) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $e->getMessage(),
                'errorCode' => $e->getCode()
            );

        } catch (Exception $ex) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $ex->getMessage(),
                'errorCode' => -1
            );

        } finally {
            //Libera recursos de conexión
            if ($stid != null) {
                oci_free_statement($stid);
            }

            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

            //Cierra la conexión
            $this->conexion->cerrar();
        }
    }

    /**
     * Obtiene los datos de la receta médica
     */
    public function consultarRecetaMedica()
    {
        global $config;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $datosRecetaMedica[] = null;

        try {
            //Asignar parámetros de entrada
            $this->setParameters();

            //Validar parámetros de entrada
            $this->validarParametros();

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stid);

            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CAB_RECETA_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $this->numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $this->numeroAdmision, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $datosRecetaMedica = array();

            //Pedidos de laboratorio e imagen
            $pedido = new Pedidos;

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $datosRecetaMedica = array(
                    'numeroHistoriaClinica' => $this->numeroHistoriaClinica, 'numeroAdmision' => $this->numeroAdmision,
                    'nombresPaciente' => $row[0] == null ? '' : $row[0],
                    'alergias' => $row[1] == null ? '' : $row[1],
                    'edad' => $row[2] == null ? '' : $row[2],
                    'diagnostico' => $row[3] == null ? '' : $row[3],
                    'fechaPrescripcion' => $row[4] == null ? '' : $row[4],
                    'registroProfesional' => $row[5] == null ? '' : $row[5],
                    'nombreMedico' => $row[6] == null ? '' : $row[6],
                    'especialidad' => $row[7] == null ? '' : $row[7],
                    'cedula' => $row[8] == null ? '' : $row[8],
                    'genero' => $row[9] == null ? '' : $row[9],
                    'numeroCelular' => $row[10] == null ? '' : $row[10],
                    'prescripciones' => $this->obtenerDetalleRecetaMedica($this->numeroHistoriaClinica, $this->numeroAdmision, $stid),
                    'recomendacionesNoFarmacologicas' => $this->obtenerRecomendacionesNoFarmacologicas($this->numeroHistoriaClinica, $this->numeroAdmision, $stid),
                    'pedidosLaboratorio' => $pedido->consultarPedidos($this->numeroHistoriaClinica, $this->numeroAdmision, 'L', $stid, $this->conexion->getConexion()),
                    'pedidosImagen' => $pedido->consultarPedidos($this->numeroHistoriaClinica, $this->numeroAdmision, 'I', $stid, $this->conexion->getConexion()),
                );

            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {
                return array(
                    'status' => true,
                    'data' => $datosRecetaMedica,
                );
            } else {
                throw new ModelsException($config['errors']['noExistenResultados']['message'], 1);
            }

        } catch (ModelsException $e) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $e->getMessage(),
                'errorCode' => $e->getCode()
            );

        } catch (Exception $ex) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $ex->getMessage(),
                'errorCode' => -1
            );

        } finally {
            //Libera recursos de conexión
            if ($stid != null) {
                oci_free_statement($stid);
            }

            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

            //Cierra la conexión
            $this->conexion->cerrar();
        }
    }

    public function downloadReporte($url, $idHashReporte)
    {

        $destination = 'downloads/reportes/gema/002/' . $idHashReporte . '.pdf';

        $source = file_get_contents($url);
        file_put_contents($destination, $source);
        return true;

    }

    /**
     * Obtiene el enlace al reporte MSP 002 de la historia clínica del paciente
     */
    public function obtenerEnlaceReporteMSP002()
    {
        global $config;

        //Inicialización de variables
        $stid = null;
        $url = null;
        $datosURL = null;

        try {
            //Asignar parámetros de entrada
            $this->setParameters();

            #hashreporte
            $idHashReporte = Helper\Strings::ocrend_encode($this->numeroHistoriaClinica . '-' . $this->numeroAdmision, 'temp');
            //Validar parámetros de entrada
            $this->validarParametrosEnlaceReporteMSP002();

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stid);

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN
            PKG_REPORTES.preportehistoriaclinica(:pn_institucion, :pn_paciente, :pn_admision, :url); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_institucion", $this->codigoInstitucion, 32);
            oci_bind_by_name($stid, ":pn_paciente", $this->numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_admision", $this->numeroAdmision, 32);

            // Bind the output argument
            oci_bind_by_name($stid, ":url", $url, 1000);

            //Ejecuta el SP
            oci_execute($stid);

            $url = str_replace('reportes/b97cabc9a', 'TELEMEDICINA/TELEME2020', $url);

            # Descargar recurso
            $downloadReporte = $this->downloadReporte($url, $idHashReporte);

            if ($downloadReporte) {

                return array(
                    'status' => true,
                    'data' => [],
                    'url' => $config['build']['url'] . 'v3/reportes/gema/002/' . $idHashReporte . '.pdf',
                );

            } else {
                throw new ModelsException('Reporte no se pudo obtener desde GEMA.');
            }

        } catch (ModelsException $e) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $e->getMessage(),
                'errorCode' => $e->getCode()
            );

        } catch (Exception $ex) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $ex->getMessage(),
                'errorCode' => -1
            );

        } finally {
            //Libera recursos de conexión
            if ($stid != null) {
                oci_free_statement($stid);
            }

            //Cierra la conexión
            $this->conexion->cerrar();
        }
    }

    /**
     * Obtiene los datos de la revisión de órganos
     */
    public function obtenerRevisionOrganos($numeroHistoriaClinica, $numeroAdmision, $stid)
    {
        global $config;

        //Inicialización de variables
        $pc_datos = null;
        $existeDatos = false;
        $revisionOrganos = null;

        try {
            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CEA_REV_ORG_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $numeroAdmision, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $revisionOrganos = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $revisionOrganos = array(
                    'sentidos' => $row[0] == null ? '' : $row[0],
                    'cardioVascular' => $row[1] == null ? '' : $row[1],
                    'genital' => $row[2] == null ? '' : $row[2],
                    'muscEsqueletico' => $row[3] == null ? '' : $row[3],
                    'hemoLinfatico' => $row[4] == null ? '' : $row[4],
                    'respiratorio' => $row[5] == null ? '' : $row[5],
                    'digestivo' => $row[6] == null ? '' : $row[6],
                    'urinario' => $row[7] == null ? '' : $row[7],
                    'endocrino' => $row[8] == null ? '' : $row[8],
                    'nervioso' => $row[9] == null ? '' : $row[9],
                );

            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {
                return $revisionOrganos;
            } else {
                throw new ModelsException($config['errors']['noExistenResultados']['message'], 1);
            }

        } finally {
            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

        }
    }

    /**
     * Obtiene los datos de los antecedentes familiares de una admisión anterior
     */
    public function obtenerAntecedentesFamiliaresAdmisionAnterior()
    {
        global $config;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $antecedentesFamiliares = null;

        try {
            //Asignar parámetros de entrada
            $this->setParameters();

            //Validar parámetros de entrada
            $this->validarParametros();

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stid);

            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN
                PRO_TEL_CEA_ANT_FA_ANT_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $this->numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $this->numeroAdmision, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $antecedentesFamiliares = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $antecedentesFamiliares = array(
                    'cardiopatia' => $row[0] == null ? '' : $row[0],
                    'diabetes' => $row[1] == null ? '' : $row[1],
                    'enfermedadVascular' => $row[2] == null ? '' : $row[2],
                    'hipertension' => $row[3] == null ? '' : $row[3],
                    'cancer' => $row[4] == null ? '' : $row[4],
                    'tuberculosis' => $row[5] == null ? '' : $row[5],
                    'enfermendadMental' => $row[6] == null ? '' : $row[6],
                    'enfermedadInfecciosa' => $row[7] == null ? '' : $row[7],
                    'malformacion' => $row[8] == null ? '' : $row[8],
                    'otro' => $row[9] == null ? '' : $row[9],
                );

            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {
                return array(
                    'status' => true,
                    'data' => $antecedentesFamiliares,
                );
            } else {
                throw new ModelsException($config['errors']['noExistenResultados']['message'], 1);
            }

        } catch (ModelsException $e) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $e->getMessage(),
                'errorCode' => $e->getCode()
            );

        } catch (Exception $ex) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $ex->getMessage(),
                'errorCode' => -1
            );

        } finally {
            //Libera recursos de conexión
            if ($stid != null) {
                oci_free_statement($stid);
            }

            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

            //Cierra la conexión
            $this->conexion->cerrar();
        }
    }

    /**
     * Obtiene los datos de los antecedentes familiares
     */
    public function obtenerAntecedentesFamiliares($numeroHistoriaClinica, $numeroAdmision, $stid)
    {
        global $config;

        //Inicialización de variables
        $pc_datos = null;
        $existeDatos = false;
        $antecedentesFamiliares = null;

        try {
            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CEA_ANTEC_FAM_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $numeroAdmision, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $antecedentesFamiliares = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $antecedentesFamiliares = array(
                    'cardiopatia' => $row[0] == null ? '' : $row[0],
                    'diabetes' => $row[1] == null ? '' : $row[1],
                    'enfermedadVascular' => $row[2] == null ? '' : $row[2],
                    'hipertension' => $row[3] == null ? '' : $row[3],
                    'cancer' => $row[4] == null ? '' : $row[4],
                    'tuberculosis' => $row[5] == null ? '' : $row[5],
                    'enfermendadMental' => $row[6] == null ? '' : $row[6],
                    'enfermedadInfecciosa' => $row[7] == null ? '' : $row[7],
                    'malformacion' => $row[8] == null ? '' : $row[8],
                    'otro' => $row[9] == null ? '' : $row[9],
                );

            }

            //No existe datos en la consulta
            if (!$existeDatos) {
                $antecedentesFamiliares = array(
                    'cardiopatia' => '',
                    'diabetes' => '',
                    'enfermedadVascular' => '',
                    'hipertension' => '',
                    'cancer' => '',
                    'tuberculosis' => '',
                    'enfermendadMental' => '',
                    'enfermedadInfecciosa' => '',
                    'malformacion' => '',
                    'otro' => '',
                );
            }

            return $antecedentesFamiliares;

        } finally {
            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

        }
    }

    /**
     * Obtiene los datos de los signos vitales
     */
    public function obtenerSignosVitales($numeroHistoriaClinica, $numeroAdmision, $stid)
    {
        global $config;

        //Inicialización de variables
        $pc_datos = null;
        $existeDatos = false;
        $signosVitales = null;

        try {
            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CEA_SIGNOS_VIT_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $numeroAdmision, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $signosVitales = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $signosVitales[] = array(
                    'fecha' => $row[0] == null ? '' : $row[0],
                    'temperaturaBucal' => $row[1] == null ? '' : $row[1],
                    'temperaturaAxilar' => $row[2] == null ? '' : $row[2],
                    'temperaturaRectal' => $row[3] == null ? '' : $row[3],
                    'taSistolica' => $row[4] == null ? '' : $row[4],
                    'taDiastolica' => $row[5] == null ? '' : $row[5],
                    'pulso' => $row[6] == null ? '' : $row[6],
                    'frecuenciaRespiratoria' => $row[7] == null ? '' : $row[7],
                    'perimetroCef' => $row[8] == null ? '' : $row[8],
                    'peso' => $row[9] == null ? '' : $row[9],
                    'talla' => $row[10] == null ? '' : $row[10],
                    'imc' => $row[11] == null ? '' : $row[11],
                );

            }

            return $signosVitales;

        } finally {
            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

        }
    }

    /**
     * Obtiene los datos del examen físico
     */
    public function obtenerExamenFisico($numeroHistoriaClinica, $numeroAdmision, $stid)
    {
        global $config;

        //Inicialización de variables
        $pc_datos = null;
        $existeDatos = false;
        $examenFisico = null;

        try {
            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CEA_EXA_FISICO_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $numeroAdmision, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $examenFisico = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $examenFisico = array(
                    'cabeza1R' => $row[0] == null ? '' : $row[0],
                    'cuello2R' => $row[1] == null ? '' : $row[1],
                    'torax3R' => $row[2] == null ? '' : $row[2],
                    'abdomen4R' => $row[3] == null ? '' : $row[3],
                    'pelvis5R' => $row[4] == null ? '' : $row[4],
                    'extremidades6R' => $row[5] == null ? '' : $row[5],
                    'planTratamiento' => $row[6] == null ? '' : $row[6],
                );

            }

            //Valida si no existen datos para la consulta
            if (!$existeDatos) {
                $examenFisico = array(
                    'cabeza1R' => '',
                    'cuello2R' => '',
                    'torax3R' => '',
                    'abdomen4R' => '',
                    'pelvis5R' => '',
                    'extremidades6R' => '',
                    'planTratamiento' => '',
                );
            }

            return $examenFisico;

        } finally {
            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

        }
    }

    /**
     * Obtiene los datos de los diagnósticos
     */
    public function obtenerDiagnosticos($numeroHistoriaClinica, $numeroAdmision, $stid)
    {
        global $config;

        //Inicialización de variables
        $pc_datos = null;
        $existeDatos = false;
        $diagnosticos = null;

        try {
            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CEA_DIAG_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $numeroAdmision, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $diagnosticos = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $diagnosticos[] = array(
                    'numeroDiagnostico' => $row[0] == null ? '' : $row[0],
                    'codigo' => $row[1] == null ? '' : $row[1],
                    'grupo' => $row[2] == null ? '' : $row[2],
                    'descripcion' => $row[3] == null ? '' : $row[3],
                    'tipo' => $row[4] == null ? '' : $row[4],
                    'clasificacionDiagnostico' => $row[5] == null ? '' : $row[5],
                    'principal' => $row[6] == null ? '' : $row[6],
                );

            }

            return $diagnosticos;

        } finally {
            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

        }
    }

    /**
     * Obtiene los datos de las evoluciones
     */
    public function obtenerEvoluciones($numeroHistoriaClinica, $numeroAdmision, $stid)
    {
        global $config;

        //Inicialización de variables
        $pc_datos = null;
        $existeDatos = false;
        $evoluciones = null;

        try {
            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CEA_EVOL_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $numeroAdmision, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $evoluciones = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $evoluciones = array(
                    'codigo' => $row[0],
                    'descripcion' => $row[1],
                );

            }

            //Valida si la consulta no tiene datos
            if (!$existeDatos) {
                $evoluciones = array(
                    'codigo' => '',
                    'descripcion' => '',
                );
            }

            return $evoluciones;

        } finally {
            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

        }
    }

    /**
     * Obtiene los datos de las Recomendaciones No Farmacologicas
     */
    public function obtenerRecomendacionesNoFarmacologicas($numeroHistoriaClinica, $numeroAdmision, $stid)
    {
        global $config;

        //Inicialización de variables
        $pc_datos = null;
        $existeDatos = false;
        $recomendacionesNoFarmacologicas = null;

        try {
            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CEA_PRESC_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $numeroAdmision, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $recomendacionesNoFarmacologicas = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $recomendacionesNoFarmacologicas = array(
                    'codigo' => $row[0],
                    'descripcion' => $row[1] == null ? '' : $row[1],
                );

                break;
            }

            //Valida si la consulta no tiene datos
            if (!$existeDatos) {
                $recomendacionesNoFarmacologicas = array(
                    'codigo' => '',
                    'descripcion' => '',
                );
            }

            return $recomendacionesNoFarmacologicas;

        } finally {
            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

        }
    }

    /**
     * Obtiene el detalle de la receta médica
     */
    public function obtenerDetalleRecetaMedica($numeroHistoriaClinica, $numeroAdmision, $stid)
    {
        global $config;

        //Inicialización de variables
        $pc_datos = null;
        $existeDatos = false;
        $prescripciones = null;

        try {
            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_RECETA_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $numeroAdmision, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $prescripciones = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $prescripciones[] = array(
                    'codigo' => $row[0],
                    'medicamento' => $row[1],
                    'cantidadComprar' => $row[2] == null ? '' : $row[2],
                    'cantidadComprarLetras' => $row[3] == null ? '' : $row[3],
                    'unidadComprar' => $row[4] == null ? '' : $row[4],
                    'cantidadTomar' => $row[5] == null ? '' : $row[5],
                    'unidadTomar' => $row[6] == null ? '' : $row[6],
                    'frecuenciaDosis' => $row[7] == null ? '' : $row[7],
                    'unidadDosis' => $row[8] == null ? '' : $row[8],
                    'frecuenciaTotal' => $row[9] == null ? '' : $row[9],
                    'unidadTotal' => $row[10] == null ? '' : $row[10],
                    'viaAdministracion' => $row[11] == null ? '' : $row[11],
                    'indicacion' => $row[12] == null ? '' : $row[12],
                    'fecha' => $row[13] == null ? '' : $row[13],
                );

            }

            return $prescripciones;

        } finally {
            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

        }
    }

    /**
     * Consultar las evoluciones anteriores
     */
    public function consultarEvolucionesAnteriores()
    {
        global $config;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $evolucionesAnteriores[] = null;
        $registraHistoriaClinica;

        try {
            //Asignar parámetros de entrada
            $this->setParameters();

            //Validar parámetros de entrada
            $this->validarParametros();

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stid);

            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN
                PRO_TEL_CEA_EVOL_ANT_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $this->numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $this->numeroAdmision, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $evolucionesAnteriores = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $evolucionesAnteriores[] = array(
                    'descripcion' => $row[0],
                    'fecha' => $row[1],
                );
            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {
                return array(
                    'status' => true,
                    'data' => $evolucionesAnteriores,
                );
            } else {
                throw new ModelsException($config['errors']['noExistenResultados']['message'], 1);
            }

        } catch (ModelsException $e) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $e->getMessage(),
                'errorCode' => $e->getCode()
            );

        } catch (Exception $ex) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $ex->getMessage(),
                'errorCode' => -1
            );

        } finally {
            //Libera recursos de conexión
            if ($stid != null) {
                oci_free_statement($stid);
            }

            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

            //Cierra la conexión
            $this->conexion->cerrar();
        }
    }

    /**
     * __construct()
     */
    public function __construct(IRouter $router = null)
    {
        parent::__construct($router);

        //Instancia la clase conexión a la base de datos
        $this->conexion = new Conexion();

        $this->startDBConexion();

    }
}
