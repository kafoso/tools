(function(){
    var each = function(array, callback){
        for (var i=0;i<array.length; i++) {
            callback(array[i], i);
        }
    };
    var getCountOfAllExpandedParents = function(node){
        var count = 0;
        while (node && node.parentNode) {
            var _class = node.getAttribute('class');
            if (_class) {
                var classes = _class.replace(/\s+/, ' ').replace(/^\s+|\s+$/, '').split(' ');
                for (var i in classes) {
                    if ("expanded" == classes[i]) {
                        count++;
                        break;
                    }
                }
            }
            node = node.parentNode;
        }
        return count;
    };
    var readCookie = function(name){
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1,c.length);
            }
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length,c.length);
            }
        }
        return null;
    };
    var writeCookie = function(name, value, days) {
        var expires;
        if (days) {
            var date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            expires = "; expires="+date.toGMTString();
        }
        else {
            expires = "";
        }
        document.cookie = name+"="+value+expires+"; path=/";
    };
    var mainNodes = document.getElementsByClassName('Kafoso_Tools_Debug_Dumper_1a83b742_c5ce_11e6_9c64_842b2bb76d27');
    each(mainNodes, function(main){
        var foldMarkSpan = document.createElement('span');
        foldMarkSpan.innerHTML = '&hellip;';
        foldMarkSpan.setAttribute('class', 'fold-marker');
        var clone;
        var expandedNodes = main.getElementsByClassName('expanded');
        var collapseLevel = readCookie("Kafoso_Tools_Debug_Dumper_1a83b742_c5ce_11e6_9c64_842b2bb76d27_options_collapseLevel");
        if (collapseLevel) {
            collapseLevel = parseInt(collapseLevel, 10);
        } else {
            collapseLevel = 2;
            writeCookie("Kafoso_Tools_Debug_Dumper_1a83b742_c5ce_11e6_9c64_842b2bb76d27_options_collapseLevel", collapseLevel, 9999);
        }
        var inputNode = main.getElementsByClassName('options')[0].getElementsByClassName('collapseLevel')[0];
        inputNode.value = collapseLevel;
        var onchangeCallback = function(){
            setTimeout(function(){
                var newCollapseLevel = parseInt(inputNode.value, 10);
                writeCookie("Kafoso_Tools_Debug_Dumper_1a83b742_c5ce_11e6_9c64_842b2bb76d27_options_collapseLevel", newCollapseLevel, 9999);
            }, 0);
        };
        inputNode.onkeydown = onchangeCallback;
        inputNode.onpaste = onchangeCallback;
        inputNode.onchange = onchangeCallback;
        if (collapseLevel && collapseLevel > 0) {
            each(expandedNodes, function(expandedNode){
                if (expandedNode.parentNode) {
                    if (getCountOfAllExpandedParents(expandedNode) > collapseLevel) {
                        clone = foldMarkSpan.cloneNode(true);
                        expandedNode.parentNode.insertBefore(clone, expandedNode);
                        expandedNode.setAttribute('class', 'collapsed');
                        clone.onclick = function(){
                            this.nextSibling.setAttribute("class", "expanded");
                            this.parentNode.removeChild(this);
                        };
                    }
                }
            });
        }
        each(main.getElementsByClassName('optionsButton'), function(optionsButton){
            optionsButton.onclick = function(){
                if (null === optionsButton.nextSibling.offsetParent) {
                    optionsButton.nextSibling.style.display = "block";
                } else {
                    optionsButton.nextSibling.style.display = "none";
                }
            };
        });
    });
})();