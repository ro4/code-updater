<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!function_exists('access_object')) {
    function access_object(stdClass $obj, $key_path, $default = null)
    {
        return a_t($obj, $key_path, $default);
    }
}

if (!function_exists('access_array')) {
    function access_array(array $array, $key_path, $default = null)
    {
        return a_t($array, $key_path, $default);
    }
}

if (!function_exists('a_t')) {
    function a_t($target, $key_path, $default = null)
    {
        if (!is_string($key_path)) {
            return $default;
        }
        $paths = explode('.', $key_path);
        $value = $target;
        if (is_array($target)) {
            foreach ($paths as $path) {
                if (isset($value[$path])) {
                    $value = $value[$path];
                } else {
                    $value = $default;
                    break;
                }
            }
        } elseif (is_object($target)) {
            foreach ($paths as $path) {
                if (isset($value->$path)) {
                    $value = $value->$path;
                } else {
                    $value = $default;
                    break;
                }
            }
        } else {
            $value = $default;
        }
        return $value;
    }
}

if (!function_exists('notify')) {
    function notify($body)
    {
        $mail
                      = new PHPMailer(true);                              // Passing `true` enables exceptions
        $notification = \App\Helpers\Config::instance()->notification;
        $smtpHost     = a_t($notification, 'email.smtp_host');
        $smtpPort     = a_t($notification, 'email.smtp_port');
        $userName     = a_t($notification, 'email.account');
        $password     = a_t($notification, 'email.password');
        $receivers    = a_t($notification, 'receivers');
        try {
            //Server settings
            $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host     = $smtpHost;  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = $userName;                 // SMTP username
            $mail->Password = $password;                           // SMTP password
            $mail->SMTPSecure
                            = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port
                            = $smtpPort;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom($userName, 'Notify');
            foreach ($receivers as $receiver) {
                $mail->addAddress(a_t($receiver, 'address'), a_t($receiver, 'name'));
            }
            //Content
            $mail->isHTML(true);
            $mail->Subject = 'code pull result';
            $mail->Body    = str_replace("\n", "<br/>", $body);
            $mail->AltBody = $body;

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }
}
