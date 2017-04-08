$(document).ready(function() {
    var cb = {
        onscroll: function() {
            $('#mainbox').masonry({
                itemSelector: '.entry',
                columnWidth: 50
            });
            window.removeEventListener('scroll', cb.onscroll, false);
            setTimeout(function() {
                window.addEventListener('scroll', cb.onscroll, false);
            }, 1000)
        }
    };
    window.addEventListener('scroll', cb.onscroll, false);
})