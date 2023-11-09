$(document).ready(function () {
  UIOWA_Geomarker.setupMap(
    "mapCanvas",
    UIOWA_Geomarker.mapType,
    UIOWA_Geomarker.data
  );
});

const UIOWA_Geomarker = {
  setupMap: function (mapId, mapType, data) {
    // initialize map
    this.map = new google.maps.Map(document.getElementById(mapId), {
      zoom: 4,
      scrollwheel: true,
      center: new google.maps.LatLng(0, 0),
      mapTypeId: mapType,
    });

    const recordCount = JSON.parse(
      data.replaceAll("&quot;", '"').replaceAll("&amp;", "&")
    ).length;
    let statusMsg = "";
    let displayedCount = "";

    // initialize markers (if records exist)
    if (recordCount > 0) {
      const boundsListener = google.maps.event.addListener(
        UIOWA_Geomarker.map,
        "bounds_changed",
        function () {
          UIOWA_Geomarker.setupDataMarkers(data, function (markerCount) {
            if (markerCount !== recordCount) {
              displayedCount = markerCount + " of " + recordCount;
            } else {
              displayedCount = markerCount;
            }

            statusMsg = "Successfully plotted " + displayedCount + " records.";

            $("#mapStatus").html(statusMsg);
          });

          google.maps.event.removeListener(boundsListener);
        }
      );
    } else {
      $("#mapStatus").html("No records with valid coordinates found.");
    }
  },
  setupDataMarkers: function (data, callback) {
    // initialize marker manager
    this.mgr = new MarkerManager(this.map);

    google.maps.event.addListener(UIOWA_Geomarker.mgr, "loaded", function () {
      const markers = [];
      const latLngBounds = new google.maps.LatLngBounds();

      $.each(
        JSON.parse(data.replaceAll("&quot;", '"').replaceAll("&amp;", "&")),
        function (index, record) {
          if (record.lat && record.lng) {
            const latLng = new google.maps.LatLng(record.lat, record.lng);
            const marker = new google.maps.Marker({
              position: latLng,
              title: record.title,
              url: record.url,
            });

            google.maps.event.addListener(marker, "click", function () {
              window.location.href = this.url;
            });

            markers.push(marker);
            latLngBounds.extend(latLng);
          }
        }
      );

      UIOWA_Geomarker.mgr.addMarkers(markers, 1);
      UIOWA_Geomarker.map.fitBounds(latLngBounds);
      UIOWA_Geomarker.mgr.refresh();

      callback(markers.length);
    });
  },
};
