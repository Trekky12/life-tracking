(function ($) {

    $(document).ready(function ( ) {

        /**
         * Map
         */
        var mymap = L.map('mapid').setView([default_location.lat, default_location.lng], default_location.zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            subdomains: ['a', 'b', 'c']
        }).addTo(mymap);
        
        getMarkers();


        function getMarkers() {
            $.ajax({
                type: 'GET',
                url: jsObject.marker_url,
                data: {
                    from: $('#from').val(),
                    to: $('#to').val()
                },
                success: function (data) {
                    drawMap(data);
                }
            });
        }

        function drawMap(markers) {

            var my_latlngs = [];
            var my_markers = [];

            jQuery.each(markers, function (i, marker) {

                if (marker.lat === null || marker.lng === null) {
                    return;
                }

                var dateString = marker.dt;
                var accuracyString = "";
                if (marker.acc > 0) {
                    accuracyString = '<br/>' + lang.accuracy + ' : ' + marker.acc + ' m';
                }
                var addressString = '<br/><a href="#" data-id="' + marker.id + '" class="btn-get-address">' + lang.address + '</a>';
                var removeString = '<br/><br/><a href="#" data-url="' + jsObject.delete_marker_url + marker.id + '" class="btn-delete">' + lang.delete_text + '</a>';

                var my_marker = L.marker([marker.lat, marker.lng]).bindPopup(dateString + accuracyString + addressString + removeString);
                my_marker.addTo(mymap);
                my_latlngs.push([marker.lat, marker.lng, i]);


                if (marker.acc > 0) {
                    var circle = null;

                    my_marker.on('mouseover', function (e) {
                        circle = L.circle([marker.lat, marker.lng], {
                            opacity: 0.5,
                            radius: marker.acc
                        }).addTo(mymap);
                    });

                    my_marker.on('mouseout', function (e) {
                        mymap.removeLayer(circle);
                    });

                }

                my_markers.push(my_marker);
            });

            var polyline = L.polyline(my_latlngs).addTo(mymap);

            var group = new L.featureGroup(my_markers);
            mymap.fitBounds(group.getBounds());

            return true;
        }


        /**
         * Get Adress of marker
         */
        $('body').on('click', '.btn-get-address', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                url: jsObject.get_address_url + id,
                method: 'GET',
                success: function (response) {
                    if (response['status'] === 'success') {
                        var output = '';

                        if (response['data']['police']) {
                            output += response['data']['police'] + '\n';
                        }

                        output += response['data']['road'] + ' ' + response['data']['house_number'] + '\n' + response['data']['postcode'] + ' ' + response['data']['city'];
                        alert(output);
                    }
                }
            });
        });


        /**
         * Fade Filter
         */
        $('#show-filter').on('click', function () {
            var filter = $(this);

            $('#search-form').slideToggle(300, function () {
                filter.toggleClass('hiddenSearch');
                if ($(this).is(':hidden') === true) {
                    filter.attr("aria-hidden", "true");
                } else {
                    filter.attr("aria-hidden", "false");
                }
            });

        });

    });
})(jQuery);
