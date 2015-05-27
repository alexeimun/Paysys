<?php

    Class caja_model extends CI_Model
    {
        public function __construct()
        {
            parent::__construct();
        }

        public function TraeUsuarios()
        {
            return $this->db->query("SELECT  ID_USUARIO  FROM t_usuarios WHERE NIVEL<>0 AND ESTADO=1 AND ID_USUARIO <> " . $this->session->userdata('ID_USUARIO'));
        }

        public function TraeInteresPago()
        {
            return $this->db->query("SELECT  DESCRIPCION,VALOR, CONCEPTO
                               FROM t_movimientos WHERE TIPO_MOV = 1 AND ID_SOLICITUD=" . $this->input->post('IdSol'));
        }

        public function InsertaPagoTemp($type = 0)
        {
            if ($type == 1)
            {
                $query = "INSERT INTO t_pago_temp (CONCEPTO,VALOR,TIPO,METADATO,ID_SOLICITUD,ID_USUARIO) VALUES ";
                $size = count($_POST['REG'][0]);
                for ($i = 0; $i < $size; $i ++)
                    $query .= "('" . $_POST['REG'][1][$i] . "'," . $_POST['REG'][2][$i] . ",1," . $_POST['REG'][0][$i] . "," . $this->input->post('IdSol') . "," . $this->session->userdata('ID_USUARIO') . ")" . ($i + 1 < $size ? "," : '');
                $this->db->query($query);
            }
            else
            {
                $CHEQUE = isset($_POST['CHEQUE']) ? $_POST['CHEQUE'] : '';
                $BANCO = isset($_POST['BANCO']) ? $_POST['BANCO'] : '';
                $this->db->insert('t_pago_temp', ['CONCEPTO' => $this->input->post('CONCEPTO'),
                    'TIPO' => $this->input->post('TIPO_RECIBO'),
                    'VALOR' => $this->input->post('VALOR'),
                    'CHEQUE' => $CHEQUE,
                    'BANCO' => $BANCO,
                    'ID_SOLICITUD' => $this->input->post('IdSol'),
                    'ID_USUARIO' => $this->session->userdata('ID_USUARIO')
                ]);
            }
        }

        public function InsertaRecibo($Consecutivo, $IdSol)
        {
            foreach ($this->TraeUsuarios()->result() as $campo)
            {
                $this->db->set('USUARIO_ACCION', $this->session->userdata('ID_USUARIO'));
                $this->db->set('FECHA', 'NOW()', false);
                $this->db->set('ACCION', $Consecutivo, false);
                $this->db->insert('t_notificaciones', ['ID_USUARIO' => $campo->ID_USUARIO, 'TIPO' => 'ri']);
            }
            $this->session->set_userdata(['First' => 'Original']);
            $Secuencia = 0;
            $TotalAbonado = 0;
            $TotalInteres = 0;
            $TotalAC = 0;
            foreach ($this->TraePagosTemp($IdSol) as $pagotemp)
            {
                switch ($pagotemp->TIPO)
                {
                    case 0:
                        $TotalAbonado += $pagotemp->VALOR;
                        break;
                    case 1:
                        $TotalInteres += $pagotemp->VALOR;
                        break;
                    case 2:
                        $TotalAC += $pagotemp->VALOR;
                        break;
                }
                $this->db->set('FECHA', 'NOW()', false);
                $this->db->insert('t_movimientos',
                    ['CONCEPTO' => $pagotemp->CONCEPTO,
                        'VALOR' => $pagotemp->VALOR,
                        'CHEQUE' => $pagotemp->CHEQUE,
                        'BANCO' => $pagotemp->BANCO,
                        'CONSECUTIVO' => $Consecutivo,
                        'DESCRIPCION' => $pagotemp->METADATO,
                        'TIPO_MOV' => $pagotemp->TIPO,
                        'SECUENCIA' => ++ $Secuencia,
                        'ID_SOLICITUD' => $IdSol,
                        'ID_USUARIO' => $this->session->userdata('ID_USUARIO')
                    ]);
            }
            $this->db->update('t_consecutivos', ['CONSECUTIVO' => $Consecutivo + 1], ['NOMBRE' => 'RECIBO']);
            if ($TotalAbonado > 0)
            {
                $this->db->query('UPDATE t_solicitudes SET ABONADO=ABONADO+' . $TotalAbonado . ' WHERE ID_SOLICITUD=' . $IdSol);
                $this->db->set('FECHA', 'NOW()', false);
                $this->db->insert('t_movimientos',
                    ['VALOR' => $TotalAbonado,
                        'CONSECUTIVO' => $Consecutivo,
                        'SECUENCIA' => ++ $Secuencia,
                        'ID_SOLICITUD' => $IdSol,
                        'DESCRIPCION' => 'TOTAL_ABONO',
                        'ID_USUARIO' => $this->session->userdata('ID_USUARIO')
                    ]);
            }
            if ($TotalInteres > 0)
            {
                $this->db->query('UPDATE t_solicitudes SET ABONO_INTERES=ABONO_INTERES+' . $TotalInteres . ' WHERE ID_SOLICITUD=' . $IdSol);
                $this->db->set('FECHA', 'NOW()', false);
                $this->db->insert('t_movimientos',
                    ['VALOR' => $TotalInteres,
                        'CONSECUTIVO' => $Consecutivo,
                        'SECUENCIA' => ++ $Secuencia,
                        'ID_SOLICITUD' => $IdSol,
                        'DESCRIPCION' => 'TOTAL_INTERES',
                        'ID_USUARIO' => $this->session->userdata('ID_USUARIO')
                    ]);
            }
            if ($TotalAC > 0)
            {
                $this->db->query('UPDATE t_solicitudes SET CAPITAL_INICIAL=CAPITAL_INICIAL+' . $TotalAC . ' WHERE ID_SOLICITUD=' . $IdSol);
                $this->db->set('FECHA', 'NOW()', false);
                $this->db->insert('t_movimientos',
                    ['VALOR' => $TotalAC,
                        'CONSECUTIVO' => $Consecutivo,
                        'SECUENCIA' => ++ $Secuencia,
                        'ID_SOLICITUD' => $IdSol,
                        'DESCRIPCION' => 'TOTAL_AMPLIACION_C',
                        'ID_USUARIO' => $this->session->userdata('ID_USUARIO')
                    ]);
            }
            ##TOTA_RECIBO
            $this->db->set('FECHA', 'NOW()', false);
            $this->db->insert('t_movimientos',
                ['VALOR' => $TotalInteres + $TotalAbonado,
                    'CONSECUTIVO' => $Consecutivo,
                    'SECUENCIA' => ++ $Secuencia,
                    'ID_SOLICITUD' => $IdSol,
                    'DESCRIPCION' => 'TOTAL_RECIBO',
                    'BANCO' => $this->input->post('BANCO'),
                    'CHEQUE' => $this->input->post('CHEQUE'),
                    'ID_USUARIO' => $this->session->userdata('ID_USUARIO')
                ]);
            $this->db->delete('T_pago_temp', ['ID_USUARIO' => $this->session->userdata('ID_USUARIO'), 'ID_SOLICITUD' => $IdSol]);
            $this->session->set_userdata(['ConsecutivoRecibo' => $Consecutivo]);
        }

        public function Clientes()
        {
            return $this->db->query("SELECT
              t_deudores.NOMBRE AS  NOMBRE_DEUDOR,
              t_deudores.DOCUMENTO DOCUMENTO_DEUDOR,
              t_acreedores.NOMBRE AS NOMBRE_ACREEDOR,
              t_acreedores.DOCUMENTO AS DOCUMENTO_ACREEDOR,
              t_solicitudes.FECHA_INICIO,
              t_solicitudes.FECHA_FIN,
              t_solicitudes.CAPITAL_INICIAL,
              t_solicitudes.INTERES_MENSUAL,
              IF(t_solicitudes.ABONO_INTERES IS NULL,0,t_solicitudes.ABONO_INTERES ) AS ABONO_INTERES,
              IF(t_solicitudes.ABONADO IS NULL,0,t_solicitudes.ABONADO ) AS ABONADO

                FROM
              t_solicitudes
                INNER  JOIN t_deudores ON t_deudores.ID_DEUDOR=t_solicitudes.ID_DEUDOR
               INNER  JOIN t_acreedores ON t_acreedores.ID_ACREEDOR=t_solicitudes.ID_ACREEDOR
               WHERE ID_SOLICITUD= " . $this->input->post('IdSol'));
        }

        public function TraePagosTemp($IdSol)
        {
            return $this->db->query("SELECT
                ID_PAGO_TEMP,
                TIPO,
                CASE TIPO WHEN 0 THEN 'Abono' WHEN 1 THEN 'Intereses' WHEN 2 THEN 'Ampliación C' END TIPO_ESCRITO,
                CONCEPTO,
                VALOR,
                CHEQUE,
                METADATO,
                BANCO
                FROM
              t_pago_temp

               WHERE ID_USUARIO=" . $this->session->userdata('ID_USUARIO') . " AND  ID_SOLICITUD= " . $IdSol)->result();
        }

        public function EliminaPagoTemp()
        {
            $this->db->delete('t_pago_temp', ['ID_PAGO_TEMP' => $this->input->post('IdPago')]);
        }

        public function TraeSolicitudes()
        {
            return $this->db->query("SELECT
             SOLICITUD,
             ID_SOLICITUD,
             CAPITAL_INICIAL,
             t_deudores.NOMBRE AS NOMBRE_DEUDOR,
             t_acreedores.NOMBRE AS NOMBRE_ACREEDOR,
             t_solicitudes.FECHA_INICIO,
             t_solicitudes.FECHA_FIN,
             t_deudores.DOCUMENTO AS DOCUMENTO_DEUDOR
             #if(TIPO_HIPOTECA=0,'Cerrada','Abierta')AS  TIPO_HIPOTECA

               FROM t_solicitudes

               INNER  JOIN t_deudores ON t_deudores.ID_DEUDOR=t_solicitudes.ID_DEUDOR
               INNER  JOIN t_acreedores ON t_acreedores.ID_ACREEDOR=t_solicitudes.ID_ACREEDOR
               WHERE t_solicitudes.ESTADO=1 AND  t_deudores.ESTADO=1 AND t_acreedores.ESTADO=1 ");
        }

        public function TraeSolicitud($IdSol)
        {
            return $this->db->query("SELECT
             SOLICITUD,
             ID_SOLICITUD,
             CAPITAL_INICIAL,
             t_deudores.NOMBRE AS NOMBRE_DEUDOR,
             t_deudores.DIRECCION AS DIRECCION_DEUDOR,
             t_acreedores.NOMBRE AS NOMBRE_ACREEDOR,
             t_solicitudes.FECHA_INICIO,
             IF(t_solicitudes.TIPO_HIPOTECA=0,'CERRADA','ABIERTA') TIPO_HIPOTECA,
             t_solicitudes.FECHA_FIN,
             t_solicitudes.INTERES_MENSUAL,
             t_solicitudes.ABONADO,
             t_deudores.DOCUMENTO AS DOCUMENTO_DEUDOR,
             t_inmuebles.NUMERO_ESCRITURA,
             t_inmuebles.NUMERO_NOTARIA,
             t_inmuebles.MATRICULA,
             t_inmuebles.FECHA_CONSTITUCION,
             t_inmuebles.DIRECCION AS DIRECCION_INMUEBLE

               FROM t_solicitudes

               INNER  JOIN t_deudores ON t_deudores.ID_DEUDOR=t_solicitudes.ID_DEUDOR
               INNER  JOIN t_acreedores ON t_acreedores.ID_ACREEDOR=t_solicitudes.ID_ACREEDOR
               INNER  JOIN t_inmuebles ON t_inmuebles.ID_PROPIETARIO=t_solicitudes.ID_DEUDOR
               WHERE t_solicitudes.ESTADO=1 AND  t_deudores.ESTADO=1 AND t_acreedores.ESTADO=1 AND
               ID_SOLICITUD=$IdSol LIMIT  1");
        }

        public function TraeRecibos()
        {
            return $this->db->query("SELECT
             CONSECUTIVO AS CONSECUTIVO_RECIBO,
             t_solicitudes.SOLICITUD AS CONSECUTIVO_SOLICITUD,
             t_deudores.NOMBRE AS NOMBRE_DEUDOR,
             t_deudores.DOCUMENTO,
             t_acreedores.NOMBRE AS NOMBRE_ACREEDOR,
             ID_MOVIMIENTO,
             VALOR,
             ANULADO,
             t_movimientos.FECHA

               FROM t_movimientos

               INNER  JOIN t_solicitudes ON t_solicitudes.ID_SOLICITUD=t_movimientos.ID_SOLICITUD
               INNER  JOIN t_deudores ON t_deudores.ID_DEUDOR=t_solicitudes.ID_DEUDOR
               INNER  JOIN t_acreedores ON t_acreedores.ID_ACREEDOR=t_solicitudes.ID_ACREEDOR
               WHERE t_solicitudes.ESTADO=1 AND  t_deudores.ESTADO=1 AND t_acreedores.ESTADO=1
               AND t_movimientos.DESCRIPCION='TOTAL_RECIBO'");
        }

        public function TraeAbonos($idSol)
        {
            return $this->db->query("SELECT
                t_movimientos.VALOR AS ABONADO,
                t_solicitudes.CAPITAL_INICIAL,
                t_solicitudes.ABONADO AS TOTAL_ABONADO,
                t_movimientos.CONSECUTIVO,
                t_movimientos.FECHA

               FROM t_movimientos

               INNER  JOIN t_solicitudes ON t_solicitudes.ID_SOLICITUD=t_movimientos.ID_SOLICITUD
               WHERE t_movimientos.ID_SOLICITUD=$idSol AND  t_solicitudes.ESTADO=1 AND t_movimientos.DESCRIPCION='TOTAL_ABONO'")->result();
        }

        public function TraeRecibo()
        {
            return $this->db->query("SELECT
                t_movimientos.VALOR,
                t_movimientos.CONCEPTO,
                t_movimientos.BANCO,
                t_movimientos.CHEQUE

               FROM t_movimientos

               INNER  JOIN t_solicitudes ON t_solicitudes.ID_SOLICITUD=t_movimientos.ID_SOLICITUD
               WHERE t_movimientos.CONSECUTIVO=" . $this->session->userdata('ConsecutivoRecibo') . "
               AND t_movimientos.TIPO_MOV IS NOT NULL AND  t_solicitudes.ESTADO=1")->result();
        }

        public function InteresesPagadosDeudor($IdSol)
        {
            foreach ($this->db->query("SELECT COUNT(DESCRIPCION) MESES, SUM(VALOR) TOTAL  FROM t_movimientos WHERE TIPO_MOV=1 AND ID_SOLICITUD=" . $IdSol)->result() as $res)
                return ['MESES' => $res->MESES, 'TOTAL' => $res->TOTAL];
        }

        public function TraeInfoRecibo()
        {
            return $this->db->query("SELECT
                t_solicitudes.CAPITAL_INICIAL,
                t_solicitudes.ABONADO,
                t_movimientos.VALOR,
                t_movimientos.FECHA,
                t_movimientos.BANCO,
                t_movimientos.ANULADO,
                t_movimientos.CHEQUE,
                t_deudores.NOMBRE AS NOMBRE_DEUDOR,
                t_deudores.TELEFONO,
                t_deudores.DOCUMENTO AS DOCUMENTO_DEUDOR,
                t_acreedores.NOMBRE AS NOMBRE_ACREEDOR,
                t_inmuebles.DIRECCION AS DIR_INMUEBLE,
                t_usuarios.NOMBRE AS NOMBRE_USUARIO

               FROM t_movimientos

               INNER  JOIN t_solicitudes ON t_solicitudes.ID_SOLICITUD=t_movimientos.ID_SOLICITUD
               INNER  JOIN t_inmuebles ON t_inmuebles.ID_INMUEBLE=t_solicitudes.ID_INMUEBLE
               INNER  JOIN t_deudores ON t_deudores.ID_DEUDOR=t_solicitudes.ID_DEUDOR
               INNER  JOIN t_acreedores ON t_acreedores.ID_ACREEDOR=t_solicitudes.ID_ACREEDOR
               INNER  JOIN t_usuarios USING (ID_USUARIO)

               WHERE t_movimientos.CONSECUTIVO=" . $this->session->userdata('ConsecutivoRecibo') . "
               AND t_movimientos.DESCRIPCION ='TOTAL_RECIBO' AND  t_solicitudes.ESTADO=1")->result();
        }

        public function TraeMovimientosDeudores()
        {
            return $this->db->query("SELECT
                mo.VALOR,
                t_deudores.NOMBRE AS NOMBRE_DEUDOR,
                mo.DESCRIPCION,
                mo.TIPO_MOV,
                mo.CONSECUTIVO

               FROM t_movimientos mo

               INNER  JOIN t_solicitudes USING (ID_SOLICITUD)
               INNER  JOIN t_deudores ON t_deudores.ID_DEUDOR=t_solicitudes.ID_DEUDOR
               INNER  JOIN t_acreedores ON t_acreedores.ID_ACREEDOR=t_solicitudes.ID_ACREEDOR

               WHERE t_acreedores.ID_ACREEDOR=1 AND  mo.TIPO_MOV IS NOT NULL")->result();
        }

        public function AnularRecibo($id)
        {
            $this->db->set('FECHA_ANULA', 'NOW()', false);
            $this->db->update('t_movimientos', ['ANULADO' => 1, 'USUARIO_ANULA' => $this->session->userdata('ID_USUARIO')], ['DESCRIPCION' => 'TOTAL_RECIBO', 'CONSECUTIVO' => $id]);
        }
    }