<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio centralizado para el cálculo de cartera
 * Garantiza precisión en la distribución de abonos y cálculo de estados
 */
class CarteraService
{
    /**
     * Calcula la información completa de cartera para un estudiante o un id_cost específico
     * 
     * @param int|string|null $id_cost ID del registro de costo
     * @param string|null $cod_alumno Código del alumno (si se quiere calcular para todos sus semestres)
     * @return array
     */
    public static function calcularCartera($id_cost = null, $cod_alumno = null)
    {
        $ids_cost = [];

        if ($cod_alumno) {
            $ids_cost = DB::table('costs')->where('cod_alumno', $cod_alumno)->pluck('id')->toArray();
        } elseif ($id_cost) {
            $ids_cost = [$id_cost];
        }

        if (empty($ids_cost)) {
            return [
                'cuotas' => [],
                'totales' => [
                    'total_abono' => 0,
                    'cuotas_total' => 0,
                    'total_abonado' => 0,
                    'saldo_pendiente' => 0,
                    'saldo_a_favor' => 0,
                    'saldo_en_mora' => 0,
                ],
                'hoy' => date('Y-m-d'),
            ];
        }

        // Obtener todas las cuotas ordenadas por fecha
        $purses = DB::connection('mysql')
            ->table('purses')
            ->whereIn('id_cost', $ids_cost)
            ->orderBy('fecha_pago', 'asc')
            ->get();

        // Calcular total de abonos (solo entries, other_entries son otros ingresos separados)
        $totalEntries = DB::connection('mysql')
            ->table('entries')
            ->whereIn('id_cost', $ids_cost)
            ->sum('valor') ?? 0;

        $totalAbono = floatval($totalEntries);

        // Variables de acumulación
        $cuotasTotal = 0;
        $totalAbonado = 0;
        $saldoPendiente = 0;
        $saldoAFavor = 0;
        $saldoEnMora = 0; // Nuevo: saldo pendiente de cuotas vencidas
        $abonoRestante = $totalAbono;
        $hoy = date('Y-m-d');

        // Array para almacenar los resultados de cada cuota
        $cuotasCalculadas = [];

        foreach ($purses as $index => $purse) {
            $cuota = floatval($purse->cuota);
            $cuotasTotal += $cuota;

            // Calcular abonado para esta cuota (distribución cronológica)
            $abonado = 0;
            $abonoRestanteAntes = $abonoRestante;
            if ($abonoRestante >= $cuota) {
                // Abono completo
                $abonado = $cuota;
                $abonoRestante -= $cuota;
            } elseif ($abonoRestante > 0) {
                // Abono parcial
                $abonado = $abonoRestante;
                $abonoRestante = 0;
            }

            // Verificar si está vencida
            $fechaPago = date('Y-m-d', strtotime($purse->fecha_pago));
            $isVencida = (strtotime($hoy) > strtotime($fechaPago));

            // Determinar estado del pago (Completa, Incompleta, Pendiente)
            $estadoPago = self::calcularEstadoPago($abonado, $cuota);

            // Determinar estado general (Al dia, En Mora, Incompleta, Proxima)
            $estado = self::calcularEstado($abonado, $cuota, $isVencida);

            // Calcular saldos (usando la misma lógica que el PDF)
            $totalAbonado += $abonado;
            
            // Determinar valueShow (igual que en el PDF)
            $valueShow = 0;
            if ($abonado >= $cuota) {
                $valueShow = $cuota; // Solo se muestra la cuota, no el exceso
            } elseif ($isVencida) {
                // Si está vencida y hay abono parcial
                if ($abonado > 0) {
                    $valueShow = $abonado;
                } else {
                    $valueShow = 0;
                }
            } else {
                // No está vencida
                if ($abonado > 0) {
                    $valueShow = $abonado;
                } else {
                    $valueShow = 0;
                }
            }
            
            // Calcular saldo pendiente y saldo en mora
            if ($abonado < $cuota) {
                $diferencia = $cuota - $abonado;
                
                if ($isVencida) {
                    // Cuota vencida incompleta = saldo pendiente y saldo en mora
                    if ($abonado > 0) {
                        $saldoPendiente += $diferencia;
                        $saldoEnMora += $diferencia; // Saldo en mora = diferencia de cuotas vencidas incompletas
                    } else {
                        $saldoPendiente += $cuota;
                        $saldoEnMora += $cuota; // Saldo en mora = cuota completa si no hay abono
                    }
                } else {
                    // Cuota no vencida con abono parcial
                    if ($abonado > 0) {
                        // La diferencia es saldo pendiente (pero no saldo en mora porque no está vencida)
                        $saldoPendiente += $diferencia;
                    }
                    // Si no hay abono y no está vencida, no se suma al saldo pendiente ni al saldo en mora
                }
            }
            
            // Calcular Saldo a Favor (igual que en el PDF: solo cuando valueShow > 0 y no está vencida)
            $saldoAFavorAntes = $saldoAFavor;
            if ($valueShow > 0 && $isVencida == false) {
                $saldoAFavor += $valueShow;
            }
            
            // Log detallado por cuota
            Log::info("CarteraService - Cuota #{$index}", [
                'id' => $purse->id,
                'fecha_pago' => $fechaPago,
                'hoy' => $hoy,
                'is_vencida' => $isVencida,
                'cuota' => $cuota,
                'abono_restante_antes' => $abonoRestanteAntes,
                'abonado' => $abonado,
                'abono_restante_despues' => $abonoRestante,
                'valueShow' => $valueShow,
                'estado' => $estado,
                'estado_pago' => $estadoPago,
                'saldo_pendiente_antes' => $saldoPendiente,
                'saldo_a_favor_antes' => $saldoAFavorAntes,
                'saldo_a_favor_despues' => $saldoAFavor
            ]);

            // Guardar información de la cuota
            $cuotasCalculadas[] = [
                'id' => $purse->id,
                'id_cost' => $purse->id_cost,
                'fecha_pago' => $purse->fecha_pago,
                'fecha_pago_formateada' => $purse->fecha_pago, // Se formateará en la vista
                'cuota' => $cuota,
                'abonado' => $abonado,
                'estado_pago' => $estadoPago,
                'estado' => $estado,
                'is_vencida' => $isVencida,
                'comentario' => $purse->comentario ?? '',
            ];
        }

        // El saldo a favor ya fue calculado correctamente en el loop anterior

        // Log detallado para depuración
        Log::info('CarteraService - DATOS DE LA BASE DE DATOS', [
            'ids_cost' => $ids_cost,
            'cod_alumno' => $cod_alumno,
            'total_entries' => $totalEntries,
            'total_abono' => $totalAbono,
            'nota' => 'other_entries no se incluyen en el cálculo de cartera (son otros ingresos separados)',
            'num_purses' => count($purses),
            'purses_raw' => $purses->map(function($p) {
                return [
                    'id' => $p->id,
                    'fecha_pago' => $p->fecha_pago,
                    'cuota' => $p->cuota,
                    'abonado' => $p->abonado ?? 0,
                    'comentario' => $p->comentario ?? ''
                ];
            })->toArray()
        ]);
        
        Log::info('CarteraService - CÁLCULOS POR CUOTA', [
            'cuotas_calculadas' => $cuotasCalculadas
        ]);
        
        Log::info('CarteraService - TOTALES CALCULADOS', [
            'ids_cost' => $ids_cost,
            'cod_alumno' => $cod_alumno,
            'total_abono' => $totalAbono,
            'cuotas_total' => $cuotasTotal,
            'total_abonado' => $totalAbonado,
            'saldo_pendiente' => $saldoPendiente,
            'saldo_a_favor' => $saldoAFavor,
            'saldo_en_mora' => $saldoEnMora,
            'num_cuotas' => count($cuotasCalculadas)
        ]);
        
        return [
            'cuotas' => $cuotasCalculadas,
            'totales' => [
                'total_abono' => $totalAbono,
                'cuotas_total' => $cuotasTotal,
                'total_abonado' => $totalAbonado,
                'saldo_pendiente' => $saldoPendiente,
                'saldo_a_favor' => $saldoAFavor,
                'saldo_en_mora' => $saldoEnMora,
            ],
            'hoy' => $hoy,
        ];
    }

    /**
     * Calcula el estado del pago basado en abonado vs cuota
     * 
     * @param float $abonado
     * @param float $cuota
     * @return string
     */
    private static function calcularEstadoPago($abonado, $cuota)
    {
        if ($abonado >= $cuota) {
            return 'Completa';
        } elseif ($abonado > 0) {
            return 'Incompleta';
        } else {
            return 'Pendiente';
        }
    }

    /**
     * Calcula el estado general basado en abonado, cuota y si está vencida
     * 
     * @param float $abonado
     * @param float $cuota
     * @param bool $isVencida
     * @return string
     */
    private static function calcularEstado($abonado, $cuota, $isVencida)
    {
        if ($abonado >= $cuota) {
            return 'Al dia';
        } elseif ($isVencida) {
            // Si ya pasó el día de pago y no está completo, está en mora
            return 'En Mora';
        } else {
            // No está vencida
            if ($abonado > 0) {
                return 'Incompleta';
            } else {
                return 'Proxima';
            }
        }
    }

    /**
     * Obtiene el total de abonos para un id_cost
     * 
     * @param int $id_cost
     * @return float
     */
    public static function obtenerTotalAbono($id_cost)
    {
        $totalEntries = DB::connection('mysql')
            ->table('entries')
            ->where('id_cost', $id_cost)
            ->sum('valor') ?? 0;

        $totalOtherEntries = DB::connection('mysql')
            ->table('other_entries')
            ->where('id_cost', $id_cost)
            ->sum('valor') ?? 0;

        return floatval($totalEntries) + floatval($totalOtherEntries);
    }
}

