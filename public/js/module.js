(function(Icinga) {
    var Map = function(module) {
        this.module = module;
        this.idCache = {};
        this.initialize();
        this.timer;
        this.module.icinga.logger.debug('Map module loaded');
    };

    var map;
    var markers;
    var hostMarkers;

    Map.prototype = {
        initialize: function()
        {
            this.module.on('rendered', this.onRenderedContainer);
            this.module.icinga.logger.debug('Map module initialized');
        },

        registerTimer: function () {
            if(this.timer) {
                this.module.icinga.timer.unregister(this.timer); 
            }
            this.timer = this.module.icinga.timer.register(
                this.updateMapData,
                this,
                10000
            );
            return this;
        },

        updateMapData: function () {
            xhr = new XMLHttpRequest();
            xhr.open('GET', 'map/data/points', true);
            xhr.onreadystatechange = function(e) {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        var result = JSON.parse(xhr.responseText);

                        // remove old markers
                        $.each( hostMarkers, function( hostname, d ) {
                            if(!result[hostname]) {
                                markers.removeLayer(d);
                                delete hostMarkers[hostname];
                            }
                        });

                        $.each( result, function( hostname, data ) {
                            var hard_state = data['host_hard_state'];
                            var icon;

                            switch(hard_state) {
                                case 0:
                                    icon = greenMarker;
                                    break;
                                case 1:
                                    icon = redMarker;
                                    break;
                                default:
                                    icon = blueMarker;
                            }

                            var service_status = {};
                            service_status[0] = "OK";
                            service_status[1] = "WARN";
                            service_status[2] = "CRIT";
                            service_status[3] = "UNKOWN";
                            service_status[99] = "PENDING";

                            var services = '<table>';
                            services += '<tr><th colspan="2">Service overview</th></tr>';
                            $.each( data['services'], function( service_display_name, service ) {
                                services += '<tr><td class="map_service_state map_service_state'
                                    + service['service_hard_state']
                                    + '">'
                                    + service_status[service['service_hard_state']]
                                    + '</td><td class="map_service_name">'
                                    + service_display_name
                                    + '</td></tr>'
                            });
                            services += '</table>';

                            var info = '<div class="map_host_detail">';
                            info += '<p><a href="monitoring/host/show?host='+hostname+'">'+hostname+'</a></p>'
                            info += services;
                            info += '</div>';

                            var marker;

                            if(hostMarkers[hostname]) {
                                marker = hostMarkers[hostname]; 
                            } else {
                                marker = L.marker(data['coordinates'], {icon: icon, title: hostname}).addTo(markers);
                                hostMarkers[hostname] = marker
                                markers.addTo(map);
                            }

                            marker.setIcon(icon);
                            marker.bindPopup(info);
                        });

                    }
                }
            };
            xhr.send(null);
        },

        onRenderedContainer: function(event) {
            // TODO: initialize once and only update
            markers = new L.MarkerClusterGroup();
            hostMarkers = {};

            map = L.map('map').setView([map_default_lat, map_default_long], map_default_zoom);

            map.on('click', function(e) {
                console.log("clicked on "+e.latlng);
            });

            var osm = L.tileLayer( '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                subdomains: ['a','b','c'],
                maxZoom: map_max_zoom,
                minZoom: map_min_zoom,
            });

            osm.addTo(map);

            this.updateMapData();
            this.registerTimer();
        },
    };

    Icinga.availableModules.map = Map;

}(Icinga));
