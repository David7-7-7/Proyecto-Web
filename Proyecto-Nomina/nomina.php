<?php
session_start();
require_once 'NominaCalculator.php';

// Se recuperan los empleados guardados en la sesión, si no hay, queda un vector vacío
$empleados = $_SESSION['empleados'] ?? [];

// Validación: Si no hay nadie registrado, se muestra un aviso 
if (empty($empleados)) {
    echo "<div style='text-align:center; margin-top:50px; font-family:Arial;'>";
    echo "<h2>No hay empleados registrados en la sesión.</h2>";
    echo "<a href='Frontend/index.html'>Volver al registro</a>";
    echo "</div>";
    exit;
}

// Se ejecuta el cálculo masivo usando la clase NominaCalculator
$resultado = NominaCalculator::calculoNominaCompleta($empleados);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Nómina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos personalizados para que quede en modo oscuro} */
        body { background-color: #121212; color: white; }
        .table { color: white; border-color: #444; }
        .table-dark { background-color: #1f1f1f; }
        .btn-pdf-general { background-color: #0d6efd; border: none; }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Resultados Nómina</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-hover text-center">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Rol</th>
                    <th>Días</th>
                    <th>Nocturnos</th>
                    <th>EPS</th>
                    <th>ARL</th>
                    <th>Salario Base</th>
                    <th>Auxilio Transp.</th>
                    <th>Salud (4%)</th>
                    <th>Pensión (4%)</th>
                    <th>Neto a Pagar</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultado['detalles'] as $r): ?>
                    <?php 
                        // Verificación de errores: Si un empleado tiene datos inválidos, se muestra una fila roja
                        if ($r['estado'] === 'error'): 
                    ?>
                        <tr class="table-danger text-dark">
                            <td colspan="12">
                                Error en datos de: <?= ($r['empleado']['nombre'] ?? 'Sin nombre') ?> 
                                (<?= implode(", ", $r['errores']) ?>)
                            </td>
                            <td><a href="empleados.php?accion=listar" class="btn btn-sm btn-secondary">Corregir</a></td>
                        </tr>
                    <?php 
                        continue; // Salta al siguiente empleado sin dibujar la fila de nómina
                        endif; 

                        // Variables simplificadas para facilitar la lectura del HTML
                        $emp = $r['empleado'];
                        $c = $r['calculos'];
                    ?>
                    <tr>
                        <td><?= $emp['nombre'] ?></td>
                        <td><?= $emp['apellido'] ?></td>
                        <td><?= $emp['cargo'] ?? 'N/A' ?></td>
                        <td><?= $emp['dias'] ?></td>
                        <td><?= $emp['nocturnos'] ?></td>
                        <td><?= $emp['eps'] ?></td>
                        <td><?= $emp['nivel_arl'] ?></td>
                        <td>$ <?= number_format($emp['salario'], 0, ',', '.') ?></td>
                        <td>$ <?= number_format($c['auxilio_transporte'], 0, ',', '.') ?></td>
                        <td>$ <?= number_format($c['salud_empleado'], 0, ',', '.') ?></td>
                        <td>$ <?= number_format($c['pension_empleado'], 0, ',', '.') ?></td>
                        <td class="table-active"><b>$ <?= number_format($c['neto_pagar'], 0, ',', '.') ?></b></td>
                        <td>
                            <a href="pdf_desprendible.php?index=<?= $r['index'] ?>" class="btn btn-danger btn-sm">PDF</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light text-dark">
                <tr>
                    <th colspan="7">TOTALES </th>
                    <th>$ <?= number_format(array_sum(array_column(array_column($resultado['detalles'], 'calculos'), 'salario_proporcional')), 0, ',', '.') ?></th>
                    
                    <th>$ <?= number_format(array_sum(array_column(array_column($resultado['detalles'], 'calculos'), 'auxilio_transporte')), 0, ',', '.') ?></th>
                    
                    <th>$ <?= number_format($resultado['totales']['total_deducciones'] / 2, 0, ',', '.') ?></th>
                    
                    <th>$ <?= number_format($resultado['totales']['total_deducciones'] / 2, 0, ',', '.') ?></th>
                    
                    <th>$ <?= number_format($resultado['totales']['total_neto'], 0, ',', '.') ?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-3">
        <a href="empleados.php?accion=listar" class="btn btn-secondary">Volver a la Lista</a>
        
        <form action="pdf_nomina.php" method="POST">
            <?php foreach ($resultado['detalles'] as $r): ?>
                <?php if ($r['estado'] === 'ok'): ?>
                    <input type="hidden" name="indices[]" value="<?= $r['index'] ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">
                Generar PDF General
            </button>
        </form>
    </div>
</div>

</body>
</html>