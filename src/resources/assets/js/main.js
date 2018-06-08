jQuery.fn.pop = [].pop;
jQuery.fn.shift = [].shift;

// Returns unique values of an array
function uniqueValues(value, index, self){
    return self.indexOf(value) === index;
}

// Hide nav
function hideNavItem(nav_item){
    nav_item.hide();
    nav_item.addClass('toggle-nav');
}

// Show nav
function showNavItem(nav_item){
    nav_item.show();
}

// Expand nav tabs
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

// From https://stackoverflow.com/a/5541252/8479313
// Detects if element is colliding with another element
function collision(element, comparison) {
    var x1 = element.offset().left;
    var y1 = element.offset().top;
    var h1 = element.outerHeight(true);
    var w1 = element.outerWidth(true);
    var b1 = y1 + h1;
    var r1 = x1 + w1;
    var x2 = comparison.offset().left;
    var y2 = comparison.offset().top;
    var h2 = comparison.outerHeight(true);
    var w2 = comparison.outerWidth(true);
    var b2 = y2 + h2;
    var r2 = x2 + w2;

    if(b1 < y2 || y1 > b2 || r1 < x2 || x1 > r2){
        return false;
    }
    return true;
}

// Return slider Highlighted areas to original positions
function clearSliderAnimations(){
    $.each($('.slider-rangeHighlight'), function(){
        $(this).animate({
            top: 0,
        }, 200, function(){
            $(this).removeClass('animating');
            $(this).css('z-index', 'unset');
        });
    });
}

// Scroll modal left/right
function scrollModal(direction){
    if(direction == 'right'){
        direction = '+';
    }else{
        direction = '-';
    }
    $('table.history').animate( { scrollLeft: direction+'=300' }, 200);

    var table = $('table.history')[0];

    if(typeof table == 'undefined'){
        return;
    }

    var scrollWidth = table.scrollWidth;
    var width = $('table.history').outerWidth();
    var scrollLeft = $('table.history').scrollLeft();

    if (scrollWidth - width === scrollLeft){
        $('.modal-scroll[data-direction="right"]').hide();
    }else{
        $('.modal-scroll[data-direction="right"]').show();
    }

    if(scrollLeft == 0){
        $('.modal-scroll[data-direction="left"]').hide();
    }else{
        $('.modal-scroll[data-direction="left"]').show();
    }
}

// Trigger scroll event for modal
function fireScrollEvent(){
    $('.modal-scroll').mousedown(function(event){
        if(event.which == 1){
            var direction = $(this).data('direction');
            scrollModal(direction);
            animation_interval = setInterval(function(){
                scrollModal(direction);
            }, 200);
        }
    });
}

$(document).ready(function(){

    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).ajaxError(function(event){
        console.log('Error making AJAX request. If you believe this is a bug please open an issue at: https://github.com/hvlucas/laravel-logger/issues/new');
    });

    // Events table data tables config
    $('.events').DataTable({
        responsive: true,
        searching: false,
        sorting: false,
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

    // Toggle nav tabs
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

    // Get model history and trigger modal to show
    $(document).on('click', '.open-model-history', function(){
        var event_id = $(this).data('event-id');
        if(typeof event_id != "undefined"){
            $.ajax({
                url: '/events-ajax-helpers/model-history',
                method: 'POST',
                data: { event_id: event_id },
                success: function(data) {
                    if(data !== -1){
                        $(data).modal();
                    }
                }
            });
        }
    });

    // Remove modal from dom once it closes
    $(document).on('hidden.bs.modal', '.modal', function(){
        $(this).remove();
    });
    
    // Trigger sliders and scroll event when modal finishes loading
    scroll_timeout = null;
    animation_interval = null;
    $(document).on('shown.bs.modal', '.modal', function(){
        $('#history-slider').slider();
        $('#scale-slider').slider();
        fireScrollEvent(); 
    });

    // Clear interval for slider scroll
    $(document).mouseup(function(){
        clearInterval(animation_interval);
    });

    // Expand slider highlighted events
    event_timeout = null
    $(document).on({
        mouseenter: function(){
            var points = $('.slider-rangeHighlight');
            if(points.length > 1){
                var cur_point = $(this);
                var times = 1;
                clearTimeout(event_timeout);
                $(this).addClass('hovered');
                event_timeout = setTimeout(function(){
                    $(this).css('z-index', '1');
                    $.each(points, function(){
                        var collided = collision($(cur_point), $(this));
                        $(this).addClass('animating');
                        if(collided && !$(this).is(cur_point)){
                            var top_px = 5*times;
                            var right_px = 2*times;
                            ++times;
                            $(this).css('z-index', 2);
                            $(this).animate({
                                top: top_px+'px',
                            }, 200);
                        }
                    });
                }, 500);
            }
        },
        mouseleave: function() {
            clearSliderAnimations();
        }
    }, '.slider-rangeHighlight:not(.animating)');

    // Clear animations for slider highlighted points every 5 seconds
    window.setInterval(function(){
        if($('.slider-rangeHighlight.hovered').length > 0){
            clearSliderAnimations();
            $('.slider-rangeHighlight.hovered').removeClass('hovered');
        }
    }, 5000);
    
    // Set point in timeline of model history
    history_timeout = null;
    $(document).on('change', 'input#history-slider', function(){
        clearTimeout(history_timeout);
        var event_point = $(this).attr('value');
        var event_id = $(this).data('event-id');
        var minimizer = $(this).data('minimizer');
        history_timeout = setTimeout(function(){
            $.ajax({
                url: '/events-ajax-helpers/model-history/filter',
                method: 'GET',
                data: { event_id: event_id, event_point: event_point, minimizer: minimizer },
                success: function(data) {
                    if(data !== -1){
                        $('.table-container').replaceWith(data);
                        $('.modal').modal('handleUpdate');
                        fireScrollEvent();
                        scrollModal('left');
                    }
                }
            });
        }, 500);
    });

    // Scale timeline of model history
    scale_timeout = null;
    $(document).on('change', 'input#scale-slider', function(){
        var scale_filter = $(this).attr('value');
        var event_id = $(this).data('event-id');
        var minimizer = $(this).data('minimizer');
        clearTimeout(scale_timeout);
        scale_timeout = setTimeout(function(){
            $.ajax({
                url: '/events-ajax-helpers/model-history/filter',
                method: 'GET',
                data: { event_id: event_id, scale_filter: scale_filter, minimizer: minimizer },
                success: function(data){
                    if(data !== -1){
                        $('.history-container').replaceWith(data);
                        $('#history-slider').slider();
                        fireScrollEvent();
                        scrollModal('left');
                    }
                }
            });
        });
    });

    // Render modal for syncing
    $(document).on('click', '.sync-model', function(){
        var sync_event_id = $(this).data('event-id');
        var model_id = $('input[data-model-id]').data('model-id');
        $.ajax({
            url: '/events-ajax-helpers/model-history/sync-form',
            method: 'GET',
            data: { model_id: model_id, sync_event_id: sync_event_id },
            success: function(data){
                if(data !== -1){
                    $('.model').append(data);
                    $('.sync-modal#'+sync_event_id).modal();
                    $('.modal').modal('handleUpdate');
                }
            }
        });
    });
});
