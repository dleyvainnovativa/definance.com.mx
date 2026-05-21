<!doctype html>
<html>

<body>
    <div
        style='background-color:#FAFAFA;color:#242424;font-family:"Helvetica Neue", "Arial Nova", "Nimbus Sans", Arial, sans-serif;font-size:16px;font-weight:400;letter-spacing:0.15008px;line-height:1.5;margin:0;padding:32px 0;min-height:100%;width:100%'>
        <table
            align="center"
            width="100%"
            style="margin:0 auto;max-width:600px;background-color:#FFFFFF;border-radius:16px"
            role="presentation"
            cellspacing="0"
            cellpadding="0"
            border="0">
            <tbody>
                <tr style="width:100%">
                    <td>
                        <div style="padding:24px 24px 8px 24px;text-align:left">
                            <img
                                alt=""
                                src="https://definance.com.mx/img/logo.png"
                                height="100"
                                style="height:100px;outline:none;border:none;text-decoration:none;vertical-align:middle;display:inline-block;max-width:100%" />
                        </div>
                        <h3
                            style="font-weight:bold;text-align:left;margin:0;font-size:20px;padding:32px 24px 0px 24px">
                            Se ha detectado un inicio de sesión en tu cuenta dentro de
                            nuestra plataforma.
                        </h3>
                        <div style="font-size:16px;padding:16px 24px 0px 24px">
                            <small>
                                <strong>Fecha y Hora: </strong>
                                {{ $dateTime }}
                            </small>
                        </div>

                        <div style="font-size:16px;padding:0px 24px 0px 24px">
                            <small>
                                <strong>Dispositivo: </strong>
                                {{ $device }}
                            </small>
                        </div>

                        <div style="font-size:16px;padding:0px 24px 16px 24px">
                            <small>
                                <strong>IP: </strong>
                                {{ $ip }}
                            </small>
                        </div>
                        <div
                            style="color:#474849;font-size:14px;font-weight:normal;text-align:left;padding:8px 24px 16px 24px">
                            Si reconoces esta actividad, no es necesario realizar ninguna
                            acción.
                        </div>
                        <div
                            style="color:#474849;font-size:14px;font-weight:normal;text-align:left;padding:8px 24px 16px 24px">
                            En caso de que no hayas sido tú, te recomendamos cambiar tu
                            contraseña de inmediato para proteger tu cuenta.
                        </div>
                        <div
                            style="color:#474849;font-size:14px;font-weight:bold;text-align:left;padding:8px 24px 16px 24px">
                            Este es un mensaje automático de seguridad, por lo que no es
                            necesario responder a este correo.
                        </div>

                        <div style="padding:16px 24px 16px 24px">
                            <hr
                                style="width:100%;border:none;border-top:1px solid #EEEEEE;margin:0" />
                        </div>
                        <div
                            style="color:#474849;font-size:12px;font-weight:normal;text-align:left;padding:4px 24px 24px 24px">
                            ¿Necesitas ayuda? Solo responde este correo para contactar a
                            Soporte
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>