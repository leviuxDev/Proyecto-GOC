<h2><i class="bi bi-shuffle"></i> Emparejamiento Inteligente</h2>
<p class="text-muted">Sugiere parejas de atletas dentro de la misma categoría y rango de combates</p>

<form method="POST" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold text-muted small text-uppercase">Categoría</label>
                <select name="categoria" class="form-select" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach($categorias as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>" <?= ($filtros['categoria'] ?? 0) == $c['id_categoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre_categoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small text-uppercase">Combates Mín</label>
                <input type="number" name="min" min="0" class="form-control" value="<?= htmlspecialchars($filtros['min'] ?? 0) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small text-uppercase">Combates Máx</label>
                <input type="number" name="max" min="0" class="form-control" value="<?= htmlspecialchars($filtros['max'] ?? 100) ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Sugerir</button>
            </div>
        </div>
    </div>
</form>

<?php if(!empty($sugerencias)): ?>
    <div class="row g-4">
        <?php foreach($sugerencias as $s): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <!-- Atleta Local -->
                            <div class="col-5 text-center">
                                <?php if($s['local']['foto']): ?>
                                    <img src="<?= BASE_URL . htmlspecialchars($s['local']['foto']) ?>" 
                                         class="rounded-circle mb-2" style="width:80px;height:80px;object-fit:cover;border:3px solid #c8102e">
                                <?php else: ?>
                                    <i class="bi bi-person-circle text-danger mb-2" style="font-size:4rem;"></i>
                                <?php endif; ?>
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($s['local']['pnombre'] . ' ' . $s['local']['papellido']) ?></h6>
                                <small class="text-muted d-block mb-2"><?= htmlspecialchars($s['local']['nombre_club'] ?? 'Sin club') ?></small>
                                <div class="d-flex justify-content-center gap-1">
                                    <span class="badge bg-success"><?= (int)$s['local']['victorias'] ?>V</span>
                                    <span class="badge bg-danger"><?= (int)$s['local']['derrotas'] ?>D</span>
                                </div>
                            </div>
                            
                            <!-- VS -->
                            <div class="col-2 text-center">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                                    <span class="fw-bold text-danger fs-5">VS</span>
                                </div>
                            </div>
                            
                            <!-- Atleta Visitante -->
                            <div class="col-5 text-center">
                                <?php if($s['visitante']['foto']): ?>
                                    <img src="<?= BASE_URL . htmlspecialchars($s['visitante']['foto']) ?>" 
                                         class="rounded-circle mb-2" style="width:80px;height:80px;object-fit:cover;border:3px solid #0d6efd">
                                <?php else: ?>
                                    <i class="bi bi-person-circle text-primary mb-2" style="font-size:4rem;"></i>
                                <?php endif; ?>
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($s['visitante']['pnombre'] . ' ' . $s['visitante']['papellido']) ?></h6>
                                <small class="text-muted d-block mb-2"><?= htmlspecialchars($s['visitante']['nombre_club'] ?? 'Sin club') ?></small>
                                <div class="d-flex justify-content-center gap-1">
                                    <span class="badge bg-success"><?= (int)$s['visitante']['victorias'] ?>V</span>
                                    <span class="badge bg-danger"><?= (int)$s['visitante']['derrotas'] ?>D</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4 pt-3 border-top">
                            <a class="btn btn-primary rounded-pill px-4" 
                               href="?c=combates&a=form&local=<?= $s['local']['id_atleta'] ?>&visitante=<?= $s['visitante']['id_atleta'] ?>">
                                <i class="bi bi-check-circle"></i> Crear Combate
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php elseif($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="alert alert-info text-center py-4 border-0 shadow-sm">
        <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
        <h5 class="mb-0">No se encontraron atletas elegibles</h5>
        <p class="text-muted mb-0">Intenta ajustar los filtros de búsqueda.</p>
    </div>
<?php endif; ?>