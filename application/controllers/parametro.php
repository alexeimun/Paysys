<?php if(!defined('BASEPATH'))
{
    exit('No direct script access allowed');
}

    class Parametro extends CI_Controller
    {
        public $Data = [];

        function __construct()
        {
            parent::__construct();
            $this->load->model(['clientes_model', 'parametros_model', 'caja_model']);
            if(!$this->session->userdata('ID_USUARIO'))
            {
                redirect('home', 'refresh');
            }
        }

        public function ImportaDeudores()
        {
            $this->parametros_model->ImportaDeudores();
        }

        public function ImportaAcreedores()
        {
            $this->parametros_model->ImportaAcreedores();
        }

        public function pazysalvo()
        {
            if($this->input->is_ajax_request())
            {

            }
            else
            {
                $this->Data['Empresa'] = $this->parametros_model->TraeInformacionEmpresa();
                $this->Params();
                $this->load->view('Parametros/PazySalvo', $this->Data);
            }
        }

        public function Empresa()
        {
            if($this->session->can('empresa'))
            {
                $this->Data['Empresa'] = $this->parametros_model->TraeInformacionEmpresa();
                $this->Params();
                $this->load->view('Parametros/Empresa', $this->Data);
            }
            else
            {
                redirect(site_url(), 'refresh');
            }
        }

        public function Consecutivos()
        {
            if($this->session->can('consecutivos'))
            {
                $this->Data['Consecutivos'] = $this->parametros_model->TraeConsecutivos();
                $this->Params();
                $this->load->view('Parametros/Consecutivos', $this->Data);
            }
            else
            {
                redirect(site_url(), 'refresh');
            }
        }

        public function ExportaDeudores()
        {
            $this->ListarDeudores();
        }

        public function ExportaAcreedores()
        {
            $this->ListarAcreedores();
        }

        public function ExportaCuadreDiario()
        {
            $this->ListarCuadreDiario();
        }

        public function ExportaIngresosDiarios()
        {
            $this->ListarIngresosDiarios();
        }

        public function ActualizaEmpresa()
        {
            if($this->input->is_ajax_request())
            {
                $this->parametros_model->ActualizaEmpresa();
            }
        }

        public function ActualizaConsecutivos()
        {
            $this->parametros_model->ActualizaConsecutivos();
        }

        public function ImportarDeudores()
        {
            if($this->session->can('importar_clientes'))
            {
                $this->Params();
                $this->load->view('Parametros/Clientes/Deudores/ImportarDeudores', $this->Data);
            }
            else
            {
                redirect(site_url(), 'refresh');
            }
        }

        public function ImportarAcreedores()
        {
            if($this->session->can('importar_clientes'))
            {
                $this->Params();
                $this->load->view('Parametros/Clientes/Acreedores/ImportarAcreedores', $this->Data);
            }
            else
            {
                redirect(site_url(), 'refresh');
            }
        }

        public function ExportarDeudores()
        {
            if($this->session->can('exportar_clientes'))
            {
                $this->Params();
                $this->load->view('Parametros/Clientes/Deudores/ExportarDeudores', $this->Data);
            }
            else
            {
                redirect(site_url(), 'refresh');
            }
        }

        public function ExportarAcreedores()
        {
            if($this->session->can('exportar_clientes'))
            {
                $this->Params();
                $this->load->view('Parametros/Clientes/Acreedores/ExportarAcreedores', $this->Data);
            }
            else
            {
                redirect(site_url(), 'refresh');
            }
        }

        public function Params()
        {
            $this->Data['Head'] = $this->load->view('User/Head', [], true);
            $this->Data['Header'] = $this->load->view('User/Header', ['Notify' => $this->notificaciones_model->TraeNotificaciones()], true);
            $this->Data['Sidebar'] = $this->load->view('User/Sidebar', [], true);
            $this->Data['Footer'] = $this->load->view('User/Footer', [], true);
        }

        private function ListarDeudores()
        {
            $deudores = $this->clientes_model->TraeDeudores();
            $table = ' <table id="tabla" class="table table-bordered table-striped">
                                <thead>
                                    <tr><th colspan="8"></th></tr>
                                <tr>
                                    <th style="border-style: dashed" colspan="8" rowspan="2">ARCHIVO EXPORTADO EL <span style="color:green;">' . date('d-m-Y') . '</span> POR ' . $this->session->userdata('NOMBRE_USUARIO') . '</th>
                                    </tr>
                                    <tr><th colspan="8"></th></tr>
                                    <tr><th colspan="8"></th></tr>
                                    <tr>
                                    <th style="background:#3c8dbc;color:white;">Nombre</th>
                                    <th style="background:#3c8dbc;color:white;">Teléfono</th>
                                    <th style="background:#3c8dbc;color:white;">Documento</th>
                                    <th style="background:#3c8dbc;color:white;">Dirección</th>
                                    <th style="background:#3c8dbc;color:white;">Correo</th>
                                    <th style="background:#3c8dbc;color:white;">Ciudad</th>
                                    <th style="background:#3c8dbc;color:white;">Encargado</th>
                                    <th style="background:#3c8dbc;color:white;"> Fecha Registro</th>
                                </tr>
                                </thead>
                                <tbody>';
            foreach ($deudores->result() as $deudor)
            {
                $table .= '<tr>
                 <td>' . $deudor->NOMBRE_DEUDOR . '</td>
                 <td>' . $deudor->TELEFONO . '</td>
                 <td>' . $deudor->DOCUMENTO . '</td>
                 <td>' . $deudor->DIRECCION . '</td>
                 <td>' . $deudor->CORREO . '</td>
                 <td>' . ucfirst(strtolower($deudor->NOMBRE_CIUDAD)) . '</td>
                 <td>' . $deudor->ENCARGADO . '</td>
                 <td>' . $deudor->FECHA_REGISTRA . '</td></tr> ';
            }
            $table .= '</tbody></table>';
            echo $table;
        }

        private function ListarAcreedores()
        {
            $table = ' <table id="tabla" class="table table-bordered table-striped">
                                <thead>
                                    <tr><th colspan="7"></th></tr>
                                <tr>
                                    <th style="border-style: dashed" colspan="7" rowspan="2">ARCHIVO EXPORTADO EL <span style="color:green;">' . date('d-m-Y') . '</span> POR ' . $this->session->userdata('NOMBRE_USUARIO') . '</th>
                                    </tr>
                                    <tr><th colspan="7"></th></tr>
                                    <tr><th colspan="7"></th></tr>
                                    <tr>
                                    <th style="background:#3c8dbc;color:white;">Nombre</th>
                                    <th style="background:#3c8dbc;color:white;">Teléfono</th>
                                    <th style="background:#3c8dbc;color:white;">Documento</th>
                                    <th style="background:#3c8dbc;color:white;">Dirección</th>
                                    <th style="background:#3c8dbc;color:white;">Correo</th>
                                    <th style="background:#3c8dbc;color:white;">Ciudad</th>
                                    <th style="background:#3c8dbc;color:white;"> Fecha Registro</th>
                                </tr>
                                </thead>
                                <tbody>';
            foreach ($this->clientes_model->TraeAcreedores()->result() as $acreedor)
            {
                $table .= '<tr>
                 <td>' . $acreedor->NOMBRE_ACREEDOR . '</td>
                 <td>' . $acreedor->TELEFONO . '</td>
                 <td>' . $acreedor->DOCUMENTO . '</td>
                 <td>' . $acreedor->DIRECCION . '</td>
                 <td>' . $acreedor->CORREO . '</td>
                 <td>' . ucfirst(strtolower($acreedor->NOMBRE_CIUDAD)) . '</td>
                 <td>' . $acreedor->FECHA_REGISTRA . '</td></tr> ';
            }
            $table .= '</tbody></table>';
            echo $table;
        }

        private function ListarCuadreDiario()
        {
            $table = ' <table id="tabla" class="table table-bordered table-striped">
                                <thead>
                                    <tr><th colspan="5"></th></tr>
                                <tr>
                                    <th style="border-style: dashed" colspan="5" rowspan="2">ARCHIVO EXPORTADO EL <span style="color:green;">' . date('d-m-Y') . '</span> POR ' . $this->session->userdata('NOMBRE_USUARIO') . '</th>
                                    </tr>
                                    <tr><th colspan="5"></th></tr>
                                    <tr><th colspan="5"></th></tr>
                                    <tr>
                                    <th style="background:#3c8dbc;color:white;">Recibo</th>
                                    <th style="background:#3c8dbc;color:white;">Valor</th>
                                    <th style="background:#3c8dbc;color:white;">%</th>
                                    <th style="background:#3c8dbc;color:white;">Admon</th>
                                    <th style="background:#3c8dbc;color:white;">Comisión</th>
                                </tr>
                                </thead>
                                <tbody>';
            $TotalComision = 0;
            $TotalAdmin = 0;
            foreach ($this->caja_model->TraeCuadreDiario() as $cuadre)
            {
                $comision = '';
                if($cuadre->TIPO_MOV == 0)
                {
                    $ptge = "0";
                    $admin = '-';
                }
                else if($cuadre->TIPO_MOV == 4)
                {
                    $ptge = '30';
                    $admin = '-';
                    $comision = $cuadre->VALOR * .3;
                    $TotalComision += $comision;
                }
                else
                {
                    $ptge = $cuadre->CUOTA_ADMINISTRACION;
                    $admin = $cuadre->VALOR * ($cuadre->CUOTA_ADMINISTRACION / 100);
                    $TotalAdmin += $admin;
                }
                $table .= '<tr>
                 <td>' . $cuadre->CONSECUTIVO . '</td>
                 <td>' . number_format($cuadre->VALOR, 0, '', '.') . '</td>
                 <td>%' . $ptge . '</td>
                 <td>' . ($admin == '-' ? '- ' : number_format($admin, 0, '', '.')) . '</td>
                 <td>' . ($comision != '' ? number_format($comision, 0, '', '.') : '$ 0') . '</td></tr> ';
            }
            $table .= '</tbody></table>';
            echo $table;
        }

        private function ListarIngresosDiarios()
        {
            $table = ' <table id="tabla" class="table table-bordered table-striped">
                                <thead>
                                    <tr><th colspan="4"></th></tr>
                                <tr>
                                    <th style="border-style: dashed" colspan="4" rowspan="2">ARCHIVO EXPORTADO EL <span style="color:green;">' . date('d-m-Y') . '</span> POR ' . $this->session->userdata('NOMBRE_USUARIO') . '</th>
                                    </tr>
                                    <tr><th colspan="4"></th></tr>
                                    <tr><th colspan="4">' . $this->input->post('FECHA') . '</th></tr>
                                    <tr>
                                    <th style="background:#3c8dbc;color:white;">Recibo</th>
                                    <th style="background:#3c8dbc;color:white;">Deudor</th>
                                    <th style="background:#3c8dbc;color:white;">Acreedor</th>
                                    <th style="background:#3c8dbc;color:white;">Valor</th>
                                </tr>
                                </thead>
                                <tbody>';

            foreach ($this->caja_model->TraeIngresosDiarios() as $ingreso)
            {
                $table .= '<tr>
                 <td>' . $ingreso->CONSECUTIVO . '</td>
                 <td>' . $ingreso->NOMBRE_DEUDOR . '</td>
                 <td>' . $ingreso->NOMBRE_ACREEDOR . '</td>
                 <td>' . number_format($ingreso->VALOR, 0, '', '.') . '</td>
                 </tr> ';
            }
            $table .= '</tbody></table>';
            echo $table;
        }
    }