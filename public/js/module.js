(function(Icinga) {

    function colorMarker(color) {
        img_base = icinga.config.baseUrl + '/img/map/';

        return L.icon({
            iconUrl: img_base + 'marker-icon-'+color+'.png',
            shadowUrl: img_base + 'marker-shadow.png',}
        );
    }

    var cache = {};

    var Map = function(module) {
        this.module = module;
        this.initialize();
        this.timer;
        this.module.icinga.logger.debug('Map module loaded');
    };

    Map.prototype = {
            
        initialize: function()
        {
            this.timer = {}
            this.module.on('rendered', this.onRenderedContainer);
            this.registerTimer()
        },

        registerTimer: function (id) {
            this.timer = this.module.icinga.timer.register(
                this.updateAllMapData,
                this,
                10000
            );
            return this;
        },

        updateAllMapData: function()
        {
            var _this = this

            if (cache.length == 0) {
                this.module.icinga.timer.unregister(this.timer)
                return this
            }

            $.each(cache, function(id) {
                if (!$('#map-' + id).length) {
                    delete cache[id]
                } else {
                    _this.updateMapData(id)
                }
            }); 
        },

        updateMapData: function (id, show_host = "") {
            function showHost(hostname) {
                if(cache[id].hostMarkers[hostname]) {
                    var el = cache[id].hostMarkers[hostname]
                    cache[id].markers.zoomToShowLayer(el, function() {
                        el.openPopup();
                    })
                }
            }

            var xhr;
            xhr = new XMLHttpRequest();
            xhr.open('GET', icinga.config.baseUrl + '/map/data/points', true);
            xhr.onreadystatechange = function(e) {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        var result = JSON.parse(xhr.responseText);
                        // remove old markers
                        $.each(cache[id].hostMarkers, function( hostname, d ) {
                            if(!result[hostname]) {
                                cache[id].markers.removeLayer(d);
                                delete cache[id].hostMarkers[hostname];
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
                                services += " state-"+service_status[service['service_hard_state']].toLowerCase();
                                services += "" + (service['service_acknowledged'] == 1 ? " handled" : "")
                                services += '">';
                                services += '<div class="state-label">';
                                services += service_status[service['service_hard_state']];
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

                            switch(parseInt(worstState)) {
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
                            info += '<a data-base-target="_next" href="'
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

                            if(cache[id].hostMarkers[hostname]) {
                                marker = cache[id].hostMarkers[hostname]; 
                                marker.options.state = worstState;
                            } else {
                                marker = L.marker(data['coordinates'],
                                    {
                                        icon: icon,
                                        title: hostname,
                                        id: hostname,
                                        state: worstState,
                                    }).addTo(cache[id].markers);

                                cache[id].hostMarkers[hostname] = marker
                                cache[id].hostData[hostname] = data
                            }

                            marker.setIcon(icon);
                            marker.bindPopup(info);
                        });

                        if(show_host != "") {
                            showHost(show_host);
                            show_host = ""
                        }
                    }
                }
            };
            xhr.send(null);
        },

        onRenderedContainer: function(event) {
            cache[id] = {}

            // TODO: initialize once and only update
            cache[id].markers = new L.MarkerClusterGroup({
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

            cache[id].hostMarkers = {};
            cache[id].hostData = {};

            cache[id].map = L.map('map-'+id).setView([map_default_lat, map_default_long], map_default_zoom);

            L.control.locate({
                icon: 'icon-pin' 
            }).addTo(cache[id].map);

            cache[id].map.on('click', function(e) {
                // TODO: any other way?
                var id = e.target._container.id.replace('map-','');

                if (e.originalEvent.ctrlKey) {
                    var coord = 'vars.geolocation = "'
                        + e.latlng.lat.toFixed(6)
                        + ','
                        + e.latlng.lng.toFixed(6)
                        + '"'
                    var marker;
                    marker = L.marker(e.latlng, { icon: colorMarker("blue") })
                    marker.bindPopup("<h1>selected coordinates:</h1><pre>" + coord + "</pre>")
                    marker.addTo(cache[id].markers);
                    marker.on('popupclose', function(evt) {
                        cache[id].markers.removeLayer(marker);
                    });
                    cache[id].markers.zoomToShowLayer(marker, function() {
                        marker.openPopup();
                    })
                }
            });

            var osm = L.tileLayer( '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                subdomains: ['a','b','c'],
                maxZoom: map_max_zoom,
                minZoom: map_min_zoom,
            });

            osm.addTo(cache[id].map);
            cache[id].markers.addTo(cache[id].map);
            
            this.updateMapData(id, map_show_host)
        },
    };

    Icinga.availableModules.map = Map;

}(Icinga));
