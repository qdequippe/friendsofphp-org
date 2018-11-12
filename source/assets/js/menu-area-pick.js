// inspired by https://github.com/JanMikes/tomasvotruba.cz/blob/be9da66c3402adfe7928c3798ab1ccd6527f92cd/source/assets/js/checklist.js

$(function() {
    var showRowsInBounds = function(bounds) {
        $("tr.meetup").each(function () {
            var meetupLatLng = L.latLng($(this).data('latitude'), $(this).data('longitude'));
            if (bounds.contains(meetupLatLng)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        // see https://stackoverflow.com/a/20064911/1348344
        var visibleMeetups = $("table#table-meetups tr:visible").length - 2;

        if (visibleMeetups < 1) {
            $("#block-zoomout").show();
        } else {
            $("#block-zoomout").hide();
        }
    };

    // show relevant meetups when map moves
    map.on('moveend', function() {
        showRowsInBounds(map.getBounds());
    });

    var pastBounds = window.localStorage.getItem('bounds');
    if (pastBounds) {
        showRowsInBounds(restoreBoundFromString(pastBounds));
    }
});
