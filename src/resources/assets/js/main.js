jQuery.fn.pop = [].pop;
jQuery.fn.shift = [].shift;
const archived_text = 'View archived events';
const event_text = 'View events';
const archive_selected_text = 'Archive selected events';
const delete_selected_text = 'Delete selected events';

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
    $('.timeline-hover-container').fadeOut(function(){
        $(this).remove();
    });
    $('.slider-rangeHighlight').removeClass('animating').fadeIn(function(){
        $(this).removeClass('animating');
    });
    $('.slider-rangeHighlight.hovered').removeClass('hovered');
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

    if (scrollWidth - width <= scrollLeft){
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

// Returns false if does not match tag in filter
function noMatchInFilter(tag){
    no_match = true;
    $.each($('.filtering-tags tag'), function(){
        var filter_text = $(this).text();
        var filter_data = $(this).data('filter');
        var comp_text = tag.text();
        var comp_data = tag.data('filter');
        if(filter_text == comp_text && filter_data == comp_data){
            no_match = false;
            return false;
        }
    });
    return no_match;
}

// Search for tags from list, also include 'keywords' that pull tag groups all at once
function searchTags(search){ 
    $('.searchable-tags li').hide();
    if(search == 'tags' || search == 'tag'){
        $('.searchable-tags li').show();
    }else if(search == 'users' || search == 'user'){
        $('.searchable-tags tag[data-filter="user_id"]').parent().show();
    }else if(search == 'events' || search == 'event' || search == 'activities' || search == 'activity'){
        $('.searchable-tags tag[data-filter="activity"]').parent().show();
    }else if(search == 'methods' || search == 'method' || search == 'requests' || search == 'request'){
        $('.searchable-tags tag[data-filter="method"]').parent().show();
    }else{
        $.each($('.searchable-tags tag'), function(){
            if($(this).text().toLowerCase().indexOf(search) === -1){
                $(this).parent().hide();
            }else{
                $(this).parent().show();
            }
        });
    }
}

function updateSelectedEvents(){
    var selected = $('table.events tbody tr.selected').length;
    $('.events-selected').text(selected);
}

function getLeft(element){
    return element.position().left / element.parent().width() * 100;
}

// Init document
$(document).ready(function(){
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).ajaxError(function(event){
        console.log('Error making AJAX request. If you believe this is a bug please open an issue at: https://github.com/hvlucas/laravel-logger/issues/new');
    });

    // Events table data tables config
    first_table = $('.events').first();
    dataTables = $('.events').DataTable({
        responsive: true,
        searching: true,
        paging: true,
        processing: true,
        info: true,
        serverSide: true,
        dom: '<"processing"r>t<"table-information"i><"pagination"p>',
        ajax: {
            url: '/events-ajax-helpers/list',
            method: 'GET',
            data: function(config, datatable) {
                var table = $(datatable.nTable);
                var model = table.data('model');

                config.model = model;
                config.length = $('#show').val();
                config.archived = $('#search').attr('data-archive');

                tag_data = [];
                var tags = $('.filtering-tags tag');
                $.each(tags, function(){
                    tag_data.push({'filter': $(this).data('filter'), 'value': $(this).text()});
                });
                config.tags = tag_data;
            }
        },
        language: {
            info: 'Showing _TOTAL_  Events',
            infoEmpty: 'Showing 0 Events',
            zeroRecords: 'These aren\'t the droids you are looking for &#x2639;',
            infoFiltered: '(showing _MAX_ filtered)',
            processing: '<i class="far fa-cog fa-spin fa-fw" aria-hidden="true"></i>',
            paginate: {
                previous: "«",
                next: "»",
            }
        },
        order: [[6, 'desc']],
        columns: [
            { 'data' : 'model_id_link' },
            { 'data' : 'event_tag' },
            { 'data' : 'auth_user_tag' },
            { 'data' : 'ip_address_link' },
            { 'data' : 'user_agent_icons', 'orderable': false },
            { 'data' : 'request' },
            { 'data' : 'when' },
        ],
        pageLength: 50,
        drawCallback: function(settings){
            $('#search').attr('data-export', 0);
            json = settings.json;
            tags = json.tags;

            // reset selection dropdown menu
            $('.select-all').find('.select-text').text('Select');
            $('.deselect-all').find('.select-text').text('Select');
            $('.deselect-all').addClass('select-all').removeClass('deselect-all');
            updateSelectedEvents();

            // set records filtered total to nav_tab item
            var total = '('+json.recordsFiltered+')';
            $('.visible-events').text(json.recordsTotal);
            var model = $(settings.nTable).data('parsed-model');
            $('a[data-model="'+model+'"] .event-count').text(total);

            // if it's the first time loading the tables 
            // then ignore this callback unless it's the first table of the list
            if(!first_table.is($(settings.nTable)) && settings.iDraw == 1){
                return;
            }

            //empty search tags and update from server response
            $('.searchable-tags').empty();
            $.each(tags, function(){
                tag = ""+this;
                if(noMatchInFilter($(tag))){
                    $('.searchable-tags').append(this);
                }
            });
            $('.searchable-tags').find('tag').wrap('<li class="search"></li>');
            if($('#search').val().length > 0){
                searchTags($('#search').val());
            }
        }
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

    
    // Clear interval for slider scroll
    $(document).mouseup(function(){
        clearInterval(animation_interval);
        $('tbody').removeClass('dragging erasing');
    });

    // Expand slider highlighted events
    event_timeout = null
    $(document).on({
        mouseenter: function(){
            var points = $('.slider-rangeHighlight');
            if(points.length > 1 && $('.timeline-hover-container').length == 0){
                var cur_point = $(this);
                var times = 1;
                clearTimeout(event_timeout);
                $(this).addClass('hovered');
                event_timeout = setTimeout(function(){
                    $(this).css('z-index', '1');
                    hit_first_collision = false;
                    max_left = 0;
                    min_left = 999;
                    $.each(points, function(){
                        var collided = collision($(cur_point), $(this));
                        $(this).addClass('animating');
                        if(collided){
                            if(!hit_first_collision){
                                var div = $('<div>', { 'class': 'timeline-hover-container' });
                                $(this).before(div);
                            }
                            hit_first_collision = true;

                            var current_left = getLeft($(this));
                            if(current_left > max_left){
                                max_left = current_left;
                            }
                            if(current_left < min_left){
                                min_left = current_left;
                            }

                            var top_px = 25*times;
                            ++times;
                            left = getLeft($(this));

                            var clone = $(this).clone();
                            clone.animate({
                                top: top_px+'px',
                                width: '150px',
                                height: '20px',
                            });
                            // if is this point is the original hovered, then add some special styling to pop
                            if($(this).is(cur_point)){
                                clone.css({border: 'dashed 1px #525151', filter: 'contrast(125%)', color: '#353535'});
                            }else{
                                $(this).hide();
                            }

                            $('.timeline-hover-container').append(clone);
                            var height = top_px+45;
                            $('.timeline-hover-container').css({'z-index': 1, 'right': 100 - left + '%' });
                            $('.timeline-hover-container').css('height', height+'px');
                        }
                    });

                    max = $('input.history-slider').data('slider-max');
                    min = $('input.history-slider').data('slider-min');
                    difference = max-min;
                    minimizer = $('input.history-slider').data('minimizer');
                    $.each($('.timeline-hover-container').find('div'), function(){
                        $(this).animate({
                            left: 10+'px'
                        });
                        $(this).css('z-index', 2);

                        var timestamp = (min + ((getLeft($(this))/100) * difference)) * minimizer * 1000;
                        var date = new Date(timestamp);
                        $(this).text(date.toLocaleDateString('en-US', {month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit'}));
                    });
                    $('.timeline-hover-container').mouseleave(function(){
                        clearSliderAnimations();
                    });
                }, 500);
            }
        }
    }, '.slider-rangeHighlight:not(.animating)');

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
                    $('.modal').hide();
                    $('body').append(data);
                    var new_modal = $('#event_'+sync_event_id).parents('.modal');
                    new_modal.modal();
                }
            }
        });
    });

    // Trigger sliders and scroll event when modal finishes loading
    scroll_timeout = null;
    animation_interval = null;
    $(document).on('shown.bs.modal', '.modal.history-modal', function(){
        $('#history-slider').slider();
        $('#scale-slider').slider();
        fireScrollEvent(); 
    });

    // Remove modal from dom once it closes
    $(document).on('hidden.bs.modal', '.modal.history-modal', function(){
        $(this).remove();
    });

    // Remove sync modal and show parent modal 
    $(document).on('hidden.bs.modal', '.modal.sync-modal', function(){
        $(this).remove();
        $('.modal.history-modal').show();
    });

    // Hide confirmation modal; remove confirmation classes 
    $(document).on('click', '.confirmation-modal #confirm, .confirmation-modal #cancel', function(){
        $('.confirmation-modal').modal('hide');
        $('.confirmation-modal').removeClass('archive');
    });

    // Submit sync form; display alert on request return
    $(document).on('click', 'form#sync-form input[type="submit"]', function(event){
        event.preventDefault();
        var form = $(this).parents('form');
        var sync_data = form.serialize();
        $.ajax({
            url: '/events-ajax-helpers/model-history/sync',
            method: 'POST',
            data: { sync_data: sync_data },
            success: function(data){
                data = $(data);
                form.parents('.modal').modal('hide');
                $('.history-scale').before(data);
                $('.modal').modal('handleUpdate');
                setTimeout(function(){
                    data.fadeOut(function(){
                        $(this).remove();
                        $('.modal').modal('handleUpdate');
                    });
                }, 3000);
            }
        });
    });

    // Searching through input
    search_timeout = null;
    $(document).on('change, keydown, keyup', '#search', function(){
        clearTimeout(search_timeout);
        input = $(this);
        search = input.val();
        if(search.length > 0){
            $('.searchable-tags').css('display', 'inline-block');
        }else{
            $('.searchable-tags').hide();
        }
        model = input.attr('data-model');
        if(model){
            search_timeout = setTimeout(function(){
                $('table[data-parsed-model="'+model+'"]').DataTable().search(input.val()).draw();
            }, 1000);
        }
        searchTags(search);
    });

    // Filtering through tags below search input
    $(document).on('click', 'li.search', function(event){
        event.stopPropagation();
        $('#search').val('');
        $(this).removeClass('search').addClass('filter');
        $('.filtering-tags').append($(this));
        $('a#clear-filter').show();
        //set val to empty and trigger change to timer resets
        $('.searchable-tags').hide();
        var model = $('#search').attr('data-model');
        $('table[data-parsed-model="'+model+'"]').DataTable().search('').draw();
        return false;
    });

    // Removing filter tags 
    $(document).on('click', 'li.filter', function(){
        $(this).removeClass('filter').addClass('search');
        $('.searchable-tags').append($(this));
        if($('.filtering-tags li').length == 0){
            $('a#clear-filter').hide();
        }
        var model = $('#search').attr('data-model');
        $('table[data-parsed-model="'+model+'"]').DataTable().draw();
    });

    // Update table based on how many they selected
    $(document).on('change', '#show', function(){
        var model = $('#search').attr('data-model');
        $('table[data-parsed-model="'+model+'"]').DataTable().draw();
    });

    // Filter by tags when clicking on the table itself
    $(document).on('click', 'table.events td tag', function(){
        if(noMatchInFilter($(this))){
            var clone = $(this).clone();
            $('.filtering-tags').append(clone);
            $('a#clear-filter').show();
            clone.wrap('<li class="filter"></li>');
        }
        var model = $('#search').attr('data-model');
        $('table[data-parsed-model="'+model+'"]').DataTable().draw();
    });

    // Redraw table once a nav_tab is clicked
    $(document).on('click', 'a.nav-link', function(event){ 
        event.stopPropagation();
        var target = $(event.target);
        if(target.is($('i.nav-item-menu')) || target.is($('ul.nav-item-menu-dropdown, ul.nav-item-menu-dropdown > *'))){
            return;
        }
        $('ul.nav-item-menu-dropdown').hide();
        var model = $(this).data('model');
        // reset input#search data model attribute; keep archive
        $('#search').attr('data-model', model);
        if(!$(this).find('.archive-icon').is(':visible')){
            $('#search').attr('data-archive', 0);
            $('.archive-text').text(archived_text);
        }else{
            $('#search').attr('data-archive', 1);
            $('.archive-text').text(event_text);
        }

        $('table[data-parsed-model="'+model+'"]').DataTable().draw();
        return false;
    });

    // Remove all tags filtering table
    $(document).on('click', 'a#clear-filter', function(){
        $('.searchable-tags').append($('.filtering-tags li'));
        $('a#clear-filter').hide();
        var model = $('#search').attr('data-model');
        $('table[data-parsed-model="'+model+'"]').DataTable().draw();
    });

    // Toggle menu dropdown
    $(document).on('click', 'i.nav-item-menu', function(){
        $(this).next('ul.nav-item-menu-dropdown').slideToggle('fast');
    });

    // Global click events
    $(document).on('click', function(event){
        var target = $(event.target);
        // Hide menu on click that is not the menu
        if(!target.is('ul.nav-item-menu-dropdown, ul.nav-item-menu-dropdown > *, i.nav-item-menu')){
            $('ul.nav-item-menu-dropdown').slideUp('fast');
        }
    });

    // Select table row
    /*
    $(document).on('click', 'table.events tbody tr', function(event){
        var target = $(event.target);
        if(!target.is('tag, a')){
            $(this).toggleClass('selected');
        }
    });
    */

    $(document).on('mousedown', 'table.events tbody tr', function(){
        var tbody = $(this).parents('tbody');
        tbody.addClass('dragging');
        if($(this).hasClass('selected')){
            tbody.addClass('erasing');
            $(this).removeClass('selected');
        }else{
            $(this).addClass('selected');
        }
    });

    $(document).on('mouseover', 'table.events tbody tr', function(){
        if($('.dragging').length > 0){
            if($('.dragging').is('.erasing')){
                $(this).removeClass('selected');
            }else{
                $(this).addClass('selected');
            }
        }
        updateSelectedEvents();
    });


    // Select/Deselect all rows
    $(document).on('click', '.select-all, .deselect-all', function(){
        if($(this).is('.select-all')){
            var add_class = 'deselect-all';
            var remove_class = 'select-all';
            var select_text = 'Deselect';
        }else{
            var add_class = 'select-all';
            var remove_class = 'deselect-all';
            var select_text = 'Select';
        }

        var model = $('#search').attr('data-model');

        var rows = $('table[data-parsed-model="'+model+'"] tbody tr');
        // empty data set
        if(rows.length == 1 && rows.find('td').length == 1){
            return;
        }

        if($(this).is('.select-all')){
            rows.addClass('selected');
        }else{
            rows.removeClass('selected');
        }
        $(this).addClass(add_class).removeClass(remove_class);
        $(this).find('.select-text').text(select_text);
        updateSelectedEvents();
    });

    // Open Modal for archive of events
    $(document).on('click', '.archive-selected', function(event){
        event.stopPropagation();
        var model = $('#search').attr('data-model');
        var selected_rows = $('table[data-parsed-model="'+model+'"]').find('tr.selected');
        var links = selected_rows.find('td:first-child a');
        var ids = [];
        $.each(links, function(){
            ids.push($(this).data('event-id'));
        });
        if(ids.length > 0){
            if($(this).is('.delete-selected')){
                var modal_text = 'You are about to PERMANENTLY DELETE ' + ids.length + ' events. Are you sure about this?';
                var modal_class = 'delete';
            }else{
                var modal_text = 'You are about to archive ' + ids.length + ' events. Are you sure about this?';
                var modal_class = 'archive';
            }
            $('.confirmation-modal').addClass(modal_class).find('#confirmation-text').text(modal_text);
            $('.confirmation-modal').modal();
            $('.confirmation-modal').modal('handleUpdate');
        }
        return false;
    });

    // Confirm archive of events
    $(document).on('click', '.confirmation-modal.archive #confirm, .confirmation-modal.delete #confirm', function(){
        var model = $('#search').attr('data-model');
        var selected_rows = $('table[data-parsed-model="'+model+'"]').find('tr.selected');
        var links = selected_rows.find('td:first-child a');
        var ids = [];
        $.each(links, function(){
            ids.push($(this).data('event-id'));
        });
    
        var modal_parent = $(this).parents('.confirmation-modal');
        if(modal_parent.is('.delete')){
            var url = '/events-ajax-helpers/delete-events';
        }else{
            var url = '/events-ajax-helpers/archive-events';
        }

        if(ids.length > 0){
            $.ajax({
                url: url,
                method: 'DELETE',
                data: { event_ids: ids },
                success: function(data) {
                    // prepend alert
                    if(data != 1){
                        var data = $(data);
                        $('.events-container div.col-lg').prepend(data);
                        setTimeout(function(){
                            data.fadeOut(function(){
                                $(this).remove();
                            });
                        }, 3000);
                    }
                    // refetch table
                    var model = $('#search').attr('data-model');
                    $('table[data-parsed-model="'+model+'"]').DataTable().draw();
                }
            });
        }
    });

    // Set input#search archived data attribute and refetch datatable
    $(document).on('click', '.view-archived, .view-events', function(event){
        event.stopPropagation();
        var model = $('#search').attr('data-model');
        var archive_icon = $('a[data-model="'+model+'"] .archive-icon');
        if($(this).is('.view-archived')){
            var data_archive = 1;
            var add_class = 'view-events';
            var remove_class = 'view-archived';
            var menu_text = event_text;
            archive_icon.show();
            $('a[data-model="'+model+'"] .archive-selected-text').text(delete_selected_text);
            $('a[data-model="'+model+'"] .archive-selected').addClass('delete-selected');
        }else{
            var data_archive = 0;
            var add_class = 'view-archived';
            var remove_class = 'view-events';
            var menu_text = archived_text;
            archive_icon.hide();
            $('a[data-model="'+model+'"] .archive-selected-text').text(archive_selected_text);
            $('a[data-model="'+model+'"] .archive-selected').removeClass('delete-selected');
        }
        $('#search').attr('data-archive', data_archive);
        $(this).addClass(add_class).removeClass(remove_class);
        $('a[data-model="'+model+'"] .archive-text').text(menu_text);
        $('table[data-parsed-model="'+model+'"]').DataTable().draw();
        // without a return, `event.stopPropagation();` keeps adding a link anchor to the URL; STOP THAT!
        return false;
    });

    $(document).on('click', '.export-events', function(event){
        event.preventDefault();
        var model = $('#search').attr('data-model');
        var params = $('table[data-parsed-model="'+model+'"]').DataTable().ajax.params();
        window.location.href = '/events-ajax-helpers/list? ' + $.param(params) + '&export=1';
    });
});
