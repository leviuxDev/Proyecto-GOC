<?php
/**
 * Controlador de Combates
 * 
 */
class CombatesController extends Controller {
    
    /**
     * Lista todos los combates registrados.
     */
    public function index(): void {
        try {
            $this->requireLogin();
            $combates = (new Combate())->todos();
            $this->view('combates/index', compact('combates'));
        } catch (Exception $e) {
            error_log("[CombatesController::index] " . $e->getMessage());
            $_SESSION['error'] = "Error al cargar la lista de combates";
            $this->redirect('index.php?c=dashboard&a=index');
        }
    }
    
    /**
     * Muestra la ficha detallada de un combate (Fight Card).
     */
    public function ver(): void {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['error'] = "ID de combate inválido";
            $this->redirect('index.php?c=combates&a=index');
            return;
        }
        
        $modelo = new Combate();
        $combate = $modelo->obtener($id);
        
        if (!$combate) {
            $_SESSION['error'] = "Combate no encontrado";
            $this->redirect('index.php?c=combates&a=index');
            return;
        }
        
        // Carga de datos relacionados para la ficha
        $tarjetas = (new TarjetaPuntuacion())->obtenerPorCombate($id);
        $resumenJueces = (new TarjetaPuntuacion())->resumenPorJuez($id);
        
        $this->view('combates/ver', compact('combate', 'tarjetas', 'resumenJueces'));
    }
    
    /**
     * Prepara y muestra el formulario de creación o edición.
     */
    public function form(): void {
        try {
            $this->requireRol(['Administrador', 'Entrenador']);
            
            $modelo = new Combate();
            $id = (int)($_GET['id'] ?? 0);
            $combate = null;
            $tarjetas = [];
            $errores = [];
            
            // Captura de parámetros opcionales (para prellenar desde otras vistas)
            $localId = (int)($_GET['local'] ?? 0);
            $visitanteId = (int)($_GET['visitante'] ?? 0);
            $idTorneo = (int)($_GET['id_torneo'] ?? 0);
            
            if ($id > 0) {
                $combate = $modelo->obtener($id);
                if (!$combate) throw new Exception("Combate no encontrado");
                $tarjetas = (new TarjetaPuntuacion())->obtenerPorCombate($id);
            } else {
                // Valores por defecto para un combate nuevo
                $combate = [
                    'id_combate' => 0,
                    'id_atleta_local' => $localId > 0 ? $localId : null,
                    'id_atleta_visitante' => $visitanteId > 0 ? $visitanteId : null,
                    'id_torneo' => $idTorneo > 0 ? $idTorneo : null,
                    'fecha' => date('Y-m-d'),
                    'ronda' => 1,
                    'rounds_pautados' => 3,
                    'round_finalizacion' => null,
                    'decision_victoria' => null,
                    'resultado' => 'Pendiente',
                    'posicion_llave' => null
                ];
            }
            
            // Datos para poblar los selectores
            $torneos = $modelo->torneos();
            $atletas = (new Atleta())->todos(['activo' => 1]);
            $jueces = (new Juez())->todos();
            
            $this->view('combates/form', compact('combate', 'torneos', 'atletas', 'jueces', 'tarjetas', 'errores'));
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('index.php?c=combates&a=index');
        }
    }
    
    /**
     * Persiste un combate nuevo o actualiza uno existente.
     * Devuelve la respuesta en JSON para el manejo asíncrono del cliente.
     */
    public function guardar(): void {
        $this->requireRol(['Administrador', 'Entrenador']);
        
        $id = (int)($_POST['id'] ?? 0);
        $errores = [];
        
        try {
            // ============ CAPTURA Y SANITIZACIÓN DE DATOS ============
            $idLocal = (int)($_POST['id_atleta_local'] ?? 0);
            $idVisitante = (int)($_POST['id_atleta_visitante'] ?? 0);
            $fecha = trim($_POST['fecha'] ?? '');
            $ronda = (int)($_POST['ronda'] ?? 0);
            $roundsPautados = (int)($_POST['rounds_pautados'] ?? 3);
            $resultado = $_POST['resultado'] ?? 'Pendiente';
            $decision = $_POST['decision_victoria'] ?? null;
            $roundFinal = !empty($_POST['round_finalizacion']) ? (int)$_POST['round_finalizacion'] : null;
            $posicion = !empty($_POST['posicion_llave']) ? (int)$_POST['posicion_llave'] : null;
            
            // ============ VALIDACIONES DE REGLAS DE NEGOCIO ============
            if ($idLocal <= 0) $errores['id_atleta_local'] = 'Debes seleccionar el atleta local';
            if ($idVisitante <= 0) $errores['id_atleta_visitante'] = 'Debes seleccionar el atleta visitante';
            
            if ($idLocal > 0 && $idVisitante > 0 && $idLocal === $idVisitante) {
                $errores['id_atleta_visitante'] = 'El atleta local y visitante no pueden ser el mismo';
            }
            
            if ($fecha === '') {
                $errores['fecha'] = 'La fecha del combate es obligatoria';
            } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                $errores['fecha'] = 'Formato de fecha inválido';
            }
            
            if ($ronda <= 0) {
                $errores['ronda'] = 'La ronda debe ser un número positivo';
            }
            
            if (!in_array($roundsPautados, [3, 6, 8, 10, 12])) {
                $errores['rounds_pautados'] = 'Número de asaltos inválido';
            }
            
            if ($roundFinal !== null) {
                if ($roundFinal <= 0) {
                    $errores['round_finalizacion'] = 'El asalto debe ser mayor a 0';
                } elseif ($roundFinal > $roundsPautados) {
                    $errores['round_finalizacion'] = "El asalto no puede superar los {$roundsPautados} pautados";
                }
            }
            
            if (!in_array($resultado, ['Pendiente', 'Local', 'Visitante', 'Empate'])) {
                $errores['resultado'] = 'Resultado inválido';
            }
            
            if (!empty($decision) && !in_array($decision, ['KO','TKO','UD','SD','MD','RTD','DQ','NC','UED','SED','MED','TD','TED'])) {
                $errores['decision_victoria'] = 'Decisión inválida';
            }
            
            if ($resultado === 'Pendiente' && !empty($decision)) {
                $errores['decision_victoria'] = 'No puedes registrar decisión si el resultado está Pendiente';
            }
            
            if ($posicion !== null && $posicion < 0) {
                $errores['posicion_llave'] = 'La posición no puede ser negativa';
            }
            
            // ============ VALIDACIÓN DE TARJETAS DE PUNTUACIÓN ============
            $tarjetas = $_POST['tarjetas'] ?? [];
            $tarjetasLimpias = [];
            
            if (!empty($tarjetas) && is_array($tarjetas)) {
                foreach ($tarjetas as $t) {
                    // Ignorar filas vacías
                    if (empty($t['id_juez']) && empty($t['puntos_local']) && empty($t['puntos_visitante'])) {
                        continue;
                    }
                    
                    if (empty($t['id_juez'])) {
                        $errores['tarjetas'] = 'Cada tarjeta debe tener un juez asignado';
                        break;
                    }
                    if (empty($t['numero_round']) || (int)$t['numero_round'] <= 0) {
                        $errores['tarjetas'] = 'El número de round debe ser positivo';
                        break;
                    }
                    
                    $pl = (int)($t['puntos_local'] ?? 0);
                    $pv = (int)($t['puntos_visitante'] ?? 0);
                    
                    if ($pl < 6 || $pl > 10 || $pv < 6 || $pv > 10) {
                        $errores['tarjetas'] = 'Los puntos deben estar entre 6 y 10';
                        break;
                    }
                    
                    $tarjetasLimpias[] = [
                        'id_juez' => (int)$t['id_juez'],
                        'numero_round' => (int)$t['numero_round'],
                        'puntos_local' => $pl,
                        'puntos_visitante' => $pv
                    ];
                }
            }
            
            // ============ RESPUESTA TEMPRANA EN CASO DE ERRORES ============
            if (!empty($errores)) {
                $this->json([
                    'status' => 'error',
                    'mensaje' => 'Por favor, corrige los errores del formulario',
                    'errores' => $errores
                ], 422);
            }
            
            // ============ DELEGACIÓN DE LA PERSISTENCIA AL MODELO ============
            $datos = [
                'id_torneo' => !empty($_POST['id_torneo']) ? (int)$_POST['id_torneo'] : null,
                'id_atleta_local' => $idLocal,
                'id_atleta_visitante' => $idVisitante,
                'fecha' => $fecha,
                'ronda' => $ronda,
                'rounds_pautados' => $roundsPautados,
                'round_finalizacion' => $roundFinal,
                'decision_victoria' => $decision ?: null,
                'resultado' => $resultado,
                'posicion_llave' => $posicion,
            ];
            
            $modelo = new Combate();
            if ($id > 0) {
                $modelo->actualizar($id, $datos);
                $mensaje = "Combate actualizado correctamente";
            } else {
                $id = $modelo->crear($datos);
                $mensaje = "Combate creado correctamente";
            }
            
            // Guardar tarjetas si las hay
            if (!empty($tarjetasLimpias)) {
                (new TarjetaPuntuacion())->guardarTarjetas($id, $tarjetasLimpias);
            }
            
            // ============ RESPUESTA EXITOSA ============
            $this->json([
                'status' => 'success',
                'mensaje' => $mensaje,
                'url_redirect' => BASE_URL . 'index.php?c=combates&a=index'
            ]);
            
        } catch (InvalidArgumentException $e) {
            $this->json(['status' => 'error', 'mensaje' => $e->getMessage()], 422);
        } catch (Exception $e) {
            error_log("[CombatesController::guardar] " . $e->getMessage());
            $this->json(['status' => 'error', 'mensaje' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Elimina un combate del sistema.
     */
    public function eliminar(): void {
        $this->requireRol(['Administrador']);
        
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) throw new InvalidArgumentException("ID de combate inválido");
            
            $modelo = new Combate();
            if (!$modelo->obtener($id)) throw new Exception("Combate no encontrado");
            
            if ($modelo->eliminar($id)) {
                $_SESSION['ok'] = "Combate eliminado correctamente";
            } else {
                $_SESSION['error'] = "No se pudo eliminar el combate";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        $this->redirect('index.php?c=combates&a=index');
    }
    
    /**
     * Genera sugerencias de emparejamiento inteligente.
     */
    public function sugerir(): void {
        try {
            $this->requireRol(['Administrador', 'Entrenador']);
            
            $categorias = (new Categoria())->todos();
            $sugerencias = [];
            $filtros = ['categoria' => '', 'min' => 0, 'max' => 100];
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $filtros = [
                    'categoria' => (int)($_POST['categoria'] ?? 0),
                    'min' => (int)($_POST['min'] ?? 0),
                    'max' => (int)($_POST['max'] ?? 100),
                ];
                
                if ($filtros['categoria'] <= 0) {
                    throw new InvalidArgumentException("Selecciona una categoría");
                }
                
                $sugerencias = (new Combate())->sugerir(
                    $filtros['categoria'],
                    $filtros['min'],
                    $filtros['max']
                );
            }
            
            $this->view('combates/sugerir', compact('categorias', 'sugerencias', 'filtros'));
            
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('index.php?c=combates&a=index');
        } catch (Exception $e) {
            error_log("[CombatesController::sugerir] " . $e->getMessage());
            $_SESSION['error'] = "Error al generar emparejamientos";
            $this->redirect('index.php?c=combates&a=index');
        }
    }
}