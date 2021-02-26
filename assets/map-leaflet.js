//Affichage de la carte
var mymap = L.map('map-leaflet').setView([44, 1.314], 7);

L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    maxZoom: 18,
    id: 'xadezign/ckjogh8zj3cyv19o3vywjycp3',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: 'pk.eyJ1IjoieGFkZXppZ24iLCJhIjoiY2phanU3N3NxM2NsNzJ3bmk4OXFmMmlpbiJ9.Y6Wn2DHM1P6BxkGT6weZ9A'
}).addTo(mymap);

//Fonction pour chaque coordonnée : Popup
function onEachFeature(feature, layer) {
    if (feature.properties && feature.properties.popupContent) {
        layer.bindPopup(feature.properties.popupContent);
    }
}

// Icone Referendum
var RFIcon = L.icon({
    iconUrl: document.location.origin + '/carto_event/wp-content/plugins/carto_event/assets/icon/icon_rf.png',
    iconSize: [40, 40],
});

 // Icone GF
var GFIcon = L.icon({
    iconUrl: document.location.origin + '/carto_event/wp-content/plugins/carto_event/assets/icon/icon_gf.png',
    iconSize: [40, 40],
});

// Affichage des points Referendum
L.geoJSON(referendum, {
    pointToLayer: function (feature, latlng) {
        return L.marker(latlng, {icon:RFIcon});
    },
    onEachFeature: onEachFeature
}).addTo(mymap);

// Affichage des points GF
L.geoJSON(generation, {
    pointToLayer: function (feature, latlng) {
        return L.marker(latlng, {icon:GFIcon});
    },
    onEachFeature: onEachFeature
}).addTo(mymap);