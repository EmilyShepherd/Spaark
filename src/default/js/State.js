
var State = (function()
{
    this.title    = document.title;
    this.template = Framework.template;
    this.content  = document.body.innerHTML;
    this.url      = window.location.href;
});

var loadState = (function(state)
{
    var currentTemplate = Framework.template.split(';');
    var targetTemplate  = state.template.split(';');
    var last            = '';
    var XMLDocument;
    var from;
    var to;
    var text            =
          '<!DOCTYPE html [<!ATTLIST div id ID #IMPLIED>]>'
        + '<html>'
        +   state.content
        + '</html>';

    //Figure out the lowest template in common between the
    //two documents.
    for (var i in targetTemplate)
    {
        if (targetTemplate[i] && currentTemplate[i])
        {
            if (targetTemplate[i] == currentTemplate[i])
            {
                last = targetTemplate[i];
            }
            else if (last)
            {
                break;
            }
        }
    }

	XMLDocument = document.implementation.createDocument('http://www.w3.org/1999/xhtml', 'html', null);
	XMLDocument.documentElement.appendChild(XMLDocument.createElement('body'));
	XMLDocument.body.innerHTML = state.content;

    //Use the calculated common template to grab the content
    if (last)
    {
        to   = document.getElementById('template_' + last);
        from = XMLDocument.getElementById('template_' + last);
    }
    else
    {
        to   = document.body;
        from = XMLDocument.body;
    }

    //Clear the reciving content area and fill it with the
    //children
    to.innerHTML = '';
    hide(to);
    var len      = from.childNodes.length;
    for (var i = 0; i < len; i++)
    {
        //var child = document.adoptNode(from.childNodes.item(0));
        to.appendChild(from.childNodes.item(0));
    }

    //Force Opera to treat the elements as HTML, simply adding
    //them doesn't work
    if (navigator.appName == 'Opera')
    {
        to.innerHTML = to.innerHTML;
    }

    fadeIn(to);

    document.title     = state.title;
    Framework.template = state.template;
});

var replaceState = (function(state)
{
    if (window.history && window.history.replaceState)
    {
        window.history.replaceState
        (
            state,
            '',
            window.location.href
        );
    }
});