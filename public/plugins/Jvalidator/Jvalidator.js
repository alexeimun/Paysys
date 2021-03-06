var pattern = /^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/;
var url = '';
var docId = -1;
var emailUser = '@';
var cliente = 'Deudor';
var elements = '.numero, .correo, .obligatorio, .telefono, .documento, .clave';
var events = 'keyup keypress change click';

function jValidate(field, event)
{
    //Se elige la etiqueta div padre
    var div = field.closest('div');
    var validate = true;

    if ((field.val() == '' || field.val() == null))
    {
        if (field.hasClass('obligatorio')) div.addClass('has-error error');
        else div.removeClass('has-error error_correo');
        validate = false;
    }
    else div.removeClass('error');

    if (field.hasClass('clave') && field.val() != '')
    {
        if ($('.confirmar').val() != $('.claveinicial').val())
        {
            div.addClass('has-error error_clave');
        }
        else
        {
            $('.confirmar').closest('div').removeClass('has-error error_clave');
            $('.claveinicial').closest('div').removeClass('has-error error_clave');
        }
    }
    else if (field.hasClass('numero'))
    {
        if (field.hasClass('documento') && (event.type == 'change' || event.type == 'click'))
        {
            if (field.val() != '' && field.val() != docId)
            {
                $.post(url, {'DOCUMENTO': field.val(), CLIENTE: cliente}, function (data)
                {
                    if (data == 'no')
                    {
                        validate = false;
                        div.addClass('has-error error_documento');
                    }
                    else if (field.val().length >= 5 && field.val().length <= 15)
                    {
                        div.removeClass('has-error error_documento');
                    }
                });
            }
        }

        else if (field.hasClass('dinero'))
        {
            field.priceFormat({
                prefix: '$ ',
                thousandsSeparator: ','
            });
            div.removeClass('has-error');
        }
        else if (event.type == 'keypress')
        {
            var key = event.keyCode;
            if (key < 48 || key > 57)
            {
                if (field.hasClass('telefono') || field.hasClass('documento'))
                {
                    event.preventDefault();
                }
                else if (key == 46 || key == 8)
                {
                    if (field.hasClass('porcentaje'))
                    {
                        if (field.val().indexOf('.') != -1)
                        {
                            event.preventDefault();
                        }
                    }
                }
                else
                {
                    validate = false;
                    event.preventDefault();
                }
            }
            else
            {
                div.removeClass('has-error');
                if (field.hasClass('porcentaje'))
                {
                    if (!((field.val().length < 4 && field.val().indexOf('.') != -1) || field.val().length < 2))
                    {
                        event.preventDefault();
                    }
                    else
                    {
                        div.removeClass('has-error');
                    }
                }
                else if (field.hasClass('telefono'))
                {
                    if (field.val().length < 6)
                    {
                        console.log(field.val().length);
                        validate = false;
                        div.addClass('error_telefono has-error');
                    }
                    else if (field.val().length >= 6 && field.val().length < 10)
                    {
                        div.removeClass('error_telefono has-error');
                    }
                    else
                    {
                        div.removeClass('error_telefono has-error');
                        event.preventDefault();
                    }
                }
                else if (field.hasClass('documento'))
                {
                    if (field.val().length < 4)
                    {
                        div.addClass('error_documento_longitud has-error');
                        validate = false;
                    }
                    else if (field.val().length >= 4 && field.val().length <= 15)
                    {
                        div.removeClass('error_documento_longitud has-error');
                    }
                    else
                    {
                        div.removeClass('error_documento_longitud has-error');
                        event.preventDefault();
                    }
                }
            }
        }
    }
    else if (field.hasClass('correo'))
    {
        if (event.type == 'change' || event.type == 'click')
        {
            if (field.hasClass('correo_unico') && field.val() != '' && field.val() != emailUser)
            {
                $.post(url, {'CORREO': field.val()}, function (data)
                {
                    if (data == 'no')
                    {
                        validate = false;
                        div.addClass('has-error error_correo_existe');
                    }
                    else div.removeClass('has-error error_correo_existe');
                });
            }
        }
        else if (event.type == 'keyup')
        {
            if (field.val() == '')
            {
                div.removeClass('has-error error_correo_existe');
            }
            else if (!field.val().match(pattern))
            {
                div.addClass('has-error error_correo');
                validate = false;
            }
            else div.removeClass('error_correo has-error');
        }
    }
    else if (validate) div.removeClass('error_correo has-error error_documento error_correo_existe error error_telefono error_documento_longitud');

    return validate;
}

function validateForm()
{
    var pass = true;

    $('.obligatorio').each(function (index, element)
        {
            element = $(element);

            if (element.val() == '' || element.val() == null)
            {
                element.closest('div').addClass('has-error error');
                $('body,html').animate({scrollTop: 0}, 200);
                pass = false;
            }
            else if (element.hasClass('correo'))
            {
                if (!element.val().match(pattern))
                {
                    element.closest('div').addClass('has-error error_correo');
                    $('body,html').animate({scrollTop: 0}, 200);
                    pass = false;
                }
                else element.closest('div').removeClass('error_correo has-error');
            }
            else if (element.hasClass('correo_unico') && element.val() != '')
            {
                if (element.val() != '' && emailUser != element.val())
                {
                    $.post(url, {'CORREO': element.val()}, function (data)
                    {
                        if (data == 'no')
                        {
                            pass = false;
                            element.closest('div').removeClass('has-error error_correo_existe');
                        }
                        else element.closest('div').addClass('has-error error_correo_existe');
                    });
                }
            }
            else if (element.hasClass('documento'))
            {
                if (element.val().length < 5 || element.val().length > 15)
                {
                    element.closest('div').addClass('error_documento_longitud has-error');
                    pass = false;
                    $('body,html').animate({scrollTop: 0}, 200);
                }
            }
            else if (element.hasClass('telefono'))
            {
                if (element.val().length < 7 || element.val().length > 10)
                {
                    element.closest('div').addClass('error_telefono has-error');
                    pass = false;
                    $('body,html').animate({scrollTop: 0}, 200);
                }
            }
        }
    );
    if (!pass)Message();
    else killMessage();
    return pass;
}
function killMessage()
{
    $('#jmsg').next('br').remove().end().remove();
}

function Message(Msg, Type, Element, Time)
{
    if (!Element) Element = 'form.form-horizontal';

    if (!Msg)Msg = 'El formulario contiene los siguientes errores...';
    if (!Time && Time != 0)Time = 500;

    var icon = 'close-circled';
    switch (Type)
    {
        case 'danger':
            icon = 'close-circled';
            break;
        case 'warning':
            icon = 'close-circled';
            break;
        case 'info':
            icon = 'checkmark-round';
            break;
        case 'success':
            icon = 'checkmark-round';
            break;
        default :
            Type = 'danger';
            break;
    }
    $('#jmsg').next('br').remove().end().remove();
    $(Element).prepend($('<div id="jmsg"  style="font-size: 12pt;" class="callout callout-' + Type + ' no-margin">' +
    '<span onclick="killMessage()" class="ion ion-' + icon + '"></span>&nbsp;' + Msg + '</div><br>')).hide().show(Time);
}