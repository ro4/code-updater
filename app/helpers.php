<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!function_exists('access_object')) {
    function access_object(stdClass $obj, $key_path, $default = null)
    {
        if (!is_string($key_path)) {
            return $default;
        }

        $paths = explode('.', $key_path);
        $value = $obj;
        foreach ($paths as $path) {
            if (isset($value->$path)) {
                $value = $value->$path;
            } else {
                $value = $default;
                break;
            }
        }

        return $value;
    }
}

if (!function_exists('access_array')) {
    function access_array(array $array, $key_path, $default = null)
    {
        if (!is_string($key_path)) {
            return $default;
        }

        $paths = explode('.', $key_path);
        $value = $array;
        foreach ($paths as $path) {
            if (isset($array[$path])) {
                $value = $array[$path];
            } else {
                $value = $default;
                break;
            }
        }

        return $value;
    }
}

if (!function_exists('notify')) {
    function notify($body)
    {
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        $notification = \App\Helpers\Config::instance()->notification;
        $smtpHost = access_object($notification, 'email.smtp_host');
        $smtpPort = access_object($notification, 'email.smtp_port');
        $userName = access_object($notification, 'email.account');
        $password = access_object($notification, 'email.password');
        $receivers = access_object($notification, 'receivers');
        try {
            //Server settings
            $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = $smtpHost;  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = $userName;                 // SMTP username
            $mail->Password = $password;                           // SMTP password
            $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $smtpPort;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom($userName, 'Jupiter Notify');
            foreach ($receivers as $receiver) {
                $mail->addAddress(access_object($receiver, 'address'), access_object($receiver, 'name'));
            }
            //Content
            $mail->Subject = '代码pull通知';
            $mail->Body    = $body;
            $mail->AltBody = $body;

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }
}
