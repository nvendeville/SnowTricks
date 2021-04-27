$(document).ready(function () {

    $("#arrowsDown").click(function () {
        $("#listingTitle").toggle(1000);
        $("#listTricks").toggle(1000);
        $("#divLoadmore").toggle(1000);
        $("#divScrollToTitle").toggle(1000);
        $("html, body").animate({scrollTop:$("#listingTitle").offset().top}, 1000);
    });

    $("#loadMore").click(function () {
        $("#listTricks").show(1000);
        $("#divLoadmore").removeClass("pt-3 pb-3");
        $.ajax({
            url : "/",
            type : "GET",
            data : "offset=" + offset,
            dataType : "json",
            success : function (response) {
                if (response.tricks !== "") {
                    $("#listTricks").append(response.tricks);
                }
                if (response.hasMore === false) {
                    $("#loadMore").addClass("d-none");
                }
                offset += 5;
            }
        });
    });

    $("#scrollToTitle").click(function () {
        $("html, body").animate({scrollTop:$("#listingTitle").offset().top}, 1000);
    });
});
