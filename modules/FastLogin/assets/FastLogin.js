document.addEventListener('keyup', function(e) {
    if(e.keyCode == 27) {
        if (isNaN(document._consEsc))
            document._consEsc = 0;
        document._consEsc++;
    } else {
        document._consEsc = 0;
    }
    if(document._consEsc == 3) {
        document._consEsc = 0;
        e.preventDefault();
        document.location.href = "/wp-login.php?redirect_to=" + encodeURIComponent(document.location.href) + "&reauth=1";
    }
});