Framework.AJAXRequest = (function(action, oncomplete, GET, POST)
{
    if (!oncomplete)
    {
        return;
    }
    
    var xmlhttp = null;
    
    var ajax_action = (function()
    {
        if (xmlhttp.readyState == 4)
        {
            document.body.style.cursor = '';
            //try
            //{
                oncomplete(JSON.parse(xmlhttp.responseText));
            //}
            //catch (e)
            //{alert(xmlhttp.responseText);
            //    console.log('Invalid response for ' + action + ': ' + e);
            //    return;
            //}
        }
    });
        
    if (window.XMLHttpRequest)
    {
        xmlhttp = new XMLHttpRequest();
    }
    else
    {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    
    var url;
  
    if (!GET)
    {
        GET  = 'ajax' + Framework.template;
    }
    else
    {
        GET += '&ajax' + Framework.template;
    }

    url = action + '?' + GET;
    
    xmlhttp.onreadystatechange = ajax_action;
    
    if (POST)
    {
        xmlhttp.open('POST', url, true);
        
        xmlhttp.setRequestHeader('Content-type',   'application/x-www-form-urlencoded');
        xmlhttp.setRequestHeader('Content-length', POST.length);
        xmlhttp.setRequestHeader('Connection',     'close');
    }
    else
    {
        xmlhttp.open('GET', url, true);
        
        POST = '';
    }
    
    document.body.style.cursor = 'progress';
    xmlhttp.send(POST);
});