<?php

class NominaCalculator
{
    //CONSTANTES 
    const SMLV                   = 1750905;  
    const AUX_TRANSPORTE_MENSUAL = 249095;   
    const DIAS_MES               = 30;      

    //Deducciones empleado
    const SALUD_EMPLEADO         = 0.04;     
    const PENSION_EMPLEADO       = 0.04;      
    //Aportes empleador
    const SALUD_EMPLEADOR        = 0.085;    // 8.5%  
    const PENSION_EMPLEADOR      = 0.12;     // 12%   
    const SENA                   = 0.02;     // 2%   
    const ICBF                   = 0.03;     // 3%    
    const CAJA_COMPENSACION      = 0.04;     // 4%   
    //Niveles ARL (riesgos: oficina,procesos manofactureros,etc)
    const ARL_NIVELES = [
        1 => 0.00522,
        2 => 0.01044,
        3 => 0.02436,
        4 => 0.04350,
        5 => 0.06960,
    ];

    //Prestaciones sociales 
    const PRIMA         = 0.0833;   
    const CESANTIAS     = 0.0833;   
    const INT_CESANTIAS = 0.12;     
    const VACACIONES    = 0.0417;    

    //Validar empleado

    public static function validarEmpleado(array $datos): array
    {
        $errores = [];

        //Nombre: obligatorio, mínimo 3 caracteres
        $nombre = trim($datos['nombre'] ?? '');
        if (empty($nombre)) {
            $errores['nombre'] = 'El nombre es obligatorio.';
        } elseif (strlen($nombre) < 3) {
            $errores['nombre'] = 'El nombre debe tener al menos 3 caracteres.';
        }

        //Documento: obligatorio, solo dígitos, entre 7 y 12 caracteres
        $documento = trim($datos['documento'] ?? '');
        if (empty($documento)) {
            $errores['documento'] = 'El documento es obligatorio.';
        } elseif (!ctype_digit($documento)) {
            $errores['documento'] = 'El documento debe contener solo números.';
        } elseif (strlen($documento) < 7 || strlen($documento) > 12) {
            $errores['documento'] = 'El documento debe tener entre 7 y 12 dígitos.';
        }

        //Cargo: obligatorio, mínimo 2 caracteres
        $cargo = trim($datos['cargo'] ?? '');
        if (empty($cargo)) {
            $errores['cargo'] = 'El cargo es obligatorio.';
        } elseif (strlen($cargo) < 2) {
            $errores['cargo'] = 'El cargo debe tener al menos 2 caracteres.';
        }

        //Salario: numérico y >= SMLV 2026
        $salario = $datos['salario'] ?? null;
        if (!is_numeric($salario)) {
            $errores['salario'] = 'El salario debe ser un valor numérico.';
        } elseif ((float)$salario < self::SMLV)/*compara con nuestro salario minimo*/  {
            $errores['salario'] = 'El salario no puede ser inferior al SMLV 2026 ($'
                . number_format(self::SMLV, 0, ',', '.') . ').';/*solo es para que se vea bonito el numero lo separa con comas :D */ 
        }

        //Días chambeados: entero entre 1 y 30
        $dias = $datos['dias'] ?? null;
        if (!is_numeric($dias)) {
            $errores['dias'] = 'Los días laborados deben ser numéricos.';
        } elseif ((int)$dias < 1 || (int)$dias > 30) {
            $errores['dias'] = 'Los días laborados deben estar entre 1 y 30.';
        }

        //Vacaciones disfrutadas: opcional, entre 0 y 30
        $vac = $datos['vacaciones_disfrutadas'] ?? 0;
        if (!is_numeric($vac) || (int)$vac < 0 || (int)$vac > 30) {
            $errores['vacaciones_disfrutadas'] = 'Las vacaciones deben estar entre 0 y 30 días.';
        }

        //Alimentación no prestacional: opcional, no negativo
        $alim = $datos['alimentacion'] ?? 0;
        if (!is_numeric($alim) || (float)$alim < 0) {
            $errores['alimentacion'] = 'El auxilio de alimentación no puede ser negativo.';
        }

        //Nivel ARL: entre 1 y 5
        $arl = (int)($datos['nivel_arl'] ?? 1);
        if ($arl < 1 || $arl > 5) {
            $errores['nivel_arl'] = 'El nivel ARL debe estar entre 1 y 5.';
        }

        //Transporte: 'auto', 'si' o 'no'
        $transp = $datos['transporte'] ?? 'auto';
        if (!in_array($transp, ['auto', 'si', 'no'])) {
            $errores['transporte'] = 'El campo transporte debe ser: auto, si o no.';
        }

        return [
            'valido'  => empty($errores),
            'errores' => $errores,
        ];
    }

    //CALCULOS

    /**
     * Calcula todo lo de nómina de un empleado.
     */
    public static function calcularNomina(array $datos): array
    {
        $salario        = (float)$datos['salario'];
        $dias           = (int)$datos['dias'];
        $vacDisfrutadas = (int)($datos['vacaciones_disfrutadas'] ?? 0);
        $alim           = (float)($datos['alimentacion'] ?? 0);
        $arlNivel       = (int)($datos['nivel_arl'] ?? 1);
        $transporte     = $datos['transporte'] ?? 'auto';
        //Devengados 
        $salarioProp = ($salario / self::DIAS_MES) * $dias;
        $tope        = self::SMLV * 2;  // $3.501.810
        $auxTransp   = 0;
        if ($transporte === 'si' || ($transporte === 'auto' && $salario <= $tope)) {
            $auxTransp = (self::AUX_TRANSPORTE_MENSUAL / self::DIAS_MES) * $dias;
        }
        $vacPagadas     = ($vacDisfrutadas > 0) ? ($salario / self::DIAS_MES) * $vacDisfrutadas : 0;
        $totalDevengado = $salarioProp + $auxTransp + $vacPagadas + $alim;
        //IBC (base de cotización este se usa mas que todo para la salud y pension) 
        //excepciones: aux. transporte y alimentación no prestacional
        $ibc = $salarioProp;
        //Deducciones empleado
        $saludEmp   = $ibc * self::SALUD_EMPLEADO;
        $pensionEmp = $ibc * self::PENSION_EMPLEADO;
        $totalDed   = $saludEmp + $pensionEmp;
        $netoPagar  = $totalDevengado - $totalDed;

        //Aportes empleador
        $saludEmpr   = $ibc * self::SALUD_EMPLEADOR;
        $pensionEmpr = $ibc * self::PENSION_EMPLEADOR;
        $arlPct      = self::ARL_NIVELES[$arlNivel] ?? self::ARL_NIVELES[1];
        $arl         = $ibc * $arlPct;
        $sena        = $ibc * self::SENA;
        $icbf        = $ibc * self::ICBF;
        $caja        = $ibc * self::CAJA_COMPENSACION;
        $totalEmpr   = $saludEmpr + $pensionEmpr + $arl + $sena + $icbf + $caja;

        //Prestaciones sociales (mensual)
        $prima        = $salarioProp * self::PRIMA;
        $cesantias    = $salarioProp * self::CESANTIAS;
        $intCes       = $cesantias   * self::INT_CESANTIAS;
        $vacProv      = $salarioProp * self::VACACIONES;
        $totalPrest   = $prima + $cesantias + $intCes + $vacProv;

        //Costo total empresa
        $costoEmpresa = $totalDevengado + $totalEmpr + $totalPrest;

        return [
            //Devengados
            'salario_proporcional'    => self::r($salarioProp),
            'auxilio_transporte'      => self::r($auxTransp),
            'vacaciones_pagadas'      => self::r($vacPagadas),
            'alimentacion_no_prest'   => self::r($alim),
            'total_devengado'         => self::r($totalDevengado),

            //IBC
            'ibc'                     => self::r($ibc),

            //Deducciones empleado
            'salud_empleado'          => self::r($saludEmp),
            'pension_empleado'        => self::r($pensionEmp),
            'total_deducciones'       => self::r($totalDed),
            'neto_pagar'              => self::r($netoPagar),

            //Aportes empleador
            'salud_empleador'         => self::r($saludEmpr),
            'pension_empleador'       => self::r($pensionEmpr),
            'arl'                     => self::r($arl),
            'arl_porcentaje'          => round($arlPct * 100, 3),/*para mostrarlo en porcentaje y como mucho 3 decimales*/ 
            'sena'                    => self::r($sena),
            'icbf'                    => self::r($icbf),
            'caja_compensacion'       => self::r($caja),
            'total_aportes_empleador' => self::r($totalEmpr),

            //Prestaciones sociales
            'prima'                   => self::r($prima),
            'cesantias'               => self::r($cesantias),
            'intereses_cesantias'     => self::r($intCes),
            'vacaciones_provision'    => self::r($vacProv),
            'total_prestaciones'      => self::r($totalPrest),

            //Costo empresa
            'costo_empresa_mensual'   => self::r($costoEmpresa),
        ];
    }

    /**
     * Nómina de N empleados y acumula los totales.
     */
    public static function calculoNominaCompleta(array $empleados): array
    {
        $resultados = [];
        $totales = [
            'total_devengado'         => 0,
            'total_deducciones'       => 0,
            'total_neto'              => 0,
            'total_aportes_empleador' => 0,
            'total_prestaciones'      => 0,
            'costo_empresa_total'     => 0,
        ];
//Recorre cada empleado; si tiene datos invalidos, registra error y continúa con el siguiente :p
        foreach ($empleados as $i => $emp) {
            $val = self::validarEmpleado($emp);

            if (!$val['valido']) {
                $resultados[] = [
                    'index'    => $i,
                    'empleado' => $emp,
                    'errores'  => $val['errores'],
                    'calculos' => null,
                    'estado'   => 'error',
                ];
                continue; // Salta al siguiente empleado sin acumularlo
            }
            // Solo empleados válidos llegan aquí
            $calc = self::calcularNomina($emp);

            $resultados[] = [
                'index'    => $i,
                'empleado' => $emp,
                'calculos' => $calc,
                'errores'  => null,
                'estado'   => 'ok',
            ];

            $totales['total_devengado']         += $calc['total_devengado'];
            $totales['total_deducciones']       += $calc['total_deducciones'];
            $totales['total_neto']              += $calc['neto_pagar'];
            $totales['total_aportes_empleador'] += $calc['total_aportes_empleador'];
            $totales['total_prestaciones']      += $calc['total_prestaciones'];
            $totales['costo_empresa_total']     += $calc['costo_empresa_mensual'];
        }
        // Redondea todos los totales acumulados a pesos :D
        foreach ($totales as $k => $v) {
            $totales[$k] = self::r($v);
        }
        // se devuelve cantidad procesados(que esten bien), cantidad con error, detalles por empleado y totales generales
        return [
            'procesados' => count(array_filter($resultados, fn($r) => $r['estado'] === 'ok')),
            'con_error'  => count(array_filter($resultados, fn($r) => $r['estado'] === 'error')),
            'detalles'   => $resultados,
            'totales'    => $totales,
        ];
    }
    //Esta funcion es para no tener que llamar a round cada rato 
   private static function r(float $v): int
    {
         return (int)round($v);
    }
}