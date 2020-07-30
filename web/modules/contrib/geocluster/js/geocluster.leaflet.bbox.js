(function ($) {
  'use strict';

  // Replace the default options for our use case.
  Drupal.leafletBBox.geoJSONOptions = {

    pointToLayer: function (featureData, latlng) {
      var lMarker;

      if (featureData.properties.geocluster_count > 1) {
        var number = featureData.properties.geocluster_count;

        var c = ' marker-cluster-';
        if (number < 10) {
          c += 'small';
        }
        else if (number < 100) {
          c += 'medium';
        }
        else if (number < 1000) {
          c += 'large';
        }
        else if (number < 10000) {
          c += 'huge';
        }
        else {
          c += 'giant';
        }
        var icon = new L.DivIcon({html: '<div><span>' + number + '</span></div>', className: 'marker-cluster' + c, iconSize: new L.Point(40, 40), iconAnchor: [20, 20]});

        lMarker = new L.Marker(latlng, {icon: icon});
      }
      else {
        let title = '';
        if (featureData.properties.label) {
          title = featureData.properties.label;
        }
        lMarker = new L.Marker(latlng, {title: title});
      }
      return lMarker;
    },

    onEachFeature: function (featureData, layer) {
      if (featureData.properties.geocluster_count > 1) {
        layer.on('click', function (e) {
          Drupal.leafletBBox.geoJSONOptions.clickOnClustered(e, featureData, layer);
        });
      }
      else {
        if (featureData.properties && featureData.properties.popup) {
          layer.bindPopup(featureData.properties.popup);
        }
      }
    },

    clickOnClustered: function (e, featureData, layer) {
      var map = layer._map;

      // Close any other opened popup.
      if (map._popup) {
        map._popup._source.closePopup();
      }

      map.setView(layer.getLatLng(), map._zoom + 1);
    }

  };

})(jQuery);
