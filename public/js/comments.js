$(document).ready(function () {
    let loadComments = function () {
        $.ajax({
            url : "/trick/" + slug + "/comments",
            type : "GET",
            data : "offset=" + offset,
            dataType : "json",
            success : function (response) {
                let comments = JSON.parse(response.comments)
                if (comments.length > 0) {
                    for (let comment of comments) {
                        let avatar = "avatardefault.jpg"
                        if (comment.avatar) {
                            avatar = comment.avatar
                        }
                        $("<div class='container'></div>")
                            .append($("<div class='row justify-content-center f-n-w mb-2'></div>")
                                .append($('<img  class="img57 rounded-circle align-self-center shadow-sm img-fluid img-thumbnail"></img>')
                                    .attr("src", "/img/" + avatar)
                                )
                                .append($('<div  class="col-lg-5 ml-2 card radius width17 padding-card"></div>')
                                    .append($('<p class="small mb-0 text-primary">' + comment.date + '</p>'))
                                    .append($('<h5>' + comment.pseudo + '</h5>'))
                                    .append($('<p  class="text-muted text-small text-left">' + comment.comment + '</p>'))
                                    .append($("<a class='reset-anchor text-small' href='#'><i class='fas fa-share mr-2 text-primary'></i><strong>Répondre</strong></a>"))
                                )
                            )
                            .appendTo($("#containerComments"))
                    }
                }
                if (response.hasMore === false) {
                    $("#loadMoreComments").addClass('d-none')
                    $("#containerComments").addClass('mb-5');
                }
                offset += 4
            }
        });
    };

    $('#loadMoreComments').click(function () {
        $('#divLoadmoreComments').removeClass('pt-3 pb-3')
        loadComments()
    })

    $('#scrollToTitle').click(function () {
        $("html, body").animate({scrollTop:$("#listingTitle").offset().top}, 1000)
    });

    loadComments()
});