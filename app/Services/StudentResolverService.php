<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Matricula;

class StudentResolverService
{
    /**
     * Obtiene los datos del estudiante (cedula, nombre, programa) dado el cod_alumno
     * 
     * @param string|int $codAlumno
     * @return object|null Objeto con cedula, nombre, nombre_programa o null si no se encuentra
     */
    public static function getStudentData($codAlumno)
    {
        if (empty($codAlumno)) {
            return null;
        }

        // Primero intentar desde mysql2 (alumno + relacion_programa_estudiante + programa)
        try {
            $data = DB::connection('mysql2')->select(
                'SELECT alumno.cedula, alumno.nombre, programa.nombre_programa 
                 FROM alumno 
                 INNER JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno 
                 INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod 
                 WHERE alumno.cod_alumno = ?',
                [$codAlumno]
            );

            if (!empty($data) && isset($data[0])) {
                return (object)[
                    'cedula' => $data[0]->cedula ?? '',
                    'nombre' => $data[0]->nombre ?? 'N/A',
                    'nombre_programa' => $data[0]->nombre_programa ?? ''
                ];
            }

            // Si no tiene programa asignado, intentar sin la relación
            $data = DB::connection('mysql2')->select(
                'SELECT alumno.cedula, alumno.nombre, "" AS nombre_programa 
                 FROM alumno 
                 WHERE alumno.cod_alumno = ?',
                [$codAlumno]
            );

            if (!empty($data) && isset($data[0])) {
                return (object)[
                    'cedula' => $data[0]->cedula ?? '',
                    'nombre' => $data[0]->nombre ?? 'N/A',
                    'nombre_programa' => ''
                ];
            }
        } catch (\Throwable $e) {
            // Silenciosamente ignorar fallo de conexión externa y seguir al fallback local
            \Log::warning('External DB student lookup failed: ' . $e->getMessage());
        }

        // Fallback: buscar en la tabla local matriculas
        try {
            $matricula = Matricula::where('cod_alumno', $codAlumno)->first();
            if ($matricula) {
                return (object)[
                    'cedula' => $matricula->numero_documento ?? '',
                    'nombre' => $matricula->nombre_completo ?? 'N/A',
                    'nombre_programa' => $matricula->programa ?? ''
                ];
            }
        } catch (\Throwable $e) {
            \Log::error('Local student lookup failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Normaliza la forma de pago: "Consignación" -> "Bancos"
     * 
     * @param string $forma
     * @return string
     */
    public static function normalizePaymentForm($forma)
    {
        return $forma === 'Consignación' ? 'Bancos' : $forma;
    }
}
