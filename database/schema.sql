-- Schema para PostgreSQL - VissApp v3
-- Migrado desde MySQL

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(25) NOT NULL UNIQUE,
    correo VARCHAR(150) NOT NULL,
    password VARCHAR(50) NOT NULL,
    rol VARCHAR(50) NOT NULL DEFAULT 'usuario'
);

-- Tabla de personas
CREATE TABLE IF NOT EXISTS personas (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    pais VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NULL,
    telefono VARCHAR(50) NULL,
    edad INTEGER NULL,
    numeroVisa VARCHAR(50) NULL,
    tipoVisa VARCHAR(100) NULL,
    fechaInicio DATE NULL,
    fechaFinal DATE NULL,
    referenciaTransaccion VARCHAR(100) NULL
);

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    persona_id INTEGER NULL,
    field VARCHAR(50) NOT NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE
);

-- Índices para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_personas_fechaFinal ON personas(fechaFinal);
CREATE INDEX IF NOT EXISTS idx_personas_pais ON personas(pais);
CREATE INDEX IF NOT EXISTS idx_personas_tipoVisa ON personas(tipoVisa);
CREATE INDEX IF NOT EXISTS idx_notifications_persona_id ON notifications(persona_id);
CREATE INDEX IF NOT EXISTS idx_notifications_changed_at ON notifications(changed_at);

-- Datos de ejemplo (usuario admin)
INSERT INTO usuarios (usuario, correo, password, rol) 
VALUES ('admin', 'admin@vissapp.com', MD5('admin123'), 'admin')
ON CONFLICT (usuario) DO NOTHING;

INSERT INTO usuarios (usuario, correo, password, rol) 
VALUES ('soporte', 'soporte@vissapp.com', MD5('soporte123'), 'soporte')
ON CONFLICT (usuario) DO NOTHING;

-- Comentarios en tablas
COMMENT ON TABLE usuarios IS 'Usuarios del sistema con roles';
COMMENT ON TABLE personas IS 'Personas con información de visas';
COMMENT ON TABLE notifications IS 'Historial de cambios en personas';
