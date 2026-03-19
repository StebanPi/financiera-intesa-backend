-- =============================================================
-- Script: Cuentas DEBE y HABER para sede AGUACHICA
-- Copia las cuentas de BARRANCABERMEJA a AGUACHICA
-- Solo inserta las que aún no existen (idempotente)
-- =============================================================

SET SQL_SAFE_UPDATES = 0;

-- -------------------------------------------------------------
-- DEBE
-- -------------------------------------------------------------
INSERT INTO debes (cuenta, nombre, sede, created_at, updated_at)
SELECT
    d.cuenta,
    d.nombre,
    'AGUACHICA',
    NOW(),
    NOW()
FROM debes d
WHERE UPPER(d.sede) = 'BARRANCABERMEJA'
  AND NOT EXISTS (
      SELECT 1 FROM debes x
      WHERE x.cuenta = d.cuenta
        AND UPPER(x.sede) = 'AGUACHICA'
  );

-- -------------------------------------------------------------
-- HABER
-- -------------------------------------------------------------
INSERT INTO habers (cuenta, nombre, sede, created_at, updated_at)
SELECT
    h.cuenta,
    h.nombre,
    'AGUACHICA',
    NOW(),
    NOW()
FROM habers h
WHERE UPPER(h.sede) = 'BARRANCABERMEJA'
  AND NOT EXISTS (
      SELECT 1 FROM habers x
      WHERE x.cuenta = h.cuenta
        AND UPPER(x.sede) = 'AGUACHICA'
  );

SET SQL_SAFE_UPDATES = 1;

-- Verificar resultado
SELECT 'DEBES AGUACHICA' AS tabla, COUNT(*) AS total FROM debes WHERE UPPER(sede) = 'AGUACHICA'
UNION ALL
SELECT 'HABERS AGUACHICA', COUNT(*) FROM habers WHERE UPPER(sede) = 'AGUACHICA';
