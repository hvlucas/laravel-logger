function uniqueValues(value, index, self){
    return self.indexOf(value) === index;
}

function hideNavItem(nav_item){
    nav_item.hide();
    nav_item.addClass('toggle-nav');
}

function showNavItem(nav_item){
    nav_item.show();
}

function toggleExpandTab(){
    return $('<li>', {
        'class': 'nav-item',
        'id': 'toggleTab'
    }).append($('<a>', {
        'class': 'nav-link',
        'href': '#',
        'text': '+'
    }));
}

$(document).ready(function(){
    $('.events').DataTable({
        searching: false,
        paging: false,
        info: false,
    });

    var items = $('.nav-item');
    var positions = [];
    var reached_end = false;
    $.each(items, function(){
        if(!reached_end){
            var top_pos = $(this).position().top;
            positions.push(top_pos);
            positions = positions.filter(uniqueValues).sort();
            if(positions.indexOf(top_pos) !== 0){
                reached_end = true;
                hideNavItem($(this));
            }
        }else{
            hideNavItem($(this));
        }
    });
    var nav_parent = $('.nav.nav-tabs');
    if(reached_end){
        nav_parent.append(toggleExpandTab());
    }

    $(document).on('click', '#toggleTab', function(){
        var items = nav_parent.find('.nav-item.toggle-nav').not('#expandTab');
        var current_text = $('#toggleTab a').text();
        if(current_text == '+'){
            new_text = '-';
            showNavItem(items);
        }else{
            new_text = '+';
            hideNavItem(items);
        }
        $('#toggleTab a').text(new_text);
    });
});

