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
use Ocrend\Kernel\Models\IModels;
use Ocrend\Kernel\Models\Models;
use Ocrend\Kernel\Models\ModelsException;
use Ocrend\Kernel\Router\IRouter;

/**
 * Modelo Odbc GEMA -> Exámenes
 */

class Examenes extends Models implements IModels
{

    # Variables de clase
    private $conexion;
    private $start = 0;
    private $length = 2000;

    /**
     * Asigna los parámetros de entrada
     */
    private function setParameters()
    {
        global $http;

        foreach ($http->request->all() as $key => $value) {
            $this->$key = strtoupper($value);
        }

    }

    /**
     * Valida los parámetros de entrada
     */
    private function validarParametros()
    {
        global $config;

        //Start
        if ($this->start == null) {
            throw new ModelsException($config['errors']['startObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($this->start)) {
                throw new ModelsException($config['errors']['startNumerico']['message'], 1);
            }
        }

        //Length
        if ($this->length == null) {
            throw new ModelsException($config['errors']['lengthObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($this->length)) {
                throw new ModelsException($config['errors']['lengthNumerico']['message'], 1);
            }
        }

    }

    /**
     * Permite consultar los exámenes de Laboratorio
     */
    public function consultarExamenesLaboratorio()
    {
        global $config;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $examenesLaboratorio[] = null;

        try {

            $file = 'examenes/lab/laboratorio.json';

            # sI EXISTE ARCHIVO PREGENERADO CARGA ARCHIVO
            if (file_exists($file)) {

                $datos_lab = file_get_contents($file);
                $json_lab = json_decode($datos_lab, true);

                # Devolver Información
                return array(
                    'status' => true,
                    'data' => $json_lab,
                );

            } else {

                //Conectar a la BDD
                $this->conexion->conectar();

                //Setear idioma y formatos en español para Oracle
                $this->setSpanishOracle($stid);

                $pc_datos = oci_new_cursor($this->conexion->getConexion());

                $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_LISTA_LAB_LEE(:pn_num_reg, :pn_num_pag, :pc_datos); END;");

                // Bind the input num_entries argument to the $max_entries PHP variable
                oci_bind_by_name($stid, ":pn_num_reg", $this->length, 32);
                oci_bind_by_name($stid, ":pn_num_pag", $this->start, 32);
                oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

                //Ejecuta el SP
                oci_execute($stid);

                //Ejecutar el REF CURSOR como un ide de sentencia normal
                oci_execute($pc_datos);

                //Resultados de la consulta
                $examenesLaboratorio = array();

                while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                    $existeDatos = true;

                    $examenesLaboratorio[] = array(
                        'codigoAgrupacion' => $row[0] == null ? '' : $row[0],
                        'descripcionAgrupacion' => $row[1] == null ? '' : $row[1],
                        'codigoExamen' => $row[2] == null ? '' : $row[2],
                        'descripcionExamen' => $row[3] == null ? '' : $row[3],
                        'precioVentaPublico' => $row[4] == null ? '' : $row[4],
                    );

                }

                //Verificar si la consulta devolvió datos
                if ($existeDatos) {

                    $file = 'examenes/lab/laboratorio.json';

                    $json_lab = json_encode($examenesLaboratorio);
                    file_put_contents($file, $json_lab);

                    # RESULTADO OBJETO
                    return array(
                        'status' => true,
                        'data' => $examenesLaboratorio);

                } else {

                    throw new ModelsException($config['errors']['noExistenResultados']['message'], 1);

                }

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
     * Permite consultar los exámenes de Imagen
     */
    public function consultarExamenesImagen()
    {
        global $config;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $examenesImagen[] = null;

        try {

            $file = 'examenes/imagen/imagen.json';

            # sI EXISTE ARCHIVO PREGENERADO CARGA ARCHIVO
            if (file_exists($file)) {

                $datos_img = file_get_contents($file);
                $json_img = json_decode($datos_img, true);

                # Devolver Información
                return array(
                    'status' => true,
                    'data' => $json_img,
                );

            } else {

                //Conectar a la BDD
                $this->conexion->conectar();

                //Setear idioma y formatos en español para Oracle
                $this->setSpanishOracle($stid);

                $pc_datos = oci_new_cursor($this->conexion->getConexion());

                $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_LISTA_IMG_LEE(:pn_num_reg, :pn_num_pag, :pc_datos); END;");

                // Bind the input num_entries argument to the $max_entries PHP variable
                oci_bind_by_name($stid, ":pn_num_reg", $this->length, 32);
                oci_bind_by_name($stid, ":pn_num_pag", $this->start, 32);
                oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

                //Ejecuta el SP
                oci_execute($stid);

                //Ejecutar el REF CURSOR como un ide de sentencia normal
                oci_execute($pc_datos);

                //Resultados de la consulta
                $examenesImagen = array();

                while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {

                    $existeDatos = true;

                    $examenesImagen[] = array(
                        'codigoAgrupacion' => $row[0] == null ? '' : $row[0],
                        'descripcionAgrupacion' => $row[1] == null ? '' : $row[1],
                        'codigoExamen' => $row[2] == null ? '' : $row[2],
                        'descripcionExamen' => $row[3] == null ? '' : $row[3],
                        'precioVentaPublico' => $row[4] == null ? '' : $row[4],
                    );

                }

                //Verificar si la consulta devolvió datos
                if (!$existeDatos) {

                    throw new ModelsException($config['errors']['noExistenResultados']['message'], 1);

                } else {

                    $file = 'examenes/imagen/imagen.json';

                    $json_img = json_encode($examenesImagen);
                    file_put_contents($file, $json_img);

                    # RESULTADO OBJETO
                    return array(
                        'status' => true,
                        'data' => $examenesImagen,
                    );

                }

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
     * __construct()
     */
    public function __construct(IRouter $router = null)
    {
        parent::__construct($router);

        //Instancia la clase conexión a la base de datos
        $this->conexion = new Conexion();

    }
}
