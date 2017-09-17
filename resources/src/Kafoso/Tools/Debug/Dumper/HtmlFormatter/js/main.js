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
var divNode = document.getElementById(uuid);

var cookieData;
try {
    cookieData = Cookie.read(cookieName);
    if (cookieData) {
        try {
            cookieData = JSON.parse(cookieData);
        } catch(e){
            cookieData = null;
        }
    }
    if (!cookieData) {
        cookieData = {};
        each(divNode.getElementsByClassName('options')[0].getElementsByTagName('input'), function(node){
            var option = node.getAttribute('data-option');
            if (option) {
                cookieData[option] = node.value;
            }
        });
        each(divNode.getElementsByClassName('options')[0].getElementsByTagName('select'), function(node){
            var option = node.getAttribute('data-option');
            if (option) {
                cookieData[option] = node.value;
            }
        });
        Cookie.write(cookieName, JSON.stringify(cookieData), 999);
    }

    var updateSelectReactions = function(){
        var selects = divNode.getElementsByClassName('options')[0].getElementsByTagName('select');
        each(selects, function(select){
            select.classList.remove("positive", "negative");
            switch (select.value) {
                case "0":
                    select.classList.add("negative");
                    break;
                case "1":
                    select.classList.add("positive");
                    break;
            }
        });
    };
    var updateOutput = function(){
        var output = divNode.getElementsByClassName('output')[0];
        output.classList.remove(
            "isHidingParentClass",
            "isHidingInterfaces",
            "isHidingTraits",
            "isHidingConstants",
            "isHidingVariables",
            "isHidingMethods",
            "isHidingMethodParameters",
            "isHidingMethodParameterTypeHints"
        );
        if ("0" === cookieData.isShowingInterfaces) {
            output.classList.add("isHidingInterfaces");
        }
        if ("0" === cookieData.isShowingParentClass) {
            output.classList.add("isHidingParentClass");
        }
        if ("0" === cookieData.isShowingTraits) {
            output.classList.add("isHidingTraits");
        }
        if ("0" === cookieData.isShowingConstants) {
            output.classList.add("isHidingConstants");
        }
        if ("0" === cookieData.isShowingVariables) {
            output.classList.add("isHidingVariables");
        }
        if ("0" === cookieData.isShowingMethods) {
            output.classList.add("isHidingMethods");
        }
        if ("0" === cookieData.isShowingMethodParameters) {
            output.classList.add("isHidingMethodParameters");
        }
        if ("0" === cookieData.isShowingMethodParameterTypeHints) {
            output.classList.add("isHidingMethodParameterTypeHints");
        }
    };

    var inputOnchange = function(e){
        setTimeout(function(){
            var value = (e.target.value).replace('/\s+/', '');
            if ("" !== value) {
                value = parseInt(value, 10);
                if (isNaN(value) || value < 0) {
                    value = 0;
                }
            } else {
                value = 0;
            }
            cookieData[e.target.getAttribute('data-option')] = value;
            Cookie.write(cookieName, JSON.stringify(cookieData), 999);
            updateOutput();
        }, 0);
    };
    each(divNode.getElementsByClassName('options')[0].getElementsByTagName('input'), function(node){
        var option = node.getAttribute('data-option');
        var value = 0;
        if (option && undefined !== cookieData[option]) {
            value = cookieData[option];
        }
        switch (option) {
            case "collapseLevel":
                value = parseInt(value, 10);
                if (isNaN(value) || value < 0) {
                    value = 2;
                }
                break;
        }
        node.value = ""+value;
        node.onchange = inputOnchange;
        node.oninput = inputOnchange;
        node.onpaste = inputOnchange;
        node.onkeydown = inputOnchange;
    });

    var selectOnchange = function(e){
        setTimeout(function(){
            cookieData[e.target.getAttribute('data-option')] = e.target.value;
            Cookie.write(cookieName, JSON.stringify(cookieData), 999);
            updateSelectReactions();
            updateOutput();
        }, 0);
    };
    each(divNode.getElementsByClassName('options')[0].getElementsByTagName('select'), function(node){
        var option = node.getAttribute('data-option');
        var value = 0;
        if (option && undefined !== cookieData[option]) {
            value = cookieData[option];
        }
        node.value = value;
        node.onchange = selectOnchange;
        node.oninput = selectOnchange;
    });
    updateSelectReactions();
    updateOutput();
} catch (e) {
    cookieData = {};
    Cookie.write(cookieName, "", -1);
}

/*
XXX fix
var mainNodes = document.getElementsByClassName('Kafoso_Tools_Debug_Dumper');
each(mainNodes, function(main){
    var foldMarkSpan = document.createElement('span');
    foldMarkSpan.innerHTML = '&hellip;';
    foldMarkSpan.setAttribute('class', 'fold-marker');
    var clone;
    var collapsibleNodes = main.getElementsByClassName('collapsible');
    var collapseLevel = 2;
    /*
    var collapseLevel = Cookie.read("Kafoso_Tools_Debug_Dumper_options_collapseLevel");
    if (collapseLevel) {
        collapseLevel = parseInt(collapseLevel, 10);
    } else {
        collapseLevel = 2;
        Cookie.write("Kafoso_Tools_Debug_Dumper_options_collapseLevel", collapseLevel, 9999);
    }
    var inputNode = main.getElementsByClassName('options')[0].getElementsByClassName('collapseLevel')[0];
    inputNode.value = collapseLevel;
    var onchangeCallback = function(){
        setTimeout(function(){
            var newCollapseLevel = parseInt(inputNode.value, 10);
            Cookie.write("Kafoso_Tools_Debug_Dumper_options_collapseLevel", newCollapseLevel, 9999);
        }, 0);
    };
    inputNode.onkeydown = onchangeCallback;
    inputNode.onpaste = onchangeCallback;
    inputNode.onchange = onchangeCallback;
    * /
    each(collapsibleNodes, function(collapsibleNode){
        if (collapsibleNode.parentNode) {
            clone = foldMarkSpan.cloneNode(true);
            collapsibleNode.parentNode.insertBefore(clone, collapsibleNode);
            if (collapseLevel <= 0 || getCountOfAllExpandedParents(collapsibleNode) > collapseLevel) {
                collapsibleNode.setAttribute('class', 'collapsible collapsed');
            } else {
                clone.setAttribute("class", "fold-marker isHidden");
            }
            clone.onclick = function(){
                var classes = this.nextSibling.getAttribute('class');
                if (classes && classes.length && classes.match(/(^|\s)expanded(\s|$)/)) {
                    this.nextSibling.setAttribute("class", "collapsible collapsed");
                    this.setAttribute("class", "fold-marker");
                } else {
                    this.nextSibling.setAttribute("class", "collapsible expanded");
                    this.setAttribute("class", "fold-marker isHidden");
                }
            };
        }
    });
});
*/
divNode.getElementsByClassName('optionsButton')[0].onclick = function(){
    var options = this.parentNode.getElementsByClassName('options')[0];
    if (null === options.offsetParent) {
        cookieData["areOptionsShown"] = "1";
        options.setAttribute('class', 'options isShown');
    } else {
        cookieData["areOptionsShown"] = "0";
        options.setAttribute('class', 'options');
    }
    Cookie.write(cookieName, JSON.stringify(cookieData), 999);
};
