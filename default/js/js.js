var Spaark =
{
    ROOT       : null,
    HREF_ROOT  : null,
    AREA       :
    {
        innerHTML : ""
    },
    init       : false,
    include    :
    {
        js  : { },
        css : { }
    },
    validators : [],
    scrollOffset : 5,
    topOffset    : 0
};
var Framework = Spaark;

if (typeof JSON == 'undefined')
{
    JSON =
    {
        parse : (function(code)
        {
            return eval(code);
        })
    };
}

/**
 * Override document.write, because it is evil
 */
document.write = document.writeln = (function(str)
{
    console.error('Spaark document.write not supported');
    
    var el       = document.createElement('div');
    el.innerHTML = str;
    document.body.appendChild(el);
});

Element.prototype.getData = (function(data)
{
    if (typeof this.dataset != 'undefined')
    {
        return this.dataset[data];
    }
    else
    {
        return this.getAttribute
        (
             'data-'
            + data.replace(/([a-z])([A-Z])/, '$1-$2').toLowerCase()
        );
    }
});