<?php
// backend/utils/mailer.php
// Requiere PHPMailer: composer require phpmailer/phpmailer
// Si no tienes Composer, descarga PHPMailer manualmente
require_once __DIR__ . '/../config/env.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// Intentar cargar PHPMailer (Composer o manual)
$autoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

class Mailer {

    /**
     * Envía el código MFA al correo del usuario.
     */
    public static function sendMFACode(string $toEmail, string $toName, string $code): bool {
        $subject = '🔐 Tu código de verificación — ' . APP_NAME;
        $body    = self::templateMFA($toName, $code);
        return self::send($toEmail, $toName, $subject, $body);
    }

    /**
     * Envía link de recuperación de contraseña.
     */
    public static function sendPasswordReset(string $toEmail, string $toName, string $resetToken): bool {
        $link    = APP_URL . '/reset-password?token=' . urlencode($resetToken);
        $subject = '🔑 Recuperar contraseña — ' . APP_NAME;
        $body    = self::templateReset($toName, $link);
        return self::send($toEmail, $toName, $subject, $body);
    }

    /**
     * Envía notificación de bienvenida tras registro.
     */
    public static function sendWelcome(string $toEmail, string $toName): bool {
        $subject = '🚗 Bienvenido/a a ' . APP_NAME;
        $body    = self::templateWelcome($toName);
        return self::send($toEmail, $toName, $subject, $body);
    }

    // ─────────────────────────────────────────────────────
    // Método de envío base
    // ─────────────────────────────────────────────────────
    private static function send(string $to, string $name, string $subject, string $body): bool {
        // Si PHPMailer no está disponible, usar mail() nativo como fallback
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $headers  = "From: " . SMTP_NAME . " <" . SMTP_FROM . ">\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            return mail($to, $subject, $body, $headers);
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(SMTP_FROM, SMTP_NAME);
            $mail->addAddress($to, $name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>'], "\n", $body));

            $mail->send();
            return true;
        } catch (Throwable $e) {
            error_log('[Mailer] Error: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────
    // Templates de correo
    // ─────────────────────────────────────────────────────
    private static function wrap(string $content): string {
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<style>
  body{margin:0;padding:0;background:#f5f3ef;font-family:'Segoe UI',Arial,sans-serif;}
  .container{max-width:520px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.1);}
  .header{background:linear-gradient(135deg,#1e3a5f 0%,#0d2240 100%);padding:28px 32px;text-align:center;}
  .logo{color:#fff;font-size:1.8rem;font-weight:900;letter-spacing:-.03em;}
  .logo span{color:#c8982a;}
  .body{padding:32px;}
  .code{font-size:2.6rem;font-weight:900;letter-spacing:.35em;color:#1e3a5f;background:#f5f3ef;border:2px dashed #c8982a;border-radius:10px;padding:16px 24px;text-align:center;margin:20px 0;}
  .btn{display:inline-block;background:#c8982a;color:#fff;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:700;font-size:1rem;margin:16px 0;}
  .footer{background:#f5f3ef;padding:20px 32px;font-size:.75rem;color:#888;text-align:center;border-top:1px solid #e2ddd6;}
  p{color:#3a3530;line-height:1.7;margin:0 0 12px;}
  h2{color:#1e3a5f;margin:0 0 16px;}
</style></head>
<body><div class="container">
  <div class="header"><div class="logo">Rent<span>Car</span></div></div>
  <div class="body">{$content}</div>
  <div class="footer">Este correo fue enviado automáticamente. No respondas a este mensaje.<br/>© 2025 RentCar · 314 Bertha B. de La Peña, Piedras Negras, Coahuila</div>
</div></body></html>
HTML;
    }

    private static function templateMFA(string $name, string $code): string {
        $content = <<<HTML
<h2>🔐 Verificación de identidad</h2>
<p>Hola <strong>{$name}</strong>,</p>
<p>Alguien intentó iniciar sesión en tu cuenta de RentCar. Usa el siguiente código para completar el acceso:</p>
<div class="code">{$code}</div>
<p>⏰ Este código expira en <strong>10 minutos</strong>.</p>
<p>Si no fuiste tú, ignora este correo y considera cambiar tu contraseña.</p>
HTML;
        return self::wrap($content);
    }

    private static function templateReset(string $name, string $link): string {
        $content = <<<HTML
<h2>🔑 Recuperar tu contraseña</h2>
<p>Hola <strong>{$name}</strong>,</p>
<p>Recibimos una solicitud para restablecer la contraseña de tu cuenta RentCar.</p>
<p style="text-align:center;"><a href="{$link}" class="btn">Restablecer contraseña →</a></p>
<p>⏰ Este enlace expira en <strong>30 minutos</strong>.</p>
<p>Si no solicitaste esto, ignora este correo. Tu contraseña no cambiará.</p>
<p style="font-size:.82rem;color:#888;">Si el botón no funciona, copia y pega esta URL: <br/>{$link}</p>
HTML;
        return self::wrap($content);
    }

    private static function templateWelcome(string $name): string {
        $link    = APP_URL . '/servicios';
        $content = <<<HTML
<h2>🚗 ¡Bienvenido/a, {$name}!</h2>
<p>Tu cuenta en RentCar ha sido creada exitosamente. Ahora puedes explorar nuestra flota premium y reservar el vehículo perfecto para ti.</p>
<p style="text-align:center;"><a href="{$link}" class="btn">Explorar vehículos →</a></p>
<p>Si tienes dudas, contáctanos en <a href="mailto:info@rentcar.mx">info@rentcar.mx</a> o al <strong>+52 (861) 100-0000</strong>.</p>
HTML;
        return self::wrap($content);
    }
}
