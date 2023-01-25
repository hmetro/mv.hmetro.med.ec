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

use app\models\v3\hmetro as Model;
use Exception;
use Ocrend\Kernel\Helpers as Helper;
use Ocrend\Kernel\Models\IModels;
use Ocrend\Kernel\Models\Models;
use Ocrend\Kernel\Models\ModelsException;
use Ocrend\Kernel\Models\Traits\DBModel;
use Ocrend\Kernel\Router\IRouter;

/**
 * Modelo Pacientes
 */

class Pacientes extends Models implements IModels
{

    use DBModel;

    # Variables de clase
    private $sortField = 'ROWNUM_';
    private $sortType = 'desc'; # desc
    private $start = 0;
    private $length = 10;
    private $searchField = null;
    private $startDate = null;
    private $endDate = null;
    private $_conexion = null;

    #Paciente
    private $numeroHistoriaClinica;
    private $codigoEspecialidadMedico;
    private $codigoHorario;
    private $fechaNacimiento;
    private $codigoConsulta;
    private $indentificacionPaciente;
    private $valorConsulta;
    private $numeroTurno;
    private $primerApellidoPaciente;
    private $primerNombrePaciente;
    private $codigoLugarAtencion;
    private $genero;
    private $tipoIdentificacionPaciente;
    private $valorCobertura;
    private $codigoPersona;

    private $codigoInstitucion;
    private $tipoIdentificacion;
    private $identificacion;
    private $primerApellido;
    private $segundoApellido;
    private $primerNombre;
    private $segundoNombre;
    private $estadoCivil;
    private $calle;
    private $numero;
    private $celular;
    private $email;
    private $pais;
    private $provincia;
    private $ciudad;
    private $distrito;

    #Datos de Factura
    private $apellidosFactura;
    private $correoFactura;
    private $direccionFactura;
    private $identificacionFactura;
    private $nombresFactura;
    private $tipoIdentificacionFactura;

    #Datos de TC
    private $identificacionTitular;
    private $nombreTitular;
    private $numeroAutorizacion;
    private $numeroVoucher;
    private $tipoTarjetaCredito;
    private $telefono;

    #Tipo de la cita
    private $tipoCita;

    # Variables de clase
    private $conexion;

    # Variables de Logs
    private $logs;

    /**
     * Obtiene el token
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

        foreach ($http->query->all() as $key => $value) {
            $this->$key = $value;
        }

        foreach ($http->request->all() as $key => $value) {
            $this->$key = $value;
        }

    }

    /**
     * Obtiene los datos del paciente con parámetro codigoPersona
     */
    public function getDatosPersonales()
    {
        global $config;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $datosPaciente[] = null;

        try {

            $this->getAuthorization();

            // Asignar parámetros de entrada
            $this->setParameters();

            // Validar parámetros de entrada

            // Código de la persona
            $this->codigoPersona = (int) $this->codigoPersona;

            if ($this->codigoPersona == null) {
                throw new ModelsException($config['errors']['codigoPersonaObligatorio']['message'], 1);
            } else {
                //Validaciones de tipo de datos y rangos permitidos
                if (!is_numeric($this->codigoPersona)) {
                    throw new ModelsException($config['errors']['codigoPersonaNumerico']['message'], 1);
                }
            }

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stid);

            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(),
                "BEGIN PRO_TEL_DATOS_PACIENTE(:pn_cod_persona, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_cod_persona", $this->codigoPersona, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $datosPaciente = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {

                $existeDatos = true;

                # RESULTADO OBJETO
                $datosPaciente[] = array(
                    'nhc' => $row[0] == null ? '' : $row[0],
                    'primerApellido' => $row[1] == null ? '' : $row[1],
                    'segundoApellido' => $row[2] == null ? '' : $row[2],
                    'primerNombre' => $row[3] == null ? '' : $row[3],
                    'segundoNombre' => $row[4] == null ? '' : $row[4],
                    'genero' => $row[5] == null ? '' : $row[5],
                    'estadoCivil' => $row[6] == null ? '' : $row[6],
                    'fechaNacimiento' => $row[7] == null ? '' : $row[7],
                    'cedula' => $row[8] == null ? '' : $row[8],
                    'pasaporte' => $row[9] == null ? '' : $row[9],
                    'ruc' => $row[10] == null ? '' : $row[10],
                    'direcciones' => $this->obtenerDirecciones($this->codigoPersona, $stid),
                    'mediosContacto' => $this->obtenerMediosContacto($this->codigoPersona, $stid),
                );
            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {

                return array(
                    'status' => true,
                    'data' => $datosPaciente[0],
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
     * Obtiene los medios de contacto del paciente
     */
    public function obtenerMediosContacto($codigoPersona, $stid)
    {
        global $config;

        //Inicialización de variables
        $pc_datos = null;
        $existeDatos = false;
        $mediosContacto[] = null;

        try {
            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CONTACTOS_PACIENTE(:pn_cod_persona, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_cod_persona", $codigoPersona, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $mediosContacto = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $mediosContacto[] = array(
                    'valor' => $row[0] == null ? '' : $row[0],
                    'tipo' => $row[1] == null ? '' : $row[1],
                );

            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {
                return $mediosContacto;
            } else {
                return [];
            }

        } finally {
            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

        }
    }

    /**
     * Obtiene las direcciones asociadas al paciente
     */
    public function obtenerDirecciones($codigoPersona, $stid)
    {
        global $config;

        //Inicialización de variables
        $pc_datos = null;
        $existeDatos = false;
        $direcciones[] = null;

        try {
            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_DIRECCION_PACIENTE(:pn_cod_persona, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_cod_persona", $codigoPersona, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $direcciones = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $direcciones[] = array(
                    'codigoDireccion' => $row[0] == null ? '' : $row[0],
                    'tipoDireccion' => $row[1] == null ? '' : $row[1],
                    'calle' => $row[2] == null ? '' : $row[2],
                    'numero' => $row[3] == null ? '' : $row[3],
                    'interseccion' => $row[4] == null ? '' : $row[4],
                    'referencia' => $row[5] == null ? '' : $row[5],
                    'pais' => $row[6] == null ? '' : $row[6],
                    'provincia' => $row[7] == null ? '' : $row[7],
                    'canton' => $row[8] == null ? '' : $row[8],
                );

            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {
                return $direcciones;
            } else {
                return [];
            }

        } finally {
            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

        }
    }

    /**
     * saveExpediente
     *
     * @return array : Con información de éxito/falla.
     */
    public function saveExpediente()
    {

        try {
            global $config, $http;

            # Get Autorization
            $this->getAuthorization();

            $hc = new Model\HistoriaClinica;
            $data = $hc->crear();

            return $data;

        } catch (ModelsException $e) {
            return array('status' => false, 'message' => $e->getMessage());
        }

    }

    /**
     * getExpediente
     *
     * @return array : Con información de éxito/falla.
     */
    public function getExpediente()
    {

        try {
            global $config, $http;

            # Get Autorization
            $this->getAuthorization();

            # Set Variable
            $numeroHistoriaClinica = $http->query->get('numeroHistoriaClinica');
            $numeroAdmision = $http->query->get('numeroAdmision');
            $idMedico = $this->id_user->codMedico;

            # Verificar que no están vacíos
            if (Helper\Functions::e($numeroHistoriaClinica, $numeroAdmision)) {
                throw new ModelsException('Parámetros insuficientes para esta peticion.');
            }

            $statusHC = $this->db->select('*', "atencionesHMQ", null, " idMedico='" . $idMedico . "' AND  numeroHistoriaClinica='" . $numeroHistoriaClinica . "'   AND numeroAdmision='" . $numeroAdmision . "'  AND dataHC IS NOT NULL ", 1);

            if (false == $statusHC) {
                throw new ModelsException('No existe el documento.');
            }

            return array(
                'status' => true,
                'data' => $statusHC[0],
            );

        } catch (ModelsException $e) {
            return array('status' => false, 'message' => $e->getMessage());
        }

    }

    /**
     * sTATUS hc
     *
     * @return array : Con información de éxito/falla.
     */
    public function statusHC($numeroHistoriaClinica, $numeroAdmision)
    {

        global $config, $http;

        $statusHC = $this->db->select('*', "atencionesHMQ", null, " numeroHistoriaClinica='" . $numeroHistoriaClinica . "'   AND numeroAdmision='" . $numeroAdmision . "'  AND statusHC='1' ", 1);

        if (false !== $statusHC) {
            return true;
        }

        return false;

    }

    /**
     * Conexion
     *
     */

    private function conectar_Oracle()
    {
        global $config;

        $_config = new \Doctrine\DBAL\Configuration();
//..
        # SETEAR LA CONNEXION A LA BASE DE DATOS DE ORACLE GEMA
        $this->_conexion = \Doctrine\DBAL\DriverManager::getConnection($config['database']['drivers']['oracleProd'], $_config);

    }

    // Validar si existe ne bdd GEMA
    public function validacionBDDGEMA()
    {

        try {

            global $config, $http;

            # Set variables de funcion
            $this->documento = $http->query->get('documento');

            $this->DNI = $this->documento;

            if (strlen($this->documento) < 8) {
                throw new ModelsException('Documento ingresado no puede ser menor a 8 caracteres.');
            }

            if (strlen($this->documento) > 15) {
                throw new ModelsException('Documento ingresado no puede ser mayor a 15 caracteres.');
            }

            # Verificar que no están vacíos
            if (Helper\Functions::e($this->documento)) {
                throw new ModelsException('Parámetros insuficientes para esta peticion.');
            }

            # Conectar base de datos
            $this->conectar_Oracle();

            # Devolver todos los resultados
            $sql = "SELECT * FROM WEB2_VW_LOGIN
                WHERE CC = '" . $this->documento . "' OR RUC = '" . $this->documento . "'
                OR PASAPORTE = '" . $this->documento . "' ";

            # Execute
            $stmt = $this->_conexion->query($sql);

            $this->_conexion->close();

            $data = $stmt->fetchAll();

            if (count($data) === 0) {

                return array(
                    'status' => false,
                    'errorCode' => 1,
                    'data' => array(),
                    'message' => 'Paciente no esta registrado. *Registra al nuevo Paciente antes de continuar.',
                    'logs' => array(),
                );

            }

            # Extraer Roles de Usuario
            $roles = array(
                'PTE' => 0,
                'MED' => 0,
                'PRO' => 0,
                'VIP' => 0,
            );

            # Extraer Códigos de Persona para Pacientes
            $cp_pacientes = array();

            # Extraer Códigos de Persona para Médicos
            $cp_medicos = array();

            # Extraer Códigos de Persona para Proveedores
            $cp_proveedores = array();

            foreach ($data as $key => $value) {

                # Verificar si es Rol Paciente
                if (!is_null($value['COD_PTE'])) {
                    $roles['PTE'] = 1;
                    $cp_pacientes[] = (int) $value['COD_PTE'];
                }

                # Verificar si es Rol Médico
                if (!is_null($value['COD_MED'])) {
                    $roles['MED'] = 1;
                    $cp_medicos[] = (int) $value['COD_MED'];

                }

                # Verificar si es Rol Proveedor
                if (!is_null($value['COD_PROV'])) {
                    $roles['PRO'] = 1;
                    $cp_proveedores[] = (int) $value['COD_PROV'];

                }

                # Verificar si es Rol VIP
                if ($value['VIP'] != 0) {
                    $roles['VIP'] = 1;
                }

                # SETEAR VALORES PARA DEFINICION
                if (!is_null($value['CC'])) {
                    if (!is_null($value['COD_PTE'])) {
                        $this->COD_PERSONA = $value['COD_PTE'];
                    } elseif (!is_null($value['COD_MED'])) {
                        $this->COD_PERSONA = $value['COD_MED'];
                    }
                }

                # SETEAR VALORES PARA DEFINICION PARA PROVEEDOR
                if ($roles['PRO'] === 1 && $roles['PTE'] === 0 && $roles['MED'] === 0) {
                    $this->COD_PERSONA = $value['COD_PROV'];
                }

            }

            # Extraer Códigos de Persona para Pacientes
            $_cp_pacientes['CP_PTE'] = array_unique($cp_pacientes);

            # Extraer Códigos de Persona para Médicos
            $_cp_medicos['CP_MED'] = array_unique($cp_medicos);

            # Extraer Códigos de Persona para Proveedores
            $_cp_proveedores['CP_PRO'] = array_unique($cp_proveedores);

            # Union de arrays
            $res = array_merge(
                array
                (
                    'DNI' => $this->DNI,
                    'COD_PERSONA' => $this->COD_PERSONA,
                ),
                $roles,
                $_cp_pacientes,
                $_cp_medicos,
                $_cp_proveedores
            );

            # Ultima validacion antes de proseguir

            if ($roles['PTE'] === 0 && $roles['MED'] === 0 && $roles['PRO'] === 0) {
                throw new ModelsException('Paciente no esta registrado en BDD.');
            }

            return array(
                'status' => true,
                'errorCode' => array(),

                'data' => $res,
                'message' => 'Paciente registrado en BDD',
                'logs' => $this->logs,
            );

        } catch (ModelsException $e) {

            return array(
                'status' => false,
                'data' => array(),
                'errorCode' => array(),
                'message' => $e->getMessage(),
                'logs' => $this->logs,
            );

        }

    }

    /**
     * getExpediente
     *
     * @return array : Con información de éxito/falla.
     */
    public function getExpedientes()
    {

        try {
            global $config, $http;

            # Get Autorization
            $this->getAuthorization();

            # Set Variable
            $numeroHistoriaClinica = $http->query->get('numeroHistoriaClinica');
            $idMedico = $this->id_user->codMedico;
            $nombreMedico = $this->id_user->primerNombre . " " . $this->id_user->segundoNombre . " " . $this->id_user->primerApellido . " " . $this->id_user->segundoApellido;

            # Verificar que no están vacíos
            if (Helper\Functions::e($numeroHistoriaClinica)) {
                throw new ModelsException('Parámetros insuficientes para esta peticion.');
            }

            $statusHC = $this->db->select('*', "atencionesHMQ", null, " idMedico='" . $idMedico . "' AND  numeroHistoriaClinica='" . $numeroHistoriaClinica . "' AND statusAtencion > 3 AND statusHC != '0'   ORDER BY id DESC ");

            if (false == $statusHC) {
                throw new ModelsException('No existe el documento.');
            }

            $expedientes = array();

            foreach ($statusHC as $key) {
                $expedientes[] = array(
                    'numeroAdmision' => $key['numeroAdmision'],
                    'fechaExpediente' => date('d-m-Y', $key['timestampCreate']),
                    'especialidadMedico' => $this->id_user->espeMedico,
                    'origen' => 'CONSULTA EXTERNA',
                    'nombreMedico' => $nombreMedico,
                    'codigoMedicoTratante' => $idMedico,
                    'statusHC' => (int) $key['statusHC'],
                    'motivoCitaMedica' => 'es el motivo',
                );
            }

            return array(
                'status' => true,
                'data' => $expedientes,
            );

        } catch (ModelsException $e) {
            return array('status' => false, 'data' => [], 'message' => $e->getMessage());
        }

    }

    /**
     * Consulta el listado de Historias Clínicas anteriores -> HOSPITAL METROPOLITANO
     */
    public function getExpedientesHM()
    {
        global $config, $http;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $historiasClinicasAnteriores[] = null;
        $registraHistoriaClinica;

        try {

            $this->getAuthorization();

            //Asignar parámetros de entrada
            $this->setParameters();

            $idMedico = $this->id_user->codMedico;
            $this->length = $this->start + 10;

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

                if ($row[5] == $idMedico) {
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
                        'expDis' => $this->statusHC($this->numeroHistoriaClinica, $row[0]),
                    );
                }

            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {

                return array(
                    'status' => true,
                    'data' => $historiasClinicasAnteriores,
                    'start' => (int) $this->start,
                    'length' => (int) $this->length,
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
     * Obtiene los datos de las evoluciones
     */
    public function obtenerEvoluciones()
    {
        global $config;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $evoluciones = null;

        try {

            //Asignar parámetros de entrada
            $this->setParameters();

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

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stid);

            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CEA_EVOL_LEE(:pn_hc, :pn_adm, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $this->numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_adm", $this->numeroAdmision, 32);
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
                $evoluciones[] = array(
                    'codigo' => $row[0],
                    'descripcion' => $row[1],
                );

            }

            //Valida si la consulta no tiene datos
            if ($existeDatos) {
                return array(
                    'status' => true,
                    'data' => $evoluciones,
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

        $sql = " alter session set NLS_DATE_FORMAT = 'DD-MM-YYYY HH24:MI'";
        # Execute
        $stid = oci_parse($this->conexion->getConexion(), $sql);
        oci_execute($stid);

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
