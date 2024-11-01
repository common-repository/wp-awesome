(function() {
    "use strict";
    
    /*global jQuery*/
    /*global ajaxurl*/
    
    var activeMenu;
    
    document.addEventListener('mousedown', function(e) {
        if(activeMenu) {
            var el = e.srcElement;
            while(el != activeMenu && el != document.body) {
                el = el.parentElement;
            }
            if(el != activeMenu) {
                activeMenu.parentElement.removeChild(activeMenu);
                activeMenu = null;
            }
        }
    });
    
    document.addEventListener( "contextmenu", function(e) {
        
        if(e.altKey || e.ctrlKey || e.metaKey || e.shiftKey)
            return;
            
        var el = e.srcElement || e.target;
        contextMenuHandler( el, e );

    } );
    
    function contextMenuHandler( el, e ) {
        
        var trail = [];
        
        while( el ) {
            var offset = trail.length;
            trail.push({
                tagName: el.tagName
            });
            if(el.tagName == "INPUT" || el.tagName == "TEXTAREA" || el.tagName == "SELECT") {
                return;
            }
            
            if(el.id) {
                trail[offset].id = el.id;
            }
            
            if(el.className) {
                trail[offset].className = el.className;
            }

            if(el.role) {
                trail[offset].role = el.role;
            }
            
            if(el.action ) {
                trail[offset].action = el.action;
            }
            
            if(el.tagName == 'A' ) {
                trail[offset].href = el.href;
            }
            
            if(el.tagName == 'IMG') {
                trail[offset].src = el.src;
            }
             
            if(el.rel) {
                trail[offset].rel = el.rel;
            }
            
            if(el.alt) {
                trail[offset].alt = el.alt;
            }
                
            if(offset == 0) {
                trail[0].innerHTML = el.innerHTML;
            }
            el = el.parentElement;
        }
        e.preventDefault();
        if (activeMenu) {
            activeMenu.parentElement.removeChild(activeMenu);
            activeMenu = null;
        }
        var x = e.pageX;
        var y = e.pageY;
        
        function countItems(items) {
            var count = 0;
            for(var i in items) {
                if(items.hasOwnProperty(i)) {
                    count++;
                }
            }
            return count;
        }
        
        function buildMenu(items) {
            /**
             * If the menu has only one item, and that item has sub-items - make
             * the sub menu the main menu - and discard the main menu item.
             */
            if(countItems(items) == 1) {
                for(var i in items) {
                    if(items.hasOwnProperty(i) && items[i].hasOwnProperty("items")) {
                        return buildMenu(items[i].items);
                    }
                }
            }
            
            
            var menu = document.createElement('DIV');
            menu.className = 'wpa-context-menu';
            
            for(var i in items) {
                var item = items[i];
                var itemEl = document.createElement('DIV');
                itemEl.className = 'wpa-context-menu-item';
                var itemAEl = document.createElement('A');
                itemAEl.innerHTML = item.label;
                itemAEl.href = item.url;
                if(item.url)
                    itemEl.appendChild(itemAEl);
                menu.appendChild(itemEl);
                if(item.items && countItems(item.items)>0) {
                    itemEl.className = 'wpa-context-menu-has-children';
                    var submenu = buildMenu(item.items);
                    itemEl.appendChild(submenu);
                }
            }
            
            return menu;
        }
        console.log(trail, document.location.href);
        jQuery.post( settings.ajax_url, {
            action: 'wpa_context_menu',
            trail: trail,
            url: document.location.href
        }, function(response) {
            if(countItems(response.items) > 0) {
                var menu = buildMenu(response.items);
                menu.style.left = x + "px";
                menu.style.top = y + "px";
                activeMenu = menu;
                document.body.appendChild(menu);
                if(x > document.body.offsetWidth - menu.offsetWidth) {
                    menu.style.left = (document.body.offsetWidth - menu.offsetWidth) + "px";
                }
                console.log(menu.offsetWidth,document.body.offsetWidth);
                setTimeout(function() {
                    menu.className = menu.className + " wpa-show";
                }, 0);
            }
        } );
        
    }
    
})();