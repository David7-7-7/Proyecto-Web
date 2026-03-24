<?php
session_start();
require_once 'fpdf/fpdf.php';
require_once 'NominaCalculator.php';

$empleados = $_SESSION['empleados'] ?? [];
if (empty($empleados)) {
    exit("No hay empleados registrados para generar el PDF.");
}

// Obtenemos los cálculos procesados por la clase
$resultado = NominaCalculator::calculoNominaCompleta($empleados);

// Crear PDF en Orientación Horizontal (L) para que quepa toda la tabla
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// --- TÍTULO ---
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 15, utf8_decode('REPORTE DE RESULTADOS NÓMINA'), 0, 1, 'C');
$pdf->Ln(5);

// --- ENCABEZADO DE TABLA (Estilo Oscuro) ---
$pdf->SetFillColor(26, 26, 26); // Color #1a1a1a
$pdf->SetTextColor(255, 255, 255); // Texto Blanco
$pdf->SetDrawColor(50, 50, 50);    // Bordes grises
$pdf->SetFont('Arial', 'B', 8);

// Anchos de columnas (Total 277mm para A4 Horizontal)
$w = [25, 25, 22, 10, 18, 22, 10, 28, 28, 25, 25, 30];

$pdf->Cell($w[0], 10, 'Nombre', 1, 0, 'C', true);
$pdf->Cell($w[1], 10, 'Apellido', 1, 0, 'C', true);
$pdf->Cell($w[2], 10, 'Rol', 1, 0, 'C', true);
$pdf->Cell($w[3], 10, 'Dias', 1, 0, 'C', true);
$pdf->Cell($w[4], 10, 'Nocturnos', 1, 0, 'C', true);
$pdf->Cell($w[5], 10, 'EPS', 1, 0, 'C', true);
$pdf->Cell($w[6], 10, 'ARL', 1, 0, 'C', true);
$pdf->Cell($w[7], 10, 'Salario Base', 1, 0, 'C', true);
$pdf->Cell($w[8], 10, 'Auxilio Transp.', 1, 0, 'C', true);
$pdf->Cell($w[9], 10, 'Salud (4%)', 1, 0, 'C', true);
$pdf->Cell($w[10], 10, 'Pension (4%)', 1, 0, 'C', true);
$pdf->Cell($w[11], 10, 'Neto a Pagar', 1, 1, 'C', true);

// --- CUERPO DE LA TABLA ---
$pdf->SetTextColor(0, 0, 0); // Texto Negro para los datos
$pdf->SetFont('Arial', '', 8);
$pdf->SetFillColor(255, 255, 255);

foreach ($resultado['detalles'] as $r) {
    if ($r['estado'] !== 'ok') continue;
    
    $emp = $r['empleado'];
    $c = $r['calculos'];
    
    // Para que las celdas tengan un fondo sutil si quieres (opcional)
    $pdf->Cell($w[0], 8, utf8_decode($emp['nombre']), 1, 0, 'L');
    $pdf->Cell($w[1], 8, utf8_decode($emp['apellido']), 1, 0, 'L');
    $pdf->Cell($w[2], 8, utf8_decode($emp['cargo']), 1, 0, 'C');
    $pdf->Cell($w[3], 8, $emp['dias'], 1, 0, 'C');
    $pdf->Cell($w[4], 8, $emp['nocturnos'], 1, 0, 'C');
    $pdf->Cell($w[5], 8, utf8_decode($emp['eps']), 1, 0, 'C');
    $pdf->Cell($w[6], 8, $emp['nivel_arl'], 1, 0, 'C');
    $pdf->Cell($w[7], 8, '$ '.number_format($c['salario_proporcional'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell($w[8], 8, '$ '.number_format($c['auxilio_transporte'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell($w[9], 8, '$ '.number_format($c['salud_empleado'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell($w[10], 8, '$ '.number_format($c['pension_empleado'], 0, ',', '.'), 1, 0, 'R');
    
    $pdf->SetFont('Arial', 'B', 8); // Resaltar el Neto
    $pdf->Cell($w[11], 8, '$ '.number_format($c['neto_pagar'], 0, ',', '.'), 1, 1, 'R');
    $pdf->SetFont('Arial', '', 8);
}

// --- FILA DE TOTALES (Fondo Blanco, Texto Negro en Negrita) ---
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(255, 255, 255);

// Sumamos los valores para la fila final
$sumSalario = array_sum(array_column(array_column($resultado['detalles'], 'calculos'), 'salario_proporcional'));
$sumAuxilio = array_sum(array_column(array_column($resultado['detalles'], 'calculos'), 'auxilio_transporte'));
$sumDeducciones = $resultado['totales']['total_deducciones'] / 2; // Para Salud y Pensión por separado

// Etiqueta TOTALES (ocupa las primeras 7 columnas)
$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5]+$w[6], 10, 'TOTALES', 1, 0, 'C', true);

// Valores totales
$pdf->Cell($w[7], 10, '$ '.number_format($sumSalario, 0, ',', '.'), 1, 0, 'R', true);
$pdf->Cell($w[8], 10, '$ '.number_format($sumAuxilio, 0, ',', '.'), 1, 0, 'R', true);
$pdf->Cell($w[9], 10, '$ '.number_format($sumDeducciones, 0, ',', '.'), 1, 0, 'R', true);
$pdf->Cell($w[10], 10, '$ '.number_format($sumDeducciones, 0, ',', '.'), 1, 0, 'R', true);
$pdf->Cell($w[11], 10, '$ '.number_format($resultado['totales']['total_neto'], 0, ',', '.'), 1, 1, 'R', true);

$pdf->Output('I', 'Nomina_General_Completa.pdf');