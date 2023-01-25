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
use Firebase\JWT\JWT;
use Ocrend\Kernel\Models\IModels;
use Ocrend\Kernel\Models\Models;
use Ocrend\Kernel\Models\ModelsException;
use Ocrend\Kernel\Router\IRouter;

/**
 * Modelo Users
 */
class Auth extends Models implements IModels
{

    /**
     * Variables privadas
     * @return void
     */

    private $secret_key = 'hdSunv8NPTA7evYI7gY$etcqvKk4^XUhWU*bldvdlpOUG@PffH_hm_api_v1';
    private $encrypt = ['HS256'];
    private $aud = null;
    private $USER = null;
    private $_conexion = null;
    private $os_name = null;
    private $os_version = null;
    private $client_type = null;
    private $client_name = null;
    private $device = null;

    public function generateKey($data)
    {

        $time = time();

        $data['user_token'] = true;

        $token = array(
            'exp' => strtotime('+10 days', $time),
            // 'exp'  => $time + (60 * 60),
            // 'exp'  => $time--,
            'aud' => $this->Aud(),
            'data' => $data,
        );

        # SETEAR VALORES DE RESPUESTA
        return JWT::encode($token, $this->secret_key);
    }

    public function generateKeyZoom()
    {

        global $config;

        $time = time();

        $key = $config['zoom']['api_key'];
        $secret = $config['zoom']['api_secret'];

        $token = array(
            'alg' => 'HS256',
            'typ' => 'JWT',
            'iss' => $key,
            'exp' => strtotime('+1 minute', $time),
        );

        # SETEAR VALORES DE RESPUESTA
        return array(
            'status' => true,
            'zoom_token' => JWT::encode($token, $config['zoom']['api_secret']),
        );
    }

    public function Check($token)
    {

        try {

            if (empty($token)) {
                # "Invalid token supplied."
                throw new ModelsException("Invalid token supplied.");
            }

            return false;

            $decode = JWT::decode(
                $token,
                $this->secret_key,
                $this->encrypt
            );

            $data = JWT::decode(
                $token,
                $this->secret_key,
                $this->encrypt
            )->data;

            if ($decode->aud !== $this->Aud()) {
                throw new ModelsException("Invalid user logged in.");
            }

            if (!isset($data->user_token)) {
                throw new ModelsException("Invalid user_token.");
            }

            return false;

        } catch (ModelsException $e) {
            // Errores del modelo
            return array('status' => false, 'message' => $e->getMessage());

        } catch (\Exception $b) {

            if ($b->getMessage() == 'Expired token') {
                return array('status' => false, 'message' => $b->getMessage());
            }

            // Errores de JWT > para todos los demas errores token invalido
            return array('status' => false, 'message' => $b->getMessage());

        }

    }

    public function GetData($token)
    {

        $data = JWT::decode(
            $token,
            $this->secret_key,
            $this->encrypt
        )->data;
        return $data;

    }

    public function Aud()
    {
        $aud = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }

        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();

        return sha1($aud);
    }

    public function IpClient()
    {
        $aud = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }

        $aud = $_SERVER['HTTP_CLIENT_IP'];

        return $aud;
    }

/**
 * __construct()
 */

    public function __construct(IRouter $router = null)
    {
        parent::__construct($router);
    }
}
