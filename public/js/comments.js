$(document).ready(function () {
    let loadComments = function () {
        $.ajax({
            url : "/trick/" + slug + "/comments",
            type : "GET",
            data : "offset=" + offset,
            dataType : "json",
            success : (response) => {
                if (response.comments !== "") {
                    $("#containerComments").append(response.comments);
                }
                if (response.hasMore === false) {
                    $("#loadMoreComments").addClass("d-none");
                    $("#containerComments").addClass("mb-5");
                }
                offset += 4;
            }
        });
    };

    $("#loadMoreComments").click(function () {
        $("#divLoadmoreComments").removeClass("pt-3 pb-3");
        loadComments();
    });

    $("#scrollToTitle").click(function () {
        $("html, body").animate({scrollTop:$("#listingTitle").offset().top}, 1000);
    });

    loadComments();
});