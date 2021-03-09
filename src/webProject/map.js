// Wait for the page to load first
window.onload = function() {

    let anchors = document.getElementsByClassName('areaButton');

    for (let i = 0; i < anchors.length; i++) {
        let anchor = anchors[i];
        anchor.onclick = function() {
            updateRoomName(anchor.alt);
            getValuesByRoom(anchor.alt);
            return false;
        };
    }
}

function updateRoomName(roomName) {
    if (roomName === "") {
        roomName = "Affichage de toutes les salles";
    }
    document.getElementById("roomName").innerHTML = roomName;
}

function getValuesByRoom(roomName) {
    let urlRequest = "https://amsttho.divtec.me/iot/api/locations/" + roomName + "/values";
    if (roomName === "")
        urlRequest = "https://amsttho.divtec.me/iot/api/values";
    
    let httpGetValues = new XMLHttpRequest();
    httpGetValues.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let content = JSON.parse(this.responseText);
            fillTable(content);
        }
        if (this.status == 404) {
            fillTable("");
        }
    };
    httpGetValues.open("GET", urlRequest, true);
    httpGetValues.send();
}