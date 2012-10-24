/* Script by: www.jtricks.com
 * Version: 1.6 (20110222)
 * Latest version: www.jtricks.com/javascript/navigation/floating.html
 */
var floatingMenu =
{
    hasInner: typeof(window.innerWidth) == 'number',
    hasElement: typeof(document.documentElement) == 'object'
        && typeof(document.documentElement.clientWidth) == 'number'
};

var floatingArray =
[
];

floatingMenu.add = function(obj, options)
{
    var name;
    var menu;

    if (typeof(obj) === "string")
        name = obj;
    else
        menu = obj;
        

    if (options == undefined)
    {
        floatingArray.push( 
            {
                id: name,
                menu: menu,

                targetLeft: 0,
                targetTop: 0,

                snap: true
            });
    }
    else
    {
        floatingArray.push( 
            {
                id: name,
                menu: menu,

                targetLeft: options.targetLeft,
                targetRight: options.targetRight,
                targetTop: options.targetTop,
                targetBottom: options.targetBottom,

                centerX: options.centerX,
                centerY: options.centerY,

                prohibitXMovement: options.prohibitXMovement,
                prohibitYMovement: options.prohibitYMovement,

                snap: options.snap,
                ignoreParentDimensions: options.ignoreParentDimensions,

                scrollContainer: options.scrollContainer,
                scrollContainerId: options.scrollContainerId
            });
    }
};

floatingMenu.findSingle = function(item)
{
    if (item.id)
        item.menu = document.getElementById(item.id);

    if (item.scrollContainerId)
        item.scrollContainer = document.getElementById(item.scrollContainerId);
};

floatingMenu.move = function (item)
{
    if (!item.prohibitXMovement)
    {
        item.menu.style.left = item.nextX + 'px';
        item.menu.style.right = '';
    }

    if (!item.prohibitYMovement)
    {
        item.menu.style.top = item.nextY + 'px';
        item.menu.style.bottom = '';
    }
};

floatingMenu.scrollLeft = function(item)
{
    // If floating within scrollable container use it's scrollLeft
    if (item.scrollContainer)
        return item.scrollContainer.scrollLeft;

    var w = window;

    // Find top window scroll parameters if we're IFRAMEd
    while (w != w.parent)
        w = w.parent;

    return this.hasInner
        ? w.pageXOffset  
        : this.hasElement  
          ? w.document.documentElement.scrollLeft  
          : w.document.body.scrollLeft;
};

floatingMenu.scrollTop = function(item)
{
    // If floating within scrollable container use it's scrollTop
    if (item.scrollContainer)
        return item.scrollContainer.scrollTop;

    var w = window;

    // Find top window scroll parameters if we're IFRAMEd
    while (w != w.parent)
        w = w.parent;

    return this.hasInner
        ? w.pageYOffset
        : this.hasElement
          ? w.document.documentElement.scrollTop
          : w.document.body.scrollTop;
};

floatingMenu.windowWidth = function()
{
    return this.hasElement
        ? document.documentElement.clientWidth
        : document.body.clientWidth;
};

floatingMenu.windowHeight = function()
{
    if (floatingMenu.hasElement && floatingMenu.hasInner)
    {
        // Handle Opera 8 problems
        return document.documentElement.clientHeight > window.innerHeight
            ? window.innerHeight
            : document.documentElement.clientHeight
    }
    else
    {
        return floatingMenu.hasElement
            ? document.documentElement.clientHeight
            : document.body.clientHeight;
    }
};

floatingMenu.documentHeight = function()
{
    var innerHeight = this.hasInner
        ? window.innerHeight
        : 0;

    var body = document.body,
        html = document.documentElement;

    return Math.max(
        body.scrollHeight,
        body.offsetHeight, 
        html.clientHeight,
        html.scrollHeight,
        html.offsetHeight,
        innerHeight);
};

floatingMenu.documentWidth = function()
{
    var innerWidth = this.hasInner
        ? window.innerWidth
        : 0;

    var body = document.body,
        html = document.documentElement;

    return Math.max(
        body.scrollWidth,
        body.offsetWidth, 
        html.clientWidth,
        html.scrollWidth,
        html.offsetWidth,
        innerWidth);
};

floatingMenu.calculateCornerX = function(item)
{
    var offsetWidth = item.menu.offsetWidth;

    if (item.centerX)
        return this.scrollLeft(item) + (this.windowWidth() - offsetWidth)/2;

    var result = this.scrollLeft(item) - item.parentLeft;
    if (item.targetLeft == undefined)
    {
        result += this.windowWidth() - item.targetRight - offsetWidth;
    }
    else
    {
        result += item.targetLeft;
    }
        
    if (document.body != item.menu.parentNode
        && result + offsetWidth >= item.confinedWidthReserve)
    {
        result = item.confinedWidthReserve - offsetWidth;
    }

    if (result < 0)
        result = 0;

    return result;
};

floatingMenu.calculateCornerY = function(item)
{
    var offsetHeight = item.menu.offsetHeight;

    if (item.centerY)
        return this.scrollTop(item) + (this.windowHeight() - offsetHeight)/2;

    var result = this.scrollTop(item) - item.parentTop;
    if (item.targetTop === undefined)
    {
        result += this.windowHeight() - item.targetBottom - offsetHeight;
    }
    else
    {
        result += item.targetTop;
    }

    if (document.body != item.menu.parentNode
        && result + offsetHeight >= item.confinedHeightReserve)
    {
        result = item.confinedHeightReserve - offsetHeight;
    }

    if (result < 0)
        result = 0;
        
    return result;
};

floatingMenu.computeParent = function(item)
{
    if (item.ignoreParentDimensions)
    {
        item.confinedHeightReserve = this.documentHeight();
        item.confinedWidthReserver = this.documentWidth();
        item.parentLeft = 0;  
        item.parentTop = 0;  
        return;
    }

    var parentNode = item.menu.parentNode;
    var parentOffsets = this.offsets(parentNode, item);
    item.parentLeft = parentOffsets.left;
    item.parentTop = parentOffsets.top;

    item.confinedWidthReserve = parentNode.clientWidth;

    // We could have absolutely-positioned DIV wrapped
    // inside relatively-positioned. Then parent might not
    // have any height. Try to find parent that has
    // and try to find whats left of its height for us.
    var obj = parentNode;
    var objOffsets = this.offsets(obj, item);
    while (obj.clientHeight + objOffsets.top
           < item.menu.offsetHeight + parentOffsets.top)
    {
        obj = obj.parentNode;
        objOffsets = this.offsets(obj, item);
    }

    item.confinedHeightReserve = obj.clientHeight
        - (parentOffsets.top - objOffsets.top);
};

floatingMenu.offsets = function(obj, item)
{
    var result =
    {
        left: 0,
        top: 0
    };

    if (obj === item.scrollContainer)
        return;

    while (obj.offsetParent && obj.offsetParent != item.scrollContainer)
    {  
        result.left += obj.offsetLeft;  
        result.top += obj.offsetTop;  
        obj = obj.offsetParent;
    }  

    if (window == window.parent)
        return result;

    // we're IFRAMEd
    var iframes = window.parent.document.body.getElementsByTagName("IFRAME");
    for (var i = 0; i < iframes.length; i++)
    {
        if (iframes[i].contentWindow != window)
           continue;

        obj = iframes[i];
        while (obj.offsetParent)  
        {  
            result.left += obj.offsetLeft;  
            result.top += obj.offsetTop;  
            obj = obj.offsetParent;
        }  
    }

    return result;
};

floatingMenu.doFloatSingle = function(item)
{
    this.findSingle(item);

    var stepX, stepY;

    this.computeParent(item);

    var cornerX = this.calculateCornerX(item);

    var stepX = (cornerX - item.nextX) * .07;
    if (Math.abs(stepX) < .5 && item.snap
        || Math.abs(cornerX - item.nextX) == 1)
    {
        stepX = cornerX - item.nextX;
    }

    var cornerY = this.calculateCornerY(item);

    var stepY = (cornerY - item.nextY) * .07;
    if (Math.abs(stepY) < .5 && item.snap
        || Math.abs(cornerY - item.nextY) == 1)
    {
        stepY = cornerY - item.nextY;
    }

    if (Math.abs(stepX) > 0 ||
        Math.abs(stepY) > 0)
    {
        item.nextX += stepX;
        item.nextY += stepY;
        this.move(item);
    }
};

floatingMenu.fixTargets = function()
{
};

floatingMenu.fixTarget = function(item)
{
};

floatingMenu.doFloat = function()
{
    this.fixTargets();
    for (var i=0; i < floatingArray.length; i++)
    {
        this.fixTarget(floatingArray[i]);
        this.doFloatSingle(floatingArray[i]);
    }
    setTimeout('floatingMenu.doFloat()', 20);
};

floatingMenu.insertEvent = function(element, event, handler)
{
    // W3C
    if (element.addEventListener != undefined)
    {
        element.addEventListener(event, handler, false);
        return;
    }

    var listener = 'on' + event;

    // MS
    if (element.attachEvent != undefined)
    {
        element.attachEvent(listener, handler);
        return;
    }

    // Fallback
    var oldHandler = element[listener];
    element[listener] = function (e)
        {
            e = (e) ? e : window.event;
            var result = handler(e);
            return (oldHandler != undefined) 
                && (oldHandler(e) == true)
                && (result == true);
        };
};

floatingMenu.init = function()
{
    floatingMenu.fixTargets();

    for (var i=0; i < floatingArray.length; i++)
    {
        floatingMenu.initSingleMenu(floatingArray[i]);
    }

    setTimeout('floatingMenu.doFloat()', 100);
};

// Some browsers init scrollbars only after
// full document load.
floatingMenu.initSingleMenu = function(item)
{
    this.findSingle(item);
    this.computeParent(item);
    this.fixTarget(item);
    item.nextX = this.calculateCornerX(item);
    item.nextY = this.calculateCornerY(item);
    this.move(item);
};

floatingMenu.insertEvent(window, 'load', floatingMenu.init);


// Register ourselves as jQuery plugin if jQuery is present
if (typeof(jQuery) !== 'undefined')
{
    (function ($)
    {
        $.fn.addFloating = function(options)
        {
            return this.each(function()
            {
                floatingMenu.add(this, options);
            });
        };
    }) (jQuery);
}
