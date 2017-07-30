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
    var hostData;
    var hostMarkers;

    // TODO: Translate
    var service_status = {};
    service_status[0] = "ok";
    service_status[1] = "warning";
    service_status[2] = "critical";
    service_status[3] = "unknown";
    service_status[99] = "pending";

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
                            var services;

                            var worstState = (hard_state == 1 ? 2 : hard_state );

                            services = '<div class="map-popup-services">'; 
                            services += '<h1><span class="icon-services"></span> Services</h1>';
                            services += '<div class="scroll-view">'; 
                            services += '<table class="icinga-module module-monitoring">';
                            services += '<tbody>'; 

                            $.each( data['services'], function( service_display_name, service ) {
                                var state = service['service_hard_state']

                                if(state < 3 && state > worstState) {
                                    worstState = service['service_hard_state']
                                }

                                services += '<tr>';

                                services += '<td class="';
                                services += "state-col";
                                services += " state-"+service_status[service['service_hard_state']];
                                services += "" + (service['service_acknowledged'] == 1 ? " handled" : "")
                                services += '">';
                                services += '<div class="state-label">';
                                services += service_status[service['service_hard_state']].toUpperCase();
                                services += '</div>';
                                services += '</td>';

                                services += '<td>';
                                services += '<div class="state-header">';
                                services += service_display_name;
                                services += '</td>';

                                services += '</tr>';
                            });

                            services += '</tbody>';
                            services += '</table>';
                            services += '</div>';
                            services += '</div>';

                            switch(worstState) {
                                case 0:
                                    icon = colorMarker("green");
                                    break;
                                case 1:
                                    icon = colorMarker("orange");
                                    break;
                                case 2:
                                    icon = colorMarker("red");
                                    break;
                                default:
                                    icon = colorMarker("blue");
                            }

                            var host_icon ="";
                            if(data['host_icon_image'] != "") {
                                host_icon = '<img src="'+icinga.config.baseUrl+'/img/icons/'
                                + data['host_icon_image']
                                + '"'
                                + (( data['host_icon_image_alt'] != "" ) ? ' alt="' + data['host_icon_image_alt'] + '"' : '')
                                + ' class="host-icon-image icon">';
                            }

                            var info = '<div class="map-popup">';
                            info += '<h1>' 
                            info += '<a class="rowAction" href="'
                                    + icinga.config.baseUrl
                                    + '/monitoring/host/show?host='
                                    + hostname
                                    + '">'
                            info += ' <span class="icon-eye"></span> '
                            info += '</a>'
                            info += hostname + '</h1>'

                            info += services;
                            info += '</div>';

                            var marker;

                            if(hostMarkers[hostname]) {
                                marker = hostMarkers[hostname]; 
                                marker.options.state = worstState;
                            } else {
                                marker = L.marker(data['coordinates'],
                                    {
                                        icon: icon,
                                        title: hostname,
                                        id: hostname,
                                        state: worstState,
                                    }).addTo(markers);

                                hostMarkers[hostname] = marker
                                hostData[hostname] = data
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
            markers = new L.MarkerClusterGroup({
                iconCreateFunction: function(cluster) {
                    var childCount = cluster.getChildCount();

                    var worstState = 0;
                    $.each(cluster.getAllChildMarkers(), function(id, el) {
                        if(el.options.state > worstState) {
                            worstState = el.options.state
                        }
                    });

                    var c = ' marker-cluster-'+worstState;


                    return new L.DivIcon({ html: '<div><span>' + childCount + '</span></div>', className: 'marker-cluster' + c, iconSize: new L.Point(40, 40) });
                }
            });
            hostMarkers = {};
            hostData = {};

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
            markers.addTo(map);

            this.updateMapData();
            this.registerTimer();

        },
    };

    Icinga.availableModules.map = Map;

}(Icinga));
