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
        var nextWeekVisibleMeetups = $("table#table-week tr:visible").length - 1;
        var nextMonthVisibleMeetups = $("table#table-month tr:visible").length - 1;

        console.log(nextWeekVisibleMeetups);
        console.log(nextMonthVisibleMeetups);

        if (nextWeekVisibleMeetups === 0) {
            // hide week tab
            $("#table-block-week").hide();
        } else {
            $("#table-block-week").show();
        }

        if (nextMonthVisibleMeetups === 0) {
            // hide month tab
            $("#table-block-month").hide();
        } else {
            $("#table-block-month").show();
        }

        if (nextWeekVisibleMeetups === 0 && nextMonthVisibleMeetups === 0) {
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
