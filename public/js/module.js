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
            function showHost(hostname) {
                if(hostMarkers[hostname]) {
                    el = hostMarkers[hostname]
                    latLng = el.getLatLng();
                    markers.zoomToShowLayer(el, function() {
                        el.openPopup();
                    })
                }
            }

            function colorMarker(color) {
                img_base = icinga.config.baseUrl + '/img/map/';

                return L.icon({
                    iconUrl: img_base + 'marker-icon-'+color+'.png',
                    shadowUrl: img_base + 'marker-shadow.png',}
                );
            }
            
            xhr = new XMLHttpRequest();
            xhr.open('GET', icinga.config.baseUrl + '/map/data/points', true);
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
                                    icon = colorMarker("green");
                                    break;
                                case 1:
                                    icon = colorMarker("red");
                                    break;
                                default:
                                    icon = colorMarker("blue");
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

                            var host_icon ="";

                            if(data['host_icon_image'] != "") {
                                host_icon = '<img src="'+icinga.config.baseUrl+'/img/icons/'
                                + data['host_icon_image']
                                + '"'
                                + (( data['host_icon_image_alt'] != "" ) ? ' alt="' + data['host_icon_image_alt'] + '"' : '')
                                + ' class="host-icon-image icon">';
                            }

                            var info = '<div class="map_host_detail">';
                            info += host_icon+'<span><a class="rowAction" href="'+icinga.config.baseUrl+'/monitoring/host/show?host='+hostname+'">'+hostname+'</a></span>'
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

                        if(map_show_host != "") {
                            showHost(map_show_host);
                            map_show_host = "";
                        }
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
