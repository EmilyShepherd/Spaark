var forgetPage;

Framework.forgetPage = (function()
{
    forgetPage = true;
});

window.s = (function(base, template, js, css)
{
    if (!window.location.origin)
    {
        window.location.origin = window.location.protocol + "/" + '/' + window.location.hostname + (window.location.port ? ':' + window.location.port : '');
    }

    Framework.HREF_ROOT   = base;
    Framework.ROOT        = window.location.origin + Framework.HREF_ROOT;
    Framework.template    = template;
    Framework.origin      =
          window.location.protocol + '/' + '/'
        + window.location.host;

    for (var i in css)
    {
        Framework.include.css[css[i]] =
            document.getElementById('s_' + i);
    }
    for (var i in js)
    {
        Framework.include.js[js[i]] = true;
    }

    if (window.location.hash.substr(0, 2) == '#!')
    {
        document.getElementById('spaark_page').innerHTML = '';
        Framework.forgetPage();

        Framework.loadPage
        (
              Framework.origin
            + window.location.hash.substr(2)
        );
    }
    else
    {
        replaceState(new State());
    }

    window.s = null;
});