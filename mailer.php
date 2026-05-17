<?php
// Si PHPMailer no está instalado, la función simplemente no envía
if (!file_exists(__DIR__ . '/PHPMailer/src/PHPMailer.php')) {
    function sendOrderEmail($to_email, $to_name, $items, $total, $fecha) { return false; }
    return;
}

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOrderEmail($to_email, $to_name, $items, $total, $fecha) {
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

        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = '¡Tu pedido en El Buen Comer fue confirmado!';
        $mail->Body    = buildEmailHtml($to_name, $items, $total, $fecha);
        $mail->AltBody = buildEmailText($to_name, $items, $total, $fecha);

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function buildEmailHtml($nombre, $items, $total, $fecha) {
    $rows = '';
    $alt  = false;
    foreach ($items as $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        $bg       = $alt ? '#f9f9f9' : '#ffffff';
        $rows .= "
            <tr style='background:{$bg};'>
                <td style='padding:12px 16px;color:#2C3E50;font-size:14px;border-bottom:1px solid #eee;'>" . htmlspecialchars($item['nombre']) . "</td>
                <td style='padding:12px 16px;color:#7F8C8D;font-size:14px;text-align:center;border-bottom:1px solid #eee;'>" . $item['cantidad'] . "</td>
                <td style='padding:12px 16px;color:#7F8C8D;font-size:14px;text-align:right;border-bottom:1px solid #eee;'>$" . number_format($item['precio'], 0, ',', '.') . "</td>
                <td style='padding:12px 16px;color:#F39C12;font-weight:700;font-size:14px;text-align:right;border-bottom:1px solid #eee;'>$" . number_format($subtotal, 0, ',', '.') . "</td>
            </tr>";
        $alt = !$alt;
    }

    $totalFmt = number_format($total, 0, ',', '.');
    $nombreEsc = htmlspecialchars($nombre);
    $fechaFmt  = date('d/m/Y', strtotime($fecha));

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#ECF0F1;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#ECF0F1;padding:32px 16px;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.10);max-width:600px;width:100%;">

      <!-- HEADER -->
      <tr>
        <td style="background:linear-gradient(135deg,#1a1a2e 0%,#0f3460 100%);padding:36px 40px;text-align:center;">
          <h1 style="margin:0;font-size:30px;font-weight:800;color:#F39C12;letter-spacing:1px;">El Buen Comer</h1>
          <p style="margin:10px 0 0;font-size:14px;color:rgba(255,255,255,0.75);letter-spacing:0.5px;text-transform:uppercase;">Confirmación de Pedido</p>
        </td>
      </tr>

      <!-- CUERPO -->
      <tr>
        <td style="padding:36px 40px;">
          <p style="margin:0 0 6px;font-size:17px;color:#2C3E50;">Hola, <strong>{$nombreEsc}</strong> 👋</p>
          <p style="margin:0 0 28px;font-size:14px;color:#7F8C8D;line-height:1.6;">
            Tu pedido del <strong>{$fechaFmt}</strong> fue registrado exitosamente.<br>
            A continuación encontrás el detalle de tu compra:
          </p>

          <!-- TABLA ITEMS -->
          <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-radius:8px;overflow:hidden;">
            <thead>
              <tr style="background:#1a1a2e;">
                <th style="padding:12px 16px;color:#fff;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;text-align:left;">Plato</th>
                <th style="padding:12px 16px;color:#fff;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;text-align:center;">Cant.</th>
                <th style="padding:12px 16px;color:#fff;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;text-align:right;">Precio</th>
                <th style="padding:12px 16px;color:#fff;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;text-align:right;">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              {$rows}
            </tbody>
          </table>

          <!-- TOTAL -->
          <table width="100%" cellpadding="0" cellspacing="0" style="border-top:3px solid #F39C12;margin-top:0;">
            <tr>
              <td style="padding:18px 16px;text-align:right;font-size:20px;font-weight:800;color:#2C3E50;">
                Total: <span style="color:#F39C12;">\${$totalFmt}</span>
              </td>
            </tr>
          </table>

          <p style="margin:28px 0 0;font-size:14px;color:#7F8C8D;line-height:1.7;border-left:4px solid #F39C12;padding-left:14px;">
            ¡Gracias por elegir <strong style="color:#2C3E50;">El Buen Comer</strong>!<br>
            En breve tu pedido estará en preparación.
          </p>
        </td>
      </tr>

      <!-- FOOTER -->
      <tr>
        <td style="background:#1a1a2e;padding:22px 40px;text-align:center;">
          <p style="margin:0;font-size:13px;color:rgba(255,255,255,0.60);">
            &copy; 2026 El Buen Comer &nbsp;·&nbsp; 099 267 113 &nbsp;·&nbsp; info@elbuencomer.com
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
}

function buildEmailText($nombre, $items, $total, $fecha) {
    $fechaFmt = date('d/m/Y', strtotime($fecha));
    $lines = "El Buen Comer — Confirmación de Pedido\n";
    $lines .= "========================================\n\n";
    $lines .= "Hola, {$nombre}!\n";
    $lines .= "Tu pedido del {$fechaFmt} fue confirmado.\n\n";
    $lines .= "Detalle:\n";
    foreach ($items as $item) {
        $sub = $item['precio'] * $item['cantidad'];
        $lines .= "  - {$item['nombre']} x{$item['cantidad']} = \${$sub}\n";
    }
    $lines .= "\nTOTAL: \${$total}\n\n";
    $lines .= "Gracias por elegir El Buen Comer!\n";
    $lines .= "099 267 113 | info@elbuencomer.com\n";
    return $lines;
}
