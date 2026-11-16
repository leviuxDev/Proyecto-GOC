<?php
/**
 * Vista: Listado de Combates
 * 
 * Recibe: $combates (array con todos los combates)
 */
$rol = $_SESSION['usuario']['rol'] ?? 'Consulta';
?>

<div class="d-flex justify-content-between mb-3">
    <h2><i class="bi bi-fire"></i> Combates</h2>
    <div>
        <?php if($rol !== 'Consulta'): ?>
            <a href="?c=combates&a=sugerir" class="btn btn-warning">
                <i class="bi bi-shuffle"></i> Emparejamiento
            </a>
            <a href="?c=combates&a=form" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo Combate
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover datatable mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Torneo</th>
                        <th>Local</th>
                        <th>Visitante</th>
                        <th class="text-center">Ronda</th>
                        <th class="text-center">Decisión</th>
                        <th class="text-center">Resultado</th>
                        <th class="text-center" style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($combates as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['fecha']) ?></td>
                        <td><?= htmlspecialchars($c['nombre_torneo'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($c['local_nombre'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($c['visitante_nombre'] ?? 'N/A') ?></td>
                        <td class="text-center"><?= (int)$c['ronda'] ?></td>
                        <td class="text-center"><?= htmlspecialchars($c['decision_victoria'] ?? '-') ?></td>
                        <td class="text-center">
                            <?php 
                            $color = 'secondary';
                            if ($c['resultado'] === 'Pendiente') $color = 'warning';
                            elseif ($c['resultado'] === 'Local') $color = 'success';
                            elseif ($c['resultado'] === 'Visitante') $color = 'danger';
                            elseif ($c['resultado'] === 'Empate') $color = 'info';
                            ?>
                            <span class="badge bg-<?= $color ?> rounded-pill px-3">
                                <?= htmlspecialchars($c['resultado']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                <a class="btn btn-sm btn-info" href="?c=combates&a=ver&id=<?= $c['id_combate'] ?>" title="Ver Ficha">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if($rol !== 'Consulta'): ?>
                                    <a class="btn btn-sm btn-warning" href="?c=combates&a=form&id=<?= $c['id_combate'] ?>" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if($rol === 'Administrador'): ?>
                                    <a class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('¿Estás seguro de eliminar este combate?')" 
                                       href="?c=combates&a=eliminar&id=<?= $c['id_combate'] ?>" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(empty($combates)): ?>
<div class="alert alert-info text-center py-4">
    <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
    <h5 class="mb-1">No hay combates registrados</h5>
    <?php if($rol !== 'Consulta'): ?>
        <a href="?c=combates&a=form" class="btn btn-primary mt-2">Registra el primero</a>
    <?php endif; ?>
</div>
<?php endif; ?>