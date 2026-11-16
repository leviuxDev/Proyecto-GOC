<?php
/** Modelo TarjetaPuntuacion */
class TarjetaPuntuacion extends Model {
    
    public function guardarTarjetas(int $idCombate, array $tarjetas): void {
        try {
            $this->db->prepare("DELETE FROM tarjetas_puntuacion WHERE id_combate = ?")->execute([$idCombate]);
            if (empty($tarjetas)) return;
            
            $stmt = $this->db->prepare("INSERT INTO tarjetas_puntuacion (id_combate, id_juez, numero_round, puntos_local, puntos_visitante) VALUES (?, ?, ?, ?, ?)");
            foreach ($tarjetas as $t) {
                $stmt->execute([
                    $idCombate,
                    $t['id_juez'] ?? null,
                    $t['numero_round'] ?? 1,
                    $t['puntos_local'] ?? 10,
                    $t['puntos_visitante'] ?? 9
                ]);
            }
        } catch (Exception $e) {
            error_log("[TarjetaPuntuacion] guardarTarjetas(): " . $e->getMessage());
            throw $e;
        }
    }
    
    public function obtenerPorCombate(int $idCombate): array {
        try {
            $sql = "SELECT tp.*, 
                    CONCAT(j.pnombre, ' ', j.papellido) AS nombre_juez
                    FROM tarjetas_puntuacion tp
                    LEFT JOIN jueces j ON tp.id_juez = j.id_juez
                    WHERE tp.id_combate = ?
                    ORDER BY tp.id_juez, tp.numero_round";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idCombate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("[TarjetaPuntuacion] obtenerPorCombate(): " . $e->getMessage());
            return [];
        }
    }
    
    public function resumenPorJuez(int $idCombate): array {
        try {
            $sql = "SELECT 
                        tp.id_juez,
                        CONCAT(j.pnombre, ' ', j.papellido) AS nombre_juez,
                        COUNT(tp.numero_round) AS rounds,
                        SUM(tp.puntos_local) AS total_local,
                        SUM(tp.puntos_visitante) AS total_visitante
                    FROM tarjetas_puntuacion tp
                    LEFT JOIN jueces j ON tp.id_juez = j.id_juez
                    WHERE tp.id_combate = ?
                    GROUP BY tp.id_juez";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idCombate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("[TarjetaPuntuacion] resumenPorJuez(): " . $e->getMessage());
            return [];
        }
    }
}