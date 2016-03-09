Framework.ajaxLoad = (function(action, element)
{
    var onLoad = (function(response)
    {
        document.getElementById(element).innerHTML = response.content;
    });

    new Framework.AJAXRequest(action, onLoad);
});