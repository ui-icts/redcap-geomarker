# Geomarker
The Geomarker module plots geographical coordinates from records in a REDCap project via Google Maps.

## Configuration
This module must be enabled at the project-level. Once enabled, some additional configuration is required before it will function:

* **Google Maps v3 API Key:** This module requires a valid key for the Google Maps JavaScript API. You can find more information on this here: https://developers.google.com/maps/documentation/javascript/get-api-key

* **Latitude Field:** A field in your REDCap project from which latitude coordinates will be pulled.

* **Longitude Field:** A field in your REDCap project from which longitude coordinates will be pulled.

* **Type of Map:** The style of map to render. Google Maps provides four types:
  * Roadmap - Displays the default road map view. This is the default map type.
  * Satellite - Displays Google Earth satellite images.
  * Hybrid - Displays a mixture of normal and satellite views.
  * Terrain  - Displays a physical map based on terrain information.

* **Hover Text Field (optional):** A field in your REDCap project from which the hover text for each marker will be pulled.