<?php
$local = (new Atleta())->obtener($combate['id_atleta_local']);
$visitante = (new Atleta())->obtener($combate['id_atleta_visitante']);

$fotoLocal = $local['foto'] ?? null;
$nombreLocal = isset($local['pnombre']) ? $local['pnombre'] . ' ' . $local['papellido'] : 'N/A';
$clubLocal = $local['nombre_club'] ?? 'Sin club';

$fotoVisitante = $visitante['foto'] ?? null;
$nombreVisitante = isset($visitante['pnombre']) ? $visitante['pnombre'] . ' ' . $visitante['papellido'] : 'N/A';
$clubVisitante = $visitante['nombre_club'] ?? 'Sin club';

$resultado = $combate['resultado'];
$colorRes = 'secondary';
$textoRes = $resultado;
$ganadorNombre = '';

if ($resultado === 'Local') {
    $colorRes = 'success';
    $textoRes = 'GANA LOCAL';
    $ganadorNombre = $nombreLocal;
} elseif ($resultado === 'Visitante') {
    $colorRes = 'success';
    $textoRes = 'GANA VISITANTE';
    $ganadorNombre = $nombreVisitante;
} elseif ($resultado === 'Empate') {
    $colorRes = 'secondary';
    $textoRes = 'EMPATE';
} elseif ($resultado === 'Pendiente') {
    $colorRes = 'warning';
    $textoRes = 'PENDIENTE';
} elseif ($resultado === 'No Contest') {
    $colorRes = 'dark';
    $textoRes = 'SIN DECISIÓN';
}
?>

<div class="d-flex justify-content-between mb-4">
    <a href="?c=combates&a=index" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left"></i> Volver a la lista
    </a>
</div>

<!-- 1. CARTELERA DEL COMBATE (FIGHT CARD) -->
<div class="card border-0 shadow-lg mb-4" style="border-radius: 20px; overflow: hidden; background-color: #f8fafc;">
    <div class="card-body p-0">
        <div class="row g-0 align-items-center text-center bg-white">
            
            <!-- Atleta Local -->
            <div class="col-md-5 p-4 <?= $resultado === 'Local' ? 'bg-success bg-opacity-10' : '' ?>">
                <div class="position-relative d-inline-block mb-3">
                    <?php if($fotoLocal): ?>
                        <img src="<?= BASE_URL . htmlspecialchars($fotoLocal) ?>" class="rounded-circle shadow-sm" style="width: 130px; height: 130px; object-fit: cover; border: 4px solid #dc3545;">
                    <?php else: ?>
                        <i class="bi bi-person-circle text-danger" style="font-size: 7.5rem;"></i>
                    <?php endif; ?>
                    <?php if($resultado === 'Local'): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success border border-white border-3 p-2 shadow"><i class="bi bi-trophy-fill fs-5"></i></span>
                    <?php endif; ?>
                </div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($nombreLocal) ?></h4>
                <p class="text-muted small mb-2"><i class="bi bi-building"></i> <?= htmlspecialchars($clubLocal) ?></p>
                <span class="badge bg-danger rounded-pill px-3 py-2">🥊 LOCAL</span>
            </div>

            <!-- Centro: Resultado Protagonista -->
            <div class="col-md-2 p-4 text-center d-flex flex-column justify-content-center align-items-center bg-white">
                <div class="bg-<?= $colorRes ?> text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-lg mb-3" style="width: 90px; height: 90px;">
                    <i class="bi bi-trophy-fill fs-1"></i>
                </div>
                <h5 class="fw-bold text-<?= $colorRes ?> mb-1"><?= $textoRes ?></h5>
                <div class="text-muted small fw-bold"><?= (int)$combate['rounds_pautados'] ?> ROUNDS</div>
            </div>

            <!-- Atleta Visitante -->
            <div class="col-md-5 p-4 <?= $resultado === 'Visitante' ? 'bg-success bg-opacity-10' : '' ?>">
                <div class="position-relative d-inline-block mb-3">
                    <?php if($fotoVisitante): ?>
                        <img src="<?= BASE_URL . htmlspecialchars($fotoVisitante) ?>" class="rounded-circle shadow-sm" style="width: 130px; height: 130px; object-fit: cover; border: 4px solid #0d6efd;">
                    <?php else: ?>
                        <i class="bi bi-person-circle text-primary" style="font-size: 7.5rem;"></i>
                    <?php endif; ?>
                    <?php if($resultado === 'Visitante'): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success border border-white border-3 p-2 shadow"><i class="bi bi-trophy-fill fs-5"></i></span>
                    <?php endif; ?>
                </div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($nombreVisitante) ?></h4>
                <p class="text-muted small mb-2"><i class="bi bi-building"></i> <?= htmlspecialchars($clubVisitante) ?></p>
                <span class="badge bg-primary rounded-pill px-3 py-2">✈️ VISITANTE</span>
            </div>

        </div>
        
        <!-- Banner Inferior del Ganador (Dorado Suave y con márgenes) -->
        <?php if ($ganadorNombre): ?>
        <div class="m-3 p-3 text-center rounded-4" style="background: linear-gradient(135deg, #fef08a 0%, #fde047 100%); border: 1px solid #eab308; box-shadow: 0 4px 12px rgba(234, 179, 8, 0.2);">
            <h5 class="mb-0 fw-bold text-uppercase" style="color: #713f12; letter-spacing: 1px;">
                <i class="bi bi-trophy-fill me-2" style="color: #ca8a04;"></i> Ganador: <?= htmlspecialchars($ganadorNombre) ?>
            </h5>
        </div>
        <?php elseif ($resultado === 'Empate'): ?>
        <div class="m-3 p-3 text-center rounded-4" style="background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%); border: 1px solid #94a3b8; box-shadow: 0 4px 12px rgba(148, 163, 184, 0.2);">
            <h5 class="mb-0 fw-bold text-uppercase" style="color: #334155; letter-spacing: 1px;">
                <i class="bi bi-hand-thumbs-up-fill me-2" style="color: #64748b;"></i> Empate
            </h5>
        </div>
        <?php elseif ($resultado === 'Pendiente'): ?>
        <div class="m-3 p-3 text-center rounded-4" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #f59e0b; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);">
            <h5 class="mb-0 fw-bold text-uppercase" style="color: #92400e; letter-spacing: 1px;">
                <i class="bi bi-clock-history me-2" style="color: #d97706;"></i> Combate Pendiente
            </h5>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- 2. DETALLES DEL COMBATE -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4 border-bottom pb-3"><i class="bi bi-info-circle text-primary me-2"></i> Información del Combate</h5>
                <div class="row g-4">
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Fecha</small>
                        <span class="fw-semibold fs-6"><?= htmlspecialchars($combate['fecha']) ?></span>
                    </div>
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Torneo</small>
                        <span class="fw-semibold fs-6 text-truncate d-block"><?= htmlspecialchars($combate['nombre_torneo'] ?? '-') ?></span>
                    </div>
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Ronda en Llave</small>
                        <span class="fw-semibold fs-6"><?= (int)$combate['ronda'] ?></span>
                    </div>
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Decisión</small>
                        <span class="fw-semibold fs-6"><?= htmlspecialchars($combate['decision_victoria'] ?? 'Pendiente') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. TARJETAS DE PUNTUACIÓN -->
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4 border-bottom pb-3"><i class="bi bi-clipboard-data text-success me-2"></i> Tarjetas de Puntuación</h5>
                
                <?php if(!empty($tarjetas)): ?>
                    <!-- Resumen rápido -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <h6 class="text-muted mb-1 small text-uppercase">Rounds Registrados</h6>
                                <h3 class="mb-0 fw-bold"><?= count($tarjetas) ?></h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <h6 class="text-muted mb-1 small text-uppercase">Jueces</h6>
                                <h3 class="mb-0 fw-bold"><?= count($resumenJueces) ?></h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <h6 class="text-muted mb-1 small text-uppercase">Rounds Pautados</h6>
                                <h3 class="mb-0 fw-bold"><?= $combate['rounds_pautados'] ?? '-' ?></h3>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de puntuaciones -->
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Juez</th>
                                    <th class="text-center">Round</th>
                                    <th class="text-center text-danger">Pts Local 🥊</th>
                                    <th class="text-center text-primary">Pts Visitante ✈️</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalesLocal = 0; $totalesVisitante = 0;
                                foreach($tarjetas as $t): 
                                    $totalesLocal += $t['puntos_local'];
                                    $totalesVisitante += $t['puntos_visitante'];
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($t['nombre_juez'] ?? 'N/A') ?></td>
                                    <td class="text-center"><span class="badge bg-dark rounded-pill"><?= (int)$t['numero_round'] ?></span></td>
                                    <td class="text-center fw-bold text-danger"><?= (int)$t['puntos_local'] ?></td>
                                    <td class="text-center fw-bold text-primary"><?= (int)$t['puntos_visitante'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light border-top-2">
                                <tr class="fw-bold">
                                    <td colspan="2" class="text-end text-uppercase">Totales:</td>
                                    <td class="text-center text-danger fs-5"><?= $totalesLocal ?></td>
                                    <td class="text-center text-primary fs-5"><?= $totalesVisitante ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Resumen por Juez -->
                    <?php if(!empty($resumenJueces)): ?>
                    <h6 class="mt-4 mb-3 fw-bold">Resumen por Juez</h6>
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Juez</th>
                                    <th class="text-center">Rounds</th>
                                    <th class="text-center">Total Local</th>
                                    <th class="text-center">Total Visitante</th>
                                    <th class="text-center">Ganador</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($resumenJueces as $r): 
                                    $ganador = 'Empate';
                                    if($r['total_local'] > $r['total_visitante']) $ganador = 'Local';
                                    elseif($r['total_visitante'] > $r['total_local']) $ganador = 'Visitante';
                                    $colorGanador = $ganador === 'Local' ? 'danger' : ($ganador === 'Visitante' ? 'primary' : 'secondary');
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($r['nombre_juez']) ?></td>
                                    <td class="text-center"><?= (int)$r['rounds'] ?></td>
                                    <td class="text-center fw-bold text-danger"><?= (int)$r['total_local'] ?></td>
                                    <td class="text-center fw-bold text-primary"><?= (int)$r['total_visitante'] ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $colorGanador ?> rounded-pill px-3">
                                            <?= $ganador ?> <?= $ganador !== 'Empate' ? '<i class="bi bi-trophy-fill ms-1"></i>' : '' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Estado vacío -->
                    <div class="text-center py-5 bg-light rounded-4">
                        <i class="bi bi-clipboard-x text-secondary" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 fw-bold">Sin tarjetas registradas</h5>
                        <p class="text-muted mb-0">Aún no se han ingresado puntuaciones para este combate.</p>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>