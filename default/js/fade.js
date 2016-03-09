
window.Fader = (function(element)
{
    this.fadeLevel = 0;
    this.el        = element;
});

window.Fader.prototype.fadeIn = (function(mod)
{
    if (!mod) mod = 1;

    this.fadeLevel += mod * 0.04;
    this.setElFadeLevel();

    var obj = this;

    if
    (
        (mod >= 1 && this.fadeLevel < 1) ||
        (mod <= 0 && this.fadeLevel > 0)
    )
    {
        window.setTimeout
        (
            (function()
            {
                obj.fadeIn(mod);
            }),
            15
        );
    }
});

window.Fader.prototype.fadeOut = (function()
{
    this.fadeIn(-1);
});

window.Fader.prototype.hide = (function()
{
    this.fadeLevel = 0;
    this.setElFadeLevel();
});

window.Fader.prototype.setElFadeLevel = (function()
{
    this.el.style.opacity = this.fadeLevel;
    this.el.style.filter  = 'alpha(opacity=' + (this.fadeLevel * 100) + ')';
});