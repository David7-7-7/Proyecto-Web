<?php
session_start();
require_once 'fpdf/fpdf.php';
require_once 'NominaCalculator.php';

$index = $_GET['index'] ?? null;
if ($index === null || !isset($_SESSION['empleados'][$index])) exit("Empleado no encontrado");

$empleado = $_SESSION['empleados'][$index];
$calc = NominaCalculator::calcularNomina($empleado);

$pdf = new FPDF();
$pdf->AddPage();

// Título y Logo Simulado
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'HERMES INFINITY PROJECTS SAS', 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 5, 'NIT: 900.123.456-1', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(0, 10, 'COMPROBANTE INDIVIDUAL DE PAGO', 0, 1, 'C', true);
$pdf->Ln(5);

// Información del Empleado
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 7, 'Nombre:', 0); $pdf->SetFont('Arial', '', 10); $pdf->Cell(0, 7, utf8_decode($empleado['nombre'] . ' ' . $empleado['apellido']), 0, 1);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 7, 'Documento:', 0); $pdf->SetFont('Arial', '', 10); $pdf->Cell(0, 7, $empleado['codigo'], 0, 1);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 7, 'Cargo:', 0); $pdf->SetFont('Arial', '', 10); $pdf->Cell(0, 7, $empleado['cargo'], 0, 1);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 7, 'Periodo:', 0); $pdf->SetFont('Arial', '', 10); $pdf->Cell(0, 7, $empleado['dias'] . ' dias laborados', 0, 1);
$pdf->Ln(5);

// Tabla de Conceptos
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 8, 'CONCEPTO / DEVENGADOS', 1, 0, 'C', true);
$pdf->Cell(95, 8, 'DEDUCCIONES', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
// Fila 1: Salario vs Salud
$pdf->Cell(60, 8, 'Salario Proporcional', 1);
$pdf->Cell(35, 8, '$ '.number_format($calc['salario_proporcional'],0), 1, 0, 'R');
$pdf->Cell(60, 8, 'Salud (4%)', 1);
$pdf->Cell(35, 8, '$ '.number_format($calc['salud_empleado'],0), 1, 1, 'R');

// Fila 2: Auxilio vs Pension
$pdf->Cell(60, 8, 'Auxilio Transporte', 1);
$pdf->Cell(35, 8, '$ '.number_format($calc['auxilio_transporte'],0), 1, 0, 'R');
$pdf->Cell(60, 8, 'Pension (4%)', 1);
$pdf->Cell(35, 8, '$ '.number_format($calc['pension_empleado'],0), 1, 1, 'R');

$pdf->Ln(10);

// Cuadro de Neto
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 10, 'TOTAL NETO RECIBIDO:', 0, 0, 'R');
$pdf->SetFillColor(44, 62, 80);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(60, 10, '$ ' . number_format($calc['neto_pagar'], 0, ',', '.'), 1, 1, 'C', true);

// Firmas
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(20);
$pdf->Cell(95, 10, '__________________________', 0, 0, 'C');
$pdf->Cell(95, 10, '__________________________', 0, 1, 'C');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(95, 5, 'Firma Empleador', 0, 0, 'C');
$pdf->Cell(95, 5, 'Firma Empleado', 0, 1, 'C');

$pdf->Output('I', 'Desprendible_' . $empleado['nombre'] . '.pdf');