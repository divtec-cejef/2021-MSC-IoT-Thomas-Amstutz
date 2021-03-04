// Wait for the page to load first
window.onload = function() {

    //Get a reference to the link on the page
    // with an id of "mylink"
    // let rooms = document.querySelectorAll(".classe");
    var a = document.getElementById("b103");
    var b = document.getElementById("b104");
    
    //Set code to run when the link is clicked
    // by assigning a function to "onclick"
    // for(let i = 0, len = rooms.length; i < len; i++) {
    //     elements[i].onclick = function() {
    //         updateRoomName(a.alt);
    //         getValuesByRoom(a.alt);
    //         return false;
    //     }
    // }
    a.onclick = function() {
      updateRoomName(a.alt);
      getValuesByRoom(a.alt);
      return false;
    }
    b.onclick = function() {
      updateRoomName(b.alt);
      getValuesByRoom(b.alt);
      return false;
    }
}

function updateRoomName(roomName) {
    document.getElementById("roomName").innerHTML = roomName;
}

function getValuesByRoom(roomName) {
    let httpGetLast = new XMLHttpRequest();
    httpGetLast.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let content = JSON.parse(this.responseText);
            fillTable(content);
        }
    };
    httpGetLast.open("GET", "https://amsttho.divtec.me/iot/api/locations/" + roomName + "/values", true);
    httpGetLast.send();
}