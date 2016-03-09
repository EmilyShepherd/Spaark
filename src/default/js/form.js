var ERROR_REQUIRED   = 1;
var ERROR_VALIDATION = 2;

Framework.submitForm = (function(form)
{
    var inputs = form.getElementsByTagName('input');
    var STRING = '';
    var POST   = '';
    var GET    = '';
    var error  = false;

    for (var i in inputs)
    {
        if (i == 'length' || i == 'item') continue;
        var input = inputs[i];

        if (typeof input.dataset == 'undefined') input.dataset = { };

        alert(input.name);

        if
        (
            (input.name)                                &&
            (input.type != 'checkbox' || input.checked) &&
            (input.type != 'radio'    || input.checked)
        )
        {
            if (!input.value && input.dataset.required)
            {
                error = true;
                Framework.formError(ERROR_REQUIRED, input);
            }
            else if
            (
                Framework.validators[input.dataset.validate]    &&
                !Framework.validators[input.dataset.validate](input.value)
            )
            {
                error = true;
                Framework.formError(ERROR_VALIDATION, input);
            }
            else
            {
                STRING += '&' + input.name + '=' + encodeURIComponent(input.value);
            }
        }
    }

    inputs = form.getElementsByTagName('textarea');

    for (var i in inputs)
    {
        if (i == 'length' || i == 'item') continue;
        var input = inputs[i];

        if (typeof input.dataset == 'undefined') input.dataset = { };

        if
        (
            (input.name)                                &&
            (input.type != 'checkbox' || input.checked)
        )
        {
            if (!input.value && input.dataset.required)
            {
                error = true;
                Framework.formError(ERROR_REQUIRED, input);
            }
            else if
            (
                Framework.validators[input.dataset.validate]    &&
                !Framework.validators[input.dataset.validate](input.value)
            )
            {
                error = true;
                Framework.formError(ERROR_VALIDATION, input);
            }
            else
            {
                STRING += '&' + input.name + '=' + encodeURIComponent(input.value);
            }
        }
    }

    if (error) return;

    if (form.method == 'get')
    {
        GET = STRING;
    }
    else
    {
        POST = STRING;
    }

    console.log(Framework.getPage(form.action));

    new Framework.AJAXRequest
    (
        Framework.getPage(form.action),
        makeOnLoad(),
        GET,
        POST
    );
});

window.h = (function (name, func)
{
    Framework.validators[name] = func;
});

window.f = (function (e)
{
    e.preventDefault();
    Framework.submitForm(e.target);
});

Framework.formError = (function(type, input)
{
    input.style.outline = '1px solid red';
});