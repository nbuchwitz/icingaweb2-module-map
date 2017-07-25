(function(Icinga) {
    var Map = function(module) {
        this.module = module;
        this.idCache = {};
        this.initialize();
        this.timer;
        this.module.icinga.logger.debug('Map module loaded');
    };

    var map;
    var layers;
    var markers;

    Map.prototype = {
        initialize: function()
        {
            this.module.on('rendered', this.onRenderedContainer);
            this.module.icinga.logger.debug('Map module initialized');
        },

        registerTimer: function () {
            this.timer = this.module.icinga.timer.register(
                this.updateMapData,
                this,
                300000
            );
            return this;
        },

        updateMapData: function () {
            xhr = new XMLHttpRequest();
            xhr.open('GET', 'map/mapdata', true);
            xhr.onreadystatechange = function(e) {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        var result = JSON.parse(xhr.responseText);

                        markers.clearLayers();
                        map.removeLayer(markers);

                        $.each( result, function( id, data ) {
                            var info = '<table>';
                            $.each( data['nodes'], function( hostname, node ) {
                                var services = node['services'].length
                                info += '<tr><th colspan="2">'+hostname+'</th></tr>';
                                info += '<tr><td>Services:</td><td>'+services+'</td>';
                            });

                            info += '</table>';

                            var markerIcon = L.icon({
                                iconUrl: 'img/map/marker-icon.png',
                                shadowUrl: 'img/map/marker-shadow.png',}
                            );

                            var marker = L.marker(data['coordinates'], {icon: markerIcon}).addTo(markers);
                            marker.bindPopup(info);
                        });

                        markers.addTo(map);
                    }
                }
            };
            xhr.send(null);
        },

        onRenderedContainer: function(event) {
            markers = new L.MarkerClusterGroup({
               // iconCreateFunction: function(cluster) {
               //     return L.divIcon({ html: '<b>' + cluster.getChildCount() + '</b>' });
               // }
            });
            // TODO: move coordinates to configuration
            map = L.map('map').setView([52.50146, 13.37096], 6);

            map.on('click', function(e) {
                console.log("clicked on "+e.latlng);
            });

            var osm = L.tileLayer( 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                subdomains: ['a','b','c'],
                maxZoom: 17,
                minZoom: 5,
            });

            osm.addTo(map);

            this.updateMapData();
            this.registerTimer();
        },
    };

    Icinga.availableModules.map = Map;

}(Icinga));
