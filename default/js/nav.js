
if (window.onpopstate == 'undefined')
{
    window.l = (function()
    {
        return true;
    });
}
else
{
    window.l            = (function(e, el)
    {
        if (e.preventDefault) e.preventDefault();

        window.test = e;

        if (el.pathname != window.location.pathname)
        {
            Framework.loadPage(el.href);
        }
        else
        {
            Framework.scrollToAnchor(el.hash.substr(1));
        }

        return false;
    });
    
    window.onpopstate   = (function(e)
    {
        if (e.state)
        {
            if (e.preventDefault) e.preventDefault();

            loadPage(e.state.url);

            return false;
        }
    });
    
    window.onhashchange = (function(e)
    {
        if (e.preventDefault) e.preventDefault();

        if (window.location.hash.substr(0, 2) == '#!')
        {
            loadPage(window.location.hash.substr(2));
        }

        return false;
    });
    
    var loadPage        = (function(page)
    {
        if (window._gaq)
        {
            window._gaq.push(['_trackPageview', page]);
        }
        
        new Framework.AJAXRequest
        (
            page.split('#')[0],
            makeOnLoad(page)
        );
    });
    
    Framework.loadPage  = (function(page)
    {
        if (window.history && window.history.pushState)
        {
            if (forgetPage)
            {
                window.history.replaceState(null, '', page);
                forgetPage = false;
            }
            else
            {
                window.history.pushState(null, '', page);
            }
        }
        else
        {
            var savedFunc        = window.onhashchange;
            window.onhashchange  = null;
            window.location.href = '#!' + page.substr(Framework.origin.length);
            window.onhashchange  = savedFunc;
        }
        
        loadPage(page);
    });
    
    Framework.reload    = (function()
    {
        loadPage(window.location.href.substr(Framework.ROOT.length));
    });

    Framework.scrollToAnchor = (function (name)
    {
        if (name == '' || name == '_top')
        {
            scroll(0);
            return;
        }

        var el =
            document.getElementById(name) ||
            document.getElementsByName(name)[0];
        var offset =
            el.getData('scrollOffset') ||
            Framework.scrollOffset;

        scroll(el.offsetTop - offset);
    });
}

var makeOnLoad = (function(page)
{
    if (!page)
    {
        page = Framework.getPage();
    }
    
    return (function(response)
    {
        var el   = document.getElementById(response.element);
        var head = document.getElementsByTagName('head')[0];
        var fade = new Fader(el);
        
        var waitingScripts = 1;
        var executeScript  = (function()
        {
            waitingScripts--;
            if (response.script && waitingScripts == 0)
            {
                waitingScripts = -1;
                eval(response.script);
            }
        });
        
        var addIncludes = (function(type, tag, attr)
        {
            for (var i in response[type])
            {
                var path = response[type][i];
                
                if (!Framework.include[type][path])
                {
                    waitingScripts++;
                    var inc    = document.createElement(tag);
                    
                    if (type == 'css')
                    {
                        inc.rel = 'stylesheet';
                    }
                    
                    inc[attr]  = path;
                    inc.onload = executeScript;
                    head.appendChild(inc);
                    Framework.include[type][path] = inc;
                }
            }
        });
        
        for (var path in Framework.include.css)
        {
            if (response.css.indexOf(path) == -1)
            {
                document.head.removeChild(Framework.include.css[path]);
                
                delete Framework.include.css[path];
            }
        }
        
        fade.hide();
        
        el.innerHTML              = response.content;
        Framework.template        = response.template;
        document.title            = response.title;
        
        for (var i in response.statics)
        {
            var val = document.getElementById('spaark_' + i);
            
            if (val)
            {
                val.innerHTML = response.statics[i];
            }
        }

        addIncludes('css', 'link',   'href');
        addIncludes('js',  'script', 'src');
        
        executeScript();
        
        if (page.indexOf('#') != -1)
        {
            Spaark.scrollToAnchor(page.split('#')[1]);
        }
        else
        {
            scroll(Spaark.topOffset);
        }
        fade.fadeIn();
        
        replaceState(new State());
    });
});

Framework.loadContent = (function(request, target, e)
{
    if (typeof e == 'object' && e.preventDefault)
    {
        e.preventDefault();
    }

    new Framework.AJAXRequest
    (
        request,
        contentOnLoad,
        'target=' + target
    );
});

var contentOnLoad = (function(response)
{
    var el;
    if (response.element == '$body')
    {
        el = document.body;
    }
    else
    {
        el = document.getElementById(response.element);
    }

    var fade = new Fader(el);

    //Update the document
    fade.hide();
    el.innerHTML = response.content;
    fade.fadeIn();
});

Framework.getPage = (function(page)
{
    if (!page)
    {
        page = window.location.href;
    }

    return page.substr(Framework.ROOT.length - 1);
});

var getScrollY = (function()
{
    return typeof window.scrollY == 'undefined'
        ? document.documentElement.scrollTop
        : window.scrollY;
});

var getScrollX = (function()
{
    return typeof window.scrollX == 'undefined'
        ? document.documentElement.scrollLeft
        : window.scrollX;
});

var scroll = (function(target)
{
    var lastScroll = getScrollY();
    
    var doScroll   = (function()
    {
        if (lastScroll != getScrollY()) return;
        
        lastScroll = getScrollY();
        
        if (getScrollY() > target)
        {
            window.scrollTo(getScrollX(), getScrollY() - (getScrollY() - target) / 20);
        }
        else if (getScrollY() < target)
        {
            window.scrollTo(getScrollX(), getScrollY() - (getScrollY() - target) / 20);
        }
        else
        {
            return;
        }
        
        if (lastScroll != getScrollY())
        {
            lastScroll = getScrollY();
            window.setTimeout(doScroll, 1);
        }
    });
    
    doScroll();
});