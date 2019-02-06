$(document).ready(function(){
    $('.pageBlock .blockTitle').on('click', function() {
        title = $(this);
        block = title.parents('.pageBlock');        
        content = block.find('.blockContent');
        fa = title.find('.fa');
        openClass = 'fa-plus';
        closeClass = 'fa-minus';
        if (fa.hasClass(openClass)) {
            fa.removeClass(openClass);
            fa.addClass(closeClass);
        } else {
            fa.removeClass(closeClass);
            fa.addClass(openClass);
        }
        content.slideToggle(200);
    });
});