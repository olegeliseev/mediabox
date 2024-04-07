const form = document.querySelector("form");
const dropZone = document.querySelector(".drop-zone");
const fileInput = document.querySelector(".file-input");
const progressArea = document.querySelector(".progress-area");
const uploadedArea = document.querySelector(".uploaded-area");

let tempStorage = [];

window.addEventListener("load", () => { 
    fileInput.value = ''; 
});

fileInput.addEventListener("click", (e) => {
    e.stopPropagation();

    for (let i = 0; i < fileInput.files.length; i++) {
        tempStorage.push(fileInput.files[i]);
    }
});

dropZone.addEventListener("click", () => {
    fileInput.click();
});

dropZone.addEventListener("drop", (e) => {
    e.preventDefault();

    const dataTransfer = new DataTransfer();

    for (let i = 0; i < e.dataTransfer.files.length; i++) {
        dataTransfer.items.add(e.dataTransfer.files[i]);
    }

    for (let i = 0; i < fileInput.files.length; i++) {
        dataTransfer.items.add(fileInput.files[i]);
    }

    fileInput.files = dataTransfer.files;
    formatFilesNames([...e.dataTransfer.files]);
});

dropZone.addEventListener("change", (e) => {
    e.preventDefault();
    const dataTransfer = new DataTransfer();

    for (let i = 0; i < fileInput.files.length; i++) {
        dataTransfer.items.add(fileInput.files[i]);
    }

    for (let i = 0; i < tempStorage.length; i++) {
        dataTransfer.items.add(tempStorage[i]);
    }

    formatFilesNames([...fileInput.files]);
    fileInput.files = dataTransfer.files;
    tempStorage = [];
});

dropZone.addEventListener("dragover", (e) => {
    e.preventDefault();
});

function formatFilesNames(files) {
    files.forEach((file) => {
        if (file) {
            if (file.name.length >= 12) {
                let splitName = file.name.split(".");
                let fileName =
                    splitName[0].substring(0, 13) + "... ." + splitName[1];
                appendFile(fileName, file.lastModified);
            } else {
                appendFile(file.name, file.lastModified);
            }
        }
    });
}

function removeFile(fileId) {

    const dataTransfer = new DataTransfer();

    for (let i = 0; i < fileInput.files.length; i++) {
        if(fileInput.files[i].lastModified === fileId) {
            continue;
        }
        dataTransfer.items.add(fileInput.files[i]);
    }

    fileInput.files = dataTransfer.files;
}

function appendFile(fileName, fileId) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "/");
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.upload.addEventListener("progress", ({ loaded, total }) => {
        let fileLoaded = Math.floor((loaded / total) * 100);
        let fileTotal = Math.floor(total / 1000);
        let fileSize;
        fileTotal < 1024
            ? (fileSize = fileTotal + " KB")
            : (fileSize = (loaded / (1024 * 1024)).toFixed(2) + " MB");
        let progressHTML = `<li class="row">
                            <i class="fas fa-file-alt"></i>
                            <div class="content">
                              <div class="details">
                                <span class="name">${fileName} • Uploading</span>
                                <span class="percent">${fileLoaded}%</span>
                              </div>
                              <div class="progress-bar">
                                <div class="progress" style="width: ${fileLoaded}%"></div>
                              </div>
                            </div>
                          </li>`;
        uploadedArea.classList.add("onprogress");
        progressArea.innerHTML = progressHTML;
        if (loaded == total) {
            progressArea.innerHTML = "";
            let uploadedHTML = `<li class="row">
                              <div class="content upload">
                                <i class="fas fa-file-alt"></i>
                                <div class="details">
                                  <span class="name">${fileName} • Uploaded</span>
                                  <span class="size">${fileSize}</span>
                                  <span hidden>${fileId}</span>
                                </div>
                              </div>
                              <div class="icons">
                                <i class="fa-solid fa-check"></i>
                                <i class="delete fa-solid fa-trash"></i>
                              </div>
                            </li>`;
            uploadedArea.classList.remove("onprogress");
            uploadedArea.insertAdjacentHTML("afterbegin", uploadedHTML);
            const deleteButton = document.querySelector(".delete");
            deleteButton.addEventListener("click", (e) => {
                e.currentTarget.parentNode.parentNode.remove();
                removeFile(fileId);
            });
        }
    });

    let data = new FormData(form);
    xhr.send(data);
}

