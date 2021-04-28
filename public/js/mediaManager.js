/////////////// Gestionnaire du multi-select TELECHARGEZ VOS IMAGES/////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////

window.onload = () => {

    // initialisation du gestionnaire de transfert
    let getDataTransfer = () => new DataTransfer();
    const {concat} = Array.prototype;

    // test de compatibilité des navigateurs (surtout pour les vieux)
    try {
        getDataTransfer();
    } catch {
        getDataTransfer = () => new ClipboardEvent("").clipboardData;
    }

    let trickFormImages = document.getElementById("trick_form_img");
    let fileListDisplay = document.getElementById("file-list-display");
    let fileList = [];

    // submit du formulaire
    $("#trick_form_submit_button").click(function () {
        // on récupère les images à uploader dans le multi-select
        trickFormImages.files = createFileList(fileList);
        // on post le formulaire
        $("#trick_form").submit();
        // on renvoie false pour ne pas poster deux fois le formulaire
        return false;
    });

    // ajout de chaque image sélectionnée dans la div
    trickFormImages.addEventListener("change", function (event) {
        // on les intercepte
        for (let i = 0; i < trickFormImages.files.length; i++) {
            if (!isFileExist(trickFormImages.files[i])) {
                // et on les met dans notre tableau (cf : ligne supérieure 73)
                fileList.push(trickFormImages.files[i]);
            }
        }
        // on supprime tout ce qui se trouve sur le multi-select
        trickFormImages.value = "";
        renderFileList();
    });

    //création du container des images sélectionnées
    let renderFileList = function () {
        fileListDisplay.innerHTML = "";
        fileList.forEach(function (file, index) {
            let fileDisplayElem = document.createElement("p");
            fileDisplayElem.setAttribute("class", "d-flex flex-row justify-content-between w40");
            let fileReader = new FileReader();
            if (file.type.match("image")) {
                fileReader.onload = function () {
                    let thumbnail = document.createElement("img");
                    thumbnail.setAttribute("class", "float-left");
                    thumbnail.src = fileReader.result;
                    thumbnail.height = 50;

                    let imageName = document.createElement("span");
                    imageName.innerHTML = file.name;
                    imageName.setAttribute("class", "d-block mt-auto mb-auto");

                    let deleteLink = document.createElement("a");
                    let trash = document.createElement("i");
                    trash.setAttribute("class", "fas fa-trash-alt");
                    deleteLink.appendChild(trash);
                    deleteLink.setAttribute("class", "d-block mt-auto mb-auto");
                    deleteLink.setAttribute("id", file.name);
                    deleteLink.setAttribute("href", "#");

                    fileDisplayElem.appendChild(thumbnail);
                    fileDisplayElem.appendChild(imageName);
                    fileDisplayElem.appendChild(deleteLink);

                    $(deleteLink).click(function (event) {
                        event.preventDefault();
                        fileList.splice(index,1);
                        renderFileList();
                    });

                };
                fileReader.readAsDataURL(file);
            } else {
                alert("le fichier : " + file.name + " n\'est pas une image");
            }
            fileListDisplay.appendChild(fileDisplayElem);
        });
    };

    // si l"image en cours est déjà sélectionnée, on l"ignore
    let isFileExist = function (file) {
        let exist = false;
        fileList.forEach(function (exitingFile) {
            if (exitingFile.name === file.name) {
                exist = true;
            }
        })
        return exist;
    };

    // création de la liste d"images à uploader par rapport aux sélectionnées
    let createFileList = function () {
        const files = concat.apply([], arguments);
        let index = 0;
        const {length} = files;

        const dataTransfer = getDataTransfer();

        for (; index < length; index++) {
            dataTransfer.items.add(files[index]);
        }
        return dataTransfer.files;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////Mettre une image à la une//////////////////////////////////////////////////////////////////////

    let featuredImgs = document.querySelectorAll("[data-feature]");

    for (let featuredImg of featuredImgs) {
        // On écoute le clic
        featuredImg.addEventListener("click", function (e) {
            // On empêche la navigation
            e.preventDefault();

            if (confirm("Voulez-vous mettre cette image à la une ?")) {
                // On envoie une requête Ajax vers le href du lien avec la méthode PATCH
                fetch(this.getAttribute("href"), {
                    method: "PATCH",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({"_token": this.dataset.token})
                }).then(
                    // On récupère la réponse en JSON
                    (response) => {return response.json();
                    }
                ).then((data) => {
                    if (data.success) {
                        $(".borderFeaturedImg").removeClass("borderFeaturedImg");
                        $(".text-warning").removeClass("text-warning");
                        $("#" + this.getAttribute("data-name")).addClass("borderFeaturedImg");
                        $(this).children(0).addClass("text-warning");
                    } else {
                        alert(data.error);
                    }
                }).catch((e) => alert(e));
            }
        })
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////Supprimer un média//////////////////////////////////////////////////////////////////////

    let links = document.querySelectorAll("[data-delete]");

    for (let link of links) {
        link.addEventListener("click", function (e) {
            e.preventDefault();

            if (confirm("Voulez-vous supprimer ce média ?")) {
                fetch(this.getAttribute("href"), {
                    method: "DELETE",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({"_token": this.dataset.token})
                }).then(
                    response => {return response.json();
                    }
                ).then(data => {
                    if (!data.success) {
                        alert(data.error);
                        return;
                    }
                    $("#" + this.getAttribute("data-name")).remove();
                }).catch((e) => alert(e));
            }
        })
    }
}


