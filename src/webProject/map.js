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

    let testArea = document.getElementById("test");
    testArea.onclick = function() {
        alert("clické");
    };
}

function updateRoomName(roomName) {
    document.getElementById("roomName").innerHTML = roomName;
}

function getValuesByRoom(roomName) {
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
    httpGetValues.open("GET", "https://amsttho.divtec.me/iot/api/locations/" + roomName + "/values", true);
    httpGetValues.send();
}