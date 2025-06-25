$(document).ready(function() {
    $('.fa-shopping-cart, .fa-heart, .fa-balance-scale').hover(
        function() { $(this).addClass('text-primary'); },
        function() { $(this).removeClass('text-primary'); }
    );
});