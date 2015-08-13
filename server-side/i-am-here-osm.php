<!DOCTYPE html>
<html lang="en">
<head>
<meta charset=utf-8>
<title>I am here</title>
</head>
<body>

<?php
$date = $lat = $lon = '';
$date_lat_lon = rtrim(file_get_contents("/tmp/gps-position.txt"));
if ($date_lat_lon) {
	list($date, $lat, $lon) = explode("_", $date_lat_lon);
}
?>

<h1>I was here on <span id="date"><?php echo $date ? $date : "…" ?></span></h1>
<p>(last known position where I had a GPS signal, a network connection, and some battery power)</p>

<div id="mapcanvas" style="width: 800px; height: 600px">
</div>
<p>Last display: <span id="update"></span> (auto-updated every <span id="auto"></span>s).</p>

<script type="text/javascript" src="./OpenLayers.js"></script>
<script>
// map
var map, marker, markers;
var autoupdate = 30000;
<?php if ($lat && $lon): ?>
createMap(<?php echo $lat.",".$lon ?>);
<?php endif; ?>
function createMap(lat, lon) {
    map = new OpenLayers.Map("mapcanvas");
    var layer = new OpenLayers.Layer.OSM();
    map.addLayer(layer);

    marker = new OpenLayers.Marker();
    // http://stackoverflow.com/questions/18537555/move-marker-dynamically
    marker.map = map;

    var zoom=16;
 
    markers = new OpenLayers.Layer.Markers( "Markers" );
    map.addLayer(markers);
 
    markers.addMarker(marker);

    updateMap("<?php echo $date ?>", lat, lon);

    map.setCenter (marker.lonlat, zoom);

    document.querySelector("#auto").innerHTML = autoupdate/1000;
}

doRefresh();
function doRefresh() {
	var xhr; 
	try {
		xhr = new XMLHttpRequest();
	} catch (e) {
		xhr = false;
	}

	xhr.onreadystatechange  = function() { 
		if (xhr.readyState  == 4) {
			if (xhr.status  == 200) {
				dte = xhr.responseText.split('_')[0];
				lat = xhr.responseText.split('_')[1];
				lon = xhr.responseText.split('_')[2];
				if (dte && lat && lon) {
					if (!map) {
						createMap(lat, lon);
					}
					if (map) {
						updateMap(dte, lat, lon);
					}
				}
			}
		}
	};
	xhr.open("GET", "i-am-here-position.php?" + Math.random(),  true); 
	xhr.send(null);
	setTimeout('doRefresh()', autoupdate);
}

function updateMap(dte, lat, lon) {
        var lonLat = new OpenLayers.LonLat( lon, lat )
          .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
          );    

	marker.lonlat = lonLat
        map.setCenter (marker.lonlat);
	markers.redraw();
	document.querySelector("#date").innerHTML = dte;
	document.querySelector("#update").innerHTML = new Date().toLocaleString('en-GB');
}
</script>
