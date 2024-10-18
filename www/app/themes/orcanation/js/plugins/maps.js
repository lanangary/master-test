/**
 * Google Maps jQuery Plugin
 *
 * Developed by Chris Manganaro <chrism@juicebox.com.au>
 * https://www.juicebox.com.au/
 */

/**
 * Re-enable if using SnazzyInfoWindow
import SnazzyInfoWindow from 'snazzy-info-window';
 */

(function($) {
    $.fn.googleMap = function(options) {
        options = options || {};

        var defaults = {
                markerOptions: {
                    title: 'Get Directions',
                    icon: {
                        path:
                            'M23,0C10.4,0,0.1,10.4,0,23.2c0.1,4.4,1.3,8.6,3.7,12.3l17.1,24c0.6,1,1.4,1.6,2.3,1.6s1.7-0.6,2.3-1.6 l17.1-24c2.3-3.7,3.6-7.9,3.7-12.3C46,10.4,35.6,0,23,0L23,0z M23,13.8c5,0,9.2,4.2,9.2,9.2S28,32.2,23,32.2S13.8,28,13.8,23 S17.9,13.8,23,13.8L23,13.8z',
                        fillColor: '#000000',
                        fillOpacity: 1,
                        strokeWeight: 0,
                        scale: 0.8
                    }
                },
                mapOptions: {
                    zoom: 14,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    zoomControl: true,
                    mapTypeControl: false,
                    backgroundColor: '#ffffff',
                    scrollwheel: false,
                    draggable: true
                },
                callbacks: {
                    onError: function(el, data) {
                        console.log(
                            'There has been a error initialising this map, please check the data and try again.',
                            el,
                            data
                        );

                        $(el).hide();
                    },
                    afterBuild: $.noop
                }
            },
            maps = [],
            markers = [];

        var markerOpts = $.extend(defaults.markerOptions, options.markerOptions);
        var mapOpts = $.extend(defaults.mapOptions, options.mapOptions);
        var callbacks = $.extend(defaults.callbacks, options.callbacks);

        //Sets HTML5 maps
        google.maps.visualRefresh = true;

        function addClickEventToMarker(marker, link) {
            google.maps.event.addListener(marker, 'click', function() {
                window.open(link, '_blank');
            });
        }

        return this.each(function() {
            var _self = this,
                $this = $(_self),
                loc;

            /* 
            data is expected to be an array as follows: {address: "<address>", lat: "<latitude>", lng: "<longitude>"} 
            lat and lng will be used first, or the lat/lng will be geocoded from the address if they're not defined
            */
            var data = $this.data('map');

            //Create map object
            var map = new google.maps.Map(_self, mapOpts);

            // Assign map to marker
            markerOpts.map = map;

            /**
             * Create location object.
             * Check if we have lat and lng, then try to geocode address
             */
            if (data.lat && data.lng) {
                loc = new google.maps.LatLng(data.lat, data.lng);
            } else if (data.address) {
                //Create geocoder object
                var geocoder = new google.maps.Geocoder();

                geocoder.geocode(
                    {
                        address: data.address
                    },
                    function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            map.setCenter(results[0].geometry.location);

                            markerOpts.position = results[0].geometry.location;

                            if (markerOptions.icon !== undefined) {
                                markerOptions.icon.anchor = results[0].geometry.location;
                            }
                        } else {
                            return callbacks.onError.call(_self, _self, data);
                        }
                    }
                );
            } else {
                return callbacks.onError.call(_self, _self, data);
            }

            map.setCenter(loc);

            markerOpts.position = loc;

            var marker = new google.maps.Marker(markerOpts);

            /**
             * Snazzy Info Window can be defined like so
             *  -- var infowindow = new SnazzyInfoWindow({ marker: marker, content: contentString });
             */

            /**
             * Add events for the map
             */
            // On click of the marker, open directions in a new window
            addClickEventToMarker(
                marker,
                'https://maps.google.com?saddr=Current+Location&daddr=' +
                    encodeURIComponent(data.address)
            );

            // Set new center point on map as window resizes
            google.maps.event.addDomListener(window, 'resize', function() {
                var center = map.getCenter();
                google.maps.event.trigger(map, 'resize');
                map.setCenter(center);
            });

            callbacks.afterBuild.call(_self, map, marker);

            maps.push(map);
            markers.push(marker);
        });
    };
})(jQuery);
