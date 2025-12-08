<?php

declare(strict_types=1);

namespace App\Controllers;

// --- Dependencias que utilizaremos ---
use App\Models\Database;                // Conexión PDO singleton
use App\Services\NotificationService;   // Registrar cambios
use App\Services\SmsService;            // Enviar SMS (Twilio)
use App\Services\EmailService;          // Enviar e-mail (Mailgun)
use PDO;                                // Tipado estricto PDO
use Smalot\PdfParser\Parser;            // OCR rápido de PDF

class PersonaController
{
  
    /**
     * Muestra el formulario vacío para crear una persona.
     */
    public function add(): void
    {
        // Array base (rellenado con strings vacíos) que la vista usa
        $data = [
            'id'          => 0,
            'nombre'      => '',
            'apellido'    => '',
            'tipoVisa'    => '',
            'pais'        => '',
            'correo'      => '',
            'telefono'    => '',
            'edad'        => '',
            'numeroVisa'  => '',
            'referenciaTransaccion' => '',
            'fechaInicio' => '',
            'fechaFinal'  => '',
        ];

        // Carga la vista /views/dashboard/add.php
        require __DIR__ . '/../../views/dashboard/add.php';
    }

    /**
     * Muestra el mismo formulario pero cargado con la fila
     * existente correspondiente a $id (modo edición).
     */
    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // Conexión a base de datos
        $db  = Database::connect();

        // Consulta parametrizada — evita inyección
        $stm = $db->prepare(
            "SELECT id, nombre, apellido, tipoVisa, pais,
                    correo, telefono, edad, referenciaTransaccion, numeroVisa,
                    fechaInicio, fechaFinal
             FROM personas
             WHERE id = :id"
        );
        $stm->execute([':id' => $id]);

        // Si no existe la persona devolvemos array vacío para que la vista
        // muestre campos vacíos (podrías redirigir a 404 si lo prefieres)
        $data = $stm->fetch(PDO::FETCH_ASSOC) ?: [];

        require __DIR__ . '/../../views/dashboard/add.php';
    }

    /*==============================================================
    | 2.  AJAX – PREVISUALIZAR PDF (OCR con PdfParser)
    ==============================================================*/

    /**
     * Extrae metadata del PDF antes de guardar.
     * Responde JSON para que el front rellene los campos.
     */
   public function previewVisaPdf(): void
{
    header('Content-Type: application/json');

    // Estructura de salida por defecto
    $out = [
        'numeroVisa'             => '',
        'fechaInicio'            => '',
        'fechaFinal'             => '',
        'visa'                   => '',
        'passportCountry'        => '',
        'referenciaTransaccion'  => ''  // ✅ nuevo campo
    ];

    if (empty($_FILES['visaPdf']['tmp_name'])) {
        echo json_encode($out);
        return;
    }

    try {
        // Extraemos texto plano del PDF
        $txt = (new Parser())->parseFile($_FILES['visaPdf']['tmp_name'])->getText();


        // Patrones de extracción
        $patGrantNum     = '/Visa grant number\s+([0-9A-Z]+)/i';
        $patGrantDate    = '/Date of grant\s+([0-9]{1,2}\s+[A-Za-z]+\s+[0-9]{4})/i';
        $patFinalDate    = '/must not arrive after\s+([0-9]{1,2}\s+[A-Za-z]+\s+[0-9]{4})/i';
        $patVisaApp      = '/Application status.*?\R\s*([^\n:]+?):/is';
        $patVisaOld      = '/^Visa\s+(.+)$/mi';
        $patReferencia   = '/Transaction reference number\s+([A-Z0-9]+)/i'; // ✅ ajuste perfecto

        // Extracción de datos
        if (preg_match($patGrantNum, $txt, $m))     $out['numeroVisa'] = $m[1];
        if (preg_match($patGrantDate, $txt, $m))    $out['fechaInicio'] = self::isoDate($m[1]);
        if (preg_match($patFinalDate, $txt, $m))    $out['fechaFinal']  = self::isoDate($m[1]);

        if (preg_match($patVisaApp, $txt, $m)) {
            $out['visa'] = trim($m[1]);
        } elseif (preg_match($patVisaOld, $txt, $m)) {
            $out['visa'] = trim($m[1]);
        }

        if (preg_match($patReferencia, $txt, $m)) {
            $out['referenciaTransaccion'] = trim($m[1]); // ✅ referencia exacta
        }

        $out['passportCountry'] = self::extractPassportCountry($txt);

    } catch (\Throwable $e) {
        error_log('previewVisaPdf(): ' . $e->getMessage());
    }

    echo json_encode($out);
}


    /**
     * Procesa POST del formulario (create o update).
     * Devuelve JSON con la fila recién almacenada.
     */
    public function save(): void
{
    // Conexión + servicio de notificaciones
    $db       = Database::connect();
    $notifSvc = new NotificationService();

    // 1. Recolección de POST
    $id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre      = $_POST['nombre']      ?? '';
    $apellido    = $_POST['apellido']    ?? '';
    $tipoVisa    = $_POST['tipoVisa']    ?? '';
    $pais        = $_POST['pais']        ?? '';
    $correo      = $_POST['correo']      ?? '';
    $telefono    = $_POST['telefono']    ?? '';
    $edad        = isset($_POST['edad']) ? (int)$_POST['edad'] : 0;
    $numeroVisa  = $_POST['numeroVisa']  ?? '';
    $fechaInicio = $_POST['fechaInicio'] ?? '';
    $fechaFinal  = $_POST['fechaFinal']  ?? '';
    $referenciaTransaccion = $_POST['referenciaTransaccion'] ?? ''; 

    // 2. Si hay PDF subido, lo volvemos a procesar
    if (!empty($_FILES['visaPdf']['tmp_name'])) {
        try {
            $txt = (new Parser())->parseFile($_FILES['visaPdf']['tmp_name'])->getText();
            file_put_contents(__DIR__ . '/debug_pdf_text.txt', $txt);


            // Regex
            $patGrantNum     = '/Visa grant number\s+([0-9A-Z]+)/i';
            $patGrantDate    = '/Date of grant\s+([0-9]{1,2}\s+[A-Za-z]+\s+[0-9]{4})/i';
            $patFinalDate    = '/must not arrive after\s+([0-9]{1,2}\s+[A-Za-z]+\s+[0-9]{4})/i';
            $patVisaApp      = '/Application status.*?\R\s*([^\n:]+?):/is';
            $patVisaOld      = '/^Visa\s+(.+)$/mi';
            $patReferencia = '/Transaction reference number\s+([A-Z0-9]+)/i';


            if (preg_match($patGrantNum, $txt, $m))     $numeroVisa  = $m[1];
            if (preg_match($patGrantDate, $txt, $m))    $fechaInicio = self::isoDate($m[1]);
            if (preg_match($patFinalDate, $txt, $m))    $fechaFinal  = self::isoDate($m[1]);

            if (preg_match($patVisaApp, $txt, $m)) {
                $tipoVisa = trim($m[1]);
            } elseif (preg_match($patVisaOld, $txt, $m)) {
                $tipoVisa = trim($m[1]);
            }

            if (preg_match($patReferencia, $txt, $m)) {
                $referenciaTransaccion = trim($m[1]);
            }

            $pais = self::extractPassportCountry($txt) ?: $pais;

        } catch (\Throwable $e) {
            error_log('save(): parse PDF – ' . $e->getMessage());
        }
    }

    // 3. Validación
    if ($fechaFinal && (new \DateTimeImmutable($fechaFinal)) < new \DateTimeImmutable('today')) {
        header('HTTP/1.1 400 Bad Request');
        header('Content-Type: application/json');
        echo json_encode(['error' => 'La fecha final de la visa ya venció.']);
        return;
    }

    // 4. Persistencia
    if ($id > 0) {
        // UPDATE
        $antes = $db->prepare("SELECT * FROM personas WHERE id = :id");
        $antes->execute([':id' => $id]);
        $antes = $antes->fetch(PDO::FETCH_ASSOC) ?: [];

        $sql = "UPDATE personas SET
                    nombre      = :n,
                    apellido    = :a,
                    tipoVisa    = :tv,
                    pais        = :p,
                    correo      = :c,
                    telefono    = :t,
                    edad        = :e,
                    numeroVisa  = :v,
                    fechaInicio = :fi,
                    fechaFinal  = :ff,
                    referenciaTransaccion = :rt
                WHERE id = :id";

        $db->prepare($sql)->execute([
            ':n'  => $nombre,
            ':a'  => $apellido,
            ':tv' => $tipoVisa,
            ':p'  => $pais,
            ':c'  => $correo,
            ':t'  => $telefono,
            ':e'  => $edad,
            ':v'  => $numeroVisa,
            ':fi' => $fechaInicio,
            ':ff' => $fechaFinal,
            ':rt' => $referenciaTransaccion,
            ':id' => $id
        ]);

        foreach ([
            'nombre','apellido','tipoVisa','pais','correo',
            'telefono','edad','numeroVisa','fechaInicio','fechaFinal','referenciaTransaccion'
        ] as $f) {
            if ((string)($antes[$f] ?? '') !== (string)$$f) {
                $notifSvc->logChange($id, $f, $antes[$f] ?? '', $$f);
            }
        }

    } else {
        // INSERT
        $sql = "INSERT INTO personas
                (nombre, apellido, tipoVisa, pais, correo, telefono,
                 edad, numeroVisa, fechaInicio, fechaFinal, referenciaTransaccion)
                VALUES
                (:n, :a, :tv, :p, :c, :t, :e, :v, :fi, :ff, :rt)";

        $db->prepare($sql)->execute([
            ':n'  => $nombre,
            ':a'  => $apellido,
            ':tv' => $tipoVisa,
            ':p'  => $pais,
            ':c'  => $correo,
            ':t'  => $telefono,
            ':e'  => $edad,
            ':v'  => $numeroVisa,
            ':fi' => $fechaInicio,
            ':ff' => $fechaFinal,
            ':rt' => $referenciaTransaccion
        ]);

        $id = (int)$db->lastInsertId();
    }

    // 5. Devolver fila recién guardada
    $row = $db->prepare(
        "SELECT id, nombre, apellido, tipoVisa, pais, correo,
                telefono, edad, numeroVisa, fechaInicio, fechaFinal, referenciaTransaccion
         FROM personas
         WHERE id = :id"
    );
    $row->execute([':id' => $id]);

    header('Content-Type: application/json');
    echo json_encode($row->fetch(PDO::FETCH_ASSOC));
}


    /*==============================================================
    | 4.  DELETE  (DELETE del CRUD)
    ==============================================================*/
    public function delete(): void
    {
        Database::connect()
            ->prepare("DELETE FROM personas WHERE id = :id")
            ->execute([':id' => (int)($_POST['id'] ?? 0)]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    /*==============================================================
    | 5.  LISTA (READ) — Tabla con días restantes
    ==============================================================*/
    public function listView(): void
    {
        // Lógica está en un Service reutilizable
        $data = (new \App\Services\PersonaService())->getAll();

        $hoy  = new \DateTimeImmutable();
        $red = $yellow = $green = 0;

        foreach ($data as &$p) {
            // Verificar que fechaFinal no sea NULL
            if ($p['fechaFinal'] === null) {
                $p['daysRemaining'] = 0;
                $red++;
                continue;
            }
            
            // Días hasta que expire
            $dias = max(0, (new \DateTimeImmutable($p['fechaFinal']))
                               ->diff($hoy)->days);
            $p['daysRemaining'] = $dias;

            // Clasificación por color
            if ($dias < 30)       $red++;
            elseif ($dias < 60)   $yellow++;
            elseif ($dias < 90)   $green++;
        }
        unset($p); // rompe la referencia

        require __DIR__ . '/../../views/dashboard/lista_usuarios.php';
    }

    /*==============================================================
    | 6.  HELPERS PRIVADOS
    ==============================================================*/

    /** Convierte (ISO-8601). */
    private static function isoDate(string $eng): string
    {
        $d = \DateTime::createFromFormat('j F Y', $eng, new \DateTimeZone('UTC'));
        return $d ? $d->format('Y-m-d') : '';
    }

    /**
     * Busca el bloque “Passport (or other travel document) country …”
     * y devuelve la primera línea que contenga SOLAMENTE letras/espacios
     * en mayúsculas..
     */
    private static function extractPassportCountry(string $txt): string
    {
        if (!preg_match('/Passport \(or other travel\s+document\)\s+country(.{0,300})/is', $txt, $m)) {
            return '';
        }
        foreach (preg_split('/\R+/', $m[1]) as $line) {
            $line = trim($line);
            if ($line && preg_match('/^[A-Z ]{2,}$/', $line)) {
                return ucwords(strtolower($line)); // Titular → “Peru”, “United States”
            }
        }
        return '';
    }

    /*==============================================================
    | 7.  MÉTODOS PARA ENVÍO DE RECORDATORIOS (externos al CRUD)
    ==============================================================*/

    /** Enviar SMS usando Twilio. */
    public function sendSms(int $id, string $tel, int $dias, string $name): void
    {
        (new SmsService())->send(
            $tel,
            "Hola {$name}, te recordamos que te quedan {$dias} días para renovar tu visa."
        );
    }

    /** Enviar e-mail usando Mailgun. */
    public function sendEmail(int $id, string $mail, int $dias, string $name): void
    {
        (new EmailService())->send(
            $mail,
            "Recordatorio Visa – {$dias} días restantes",
            "<p>Hola {$name}, te quedan <strong>{$dias} días</strong> para renovar tu visa.</p>"
        );
    }
}
