var UIOWA_Geomarker = {};

$(document).ready(function () {
    UIOWA_Geomarker.setupMap("mapCanvas", UIOWA_Geomarker.mapType, UIOWA_Geomarker.data);
});

UIOWA_Geomarker = {
    setupMap: function (mapId, mapType, data) {
        var self = this;

        // initialize map
        this.map = new google.maps.Map(document.getElementById(mapId), {
            zoom: 4,
            scrollwheel: true,
            center: new google.maps.LatLng(0, 0),
            mapTypeId: mapType
        });

        var recordCount = data.length;
        var statusMsg = '';
        var displayedCount = '';

        // initialize markers (if records exist)
        if (recordCount > 0) {
            var boundsListener = google.maps.event.addListener(self.map, 'bounds_changed', function(){
                self.setupDataMarkers(data, function(markerCount) {
                    if (markerCount !== recordCount) {
                        displayedCount = markerCount + ' of ' + recordCount;
                    }
                    else {
                        displayedCount = markerCount
                    }

                    statusMsg = 'Successfully plotted ' + displayedCount + ' records.';

                    $('#mapStatus').html(statusMsg)
                });

                google.maps.event.removeListener(boundsListener);
            });
        }
        else {
            $('#mapStatus').html('No records with valid coordinates found.');
        }

    },
    setupDataMarkers: function (data, callback) {
        var self = this;

        // initialize marker manager
        this.mgr = new MarkerManager(this.map);

        google.maps.event.addListener(self.mgr, 'loaded', function(){
            var markers = [];
            var latLngBounds = new google.maps.LatLngBounds();

            $.each(data, function(index, record) {
                if (record.lat && record.lng) {
                    var latLng = new google.maps.LatLng(record.lat, record.lng);
                    var marker = new google.maps.Marker({
                        position: latLng,
                        title: record.title,
                        url: record.url
                    });

                    google.maps.event.addListener(marker, 'click', function() {
                        window.location.href = this.url;
                    });

                    markers.push(marker);
                    latLngBounds.extend(latLng);
                }
            });

            self.mgr.addMarkers(markers, 1);
            self.map.fitBounds(latLngBounds);
            self.mgr.refresh();

            callback(markers.length);
        });
    }
};