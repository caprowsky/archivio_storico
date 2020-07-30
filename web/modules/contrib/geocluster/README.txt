INTRODUCTION
------------

Server-side clustering for mapping.

By clustering data on the server-side, the load is shifted from the client
to the server which allowsdisplaying larger amounts of data in a performant way.

REQUIREMENTS
------------

* geofield (includes geophp) to store coordinates on my node


RECOMMENDED MODULES
-------------------

- views_geojson to return my view results in geojson format
- leaflet_geojson to have a block to integrate my view returned by geoJson
  & apply the bounding box strategy
- page_manager (includes panels & ctools)
  to associate my block & the leaflet map
- leaflet, to provide the map
- leaflet_views, to be able to use LeafletAjaxPopupController 
  to render the popup through ajax

INSTALLATION
------------

* Install Geocluster
* Go to admin/config/geocluster to run the script to generate geocluster
  indexes
  
CONFIGURATION
------------

When creating a new field for geocluster, use a geofield field.

Cf https://www.drupal.org/docs/contributed-modules/geocluster-d8d9/configuration-process
* Create a clustered GeoJSON Feed:
 - Create a display based on Views GeoJSON
 - Add the Geofield and exlude it from display
 - Enable Geocluster in the Format Settings of your View
 - Add Geocluster fields for aggregates of latitude, longitude, count
 - Use Geocluster lat, lon as data source in the Views GeoJSON format settings
 - Make sure that no additional fields are used for aggregation, 
  you can still use them with aggregate functions like GROUP_CONCAT
 - Make sure there isn't any sorting
 - You maybe want to save this step until everything else works: 
  set the item limit to 0 so that all your content will get clustered
  
* Create a page and display this view as the source of your block
